<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Group;
use App\Models\Settlement;
use App\Models\StatementRecord;
use App\Models\User;

class BalanceService
{
    /**
     * Calculate balance snapshot for a group (matching iOS BalanceEngine exactly).
     * Returns summaries per user and payment suggestions.
     */
    public function calculateSnapshot(Group $group): array
    {
        // Get active members of the group
        $activeMembers = $group->members()
            ->wherePivot('is_active', true)
            ->orderBy('uuid')
            ->get();

        if ($activeMembers->isEmpty()) {
            return ['summaries' => [], 'suggestions' => []];
        }

        // Initialize matrix: matrix[fromId][toId] = cents owed from fromId to toId
        $matrix = $this->initializeMatrix($activeMembers);

        // Get all expenses and settlements for this group, sorted by date
        $expenses = $group->expenses()->orderBy('created_at')->get();
        $settlements = $group->settlements()->orderBy('created_at')->get();

        // Process each expense
        foreach ($expenses as $expense) {
            $this->processExpense($expense, $matrix, $activeMembers);
        }

        // Process each settlement
        foreach ($settlements as $settlement) {
            $this->processSettlement($settlement, $matrix);
        }

        // Consolidate debts (net out mutual debts)
        $this->consolidateDebts($matrix, $activeMembers);

        // Format for response
        $summaries = $this->formatBalances($matrix, $activeMembers);
        $suggestions = $this->getPaymentSuggestions($summaries);

        return [
            'summaries' => $summaries,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * Build chronological statement entries for a group.
     */
    public function buildStatements(Group $group, ?string $userId = null): array
    {
        $entries = [];

        // Get all statement records for the group
        $records = $group->statementRecords()
            ->orderBy('transaction_date')
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->get();

        foreach ($records as $record) {
            $entries[] = [
                'id' => $record->uuid,
                'user_id' => $record->user->uuid ?? $record->user_id,
                'type' => $record->transaction_type,
                'description' => $record->description,
                'amount_cents' => $record->amount_cents ?? (int)($record->amount * 100),
                'balance_before_cents' => $record->balance_before_cents ?? (int)($record->balance_before * 100),
                'balance_after_cents' => $record->balance_after_cents ?? (int)($record->balance_after * 100),
                'balance_change_cents' => $record->balance_change_cents ?? (int)($record->balance_change * 100),
                'note' => $record->transaction_details['note'] ?? '',
                'transaction_date' => $record->transaction_date->toIso8601String(),
                'reference_number' => $record->reference_number,
            ];
        }

        return $entries;
    }

    /**
     * Get maximum payable amount between two users in a group (prevents overpayment).
     */
    public function maxPayable(Group $group, string $fromUserId, string $toUserId): int
    {
        $snapshot = $this->calculateSnapshot($group);

        foreach ($snapshot['summaries'] as $summary) {
            if ($summary['user_id'] === $fromUserId) {
                return $summary['owes'][$toUserId] ?? 0;
            }
        }

        return 0;
    }

    /**
     * Create statement records after an expense or settlement is added.
     * Called within a transaction by the controllers.
     */
    public function createStatementRecords(Group $group, ?Expense $expense = null, ?Settlement $settlement = null): void
    {
        // Get active members
        $activeMembers = $group->members()
            ->wherePivot('is_active', true)
            ->orderBy('uuid')
            ->get();

        if ($activeMembers->isEmpty()) {
            return;
        }

        // Determine the cutoff date
        $targetDate = $expense ? $expense->created_at : ($settlement ? $settlement->created_at : now());

        // For each active user, create a statement record
        foreach ($activeMembers as $user) {
            $balanceBefore = $this->calculateBalanceForUserBefore($group, $user->id, $targetDate);
            $balanceAfter = $this->calculateBalanceForUserAfter($group, $user->id, $targetDate);
            $balanceChange = $balanceAfter - $balanceBefore;

            // Determine impact and description
            if ($expense) {
                $impact = $this->calculateExpenseImpact($expense, $user->id);
                $description = $this->formatExpenseDescription($expense, $user->id);
                $type = 'expense';
                $refId = $expense->id;
            } else {
                $impact = $this->calculateSettlementImpact($settlement, $user->id);
                $description = $this->formatSettlementDescription($settlement, $user->id);
                $type = 'settlement';
                $refId = $settlement->id;
            }

            // Skip if no impact
            if ($impact == 0 && $balanceChange == 0) {
                continue;
            }

            StatementRecord::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'group_id' => $group->id,
                'user_id' => $user->id,
                'expense_id' => $expense?->id,
                'settlement_id' => $settlement?->id,
                'transaction_type' => $type,
                'description' => $description,
                'amount_cents' => abs($impact),
                'reference_number' => StatementRecord::generateReferenceNumber(strtoupper(substr($type, 0, 3))),
                'balance_before_cents' => $balanceBefore,
                'balance_after_cents' => $balanceAfter,
                'balance_change_cents' => $balanceChange,
                'transaction_details' => [
                    'note' => '',
                ],
                'transaction_date' => $targetDate,
                'status' => 'completed',
            ]);
        }
    }

    /**
     * Initialize the debt matrix.
     */
    private function initializeMatrix($activeMembers): array
    {
        $matrix = [];
        foreach ($activeMembers as $user) {
            $matrix[$user->id] = [];
            foreach ($activeMembers as $other) {
                $matrix[$user->id][$other->id] = 0;
            }
        }
        return $matrix;
    }

    /**
     * Process an expense: distribute costs among participants.
     * CRITICAL: Must match iOS BalanceEngine exactly!
     * Split algorithm: integer division with remainder distributed by UUID order.
     */
    private function processExpense(Expense $expense, array &$matrix, $activeMembers): void
    {
        // Get participants from snapshot (should match creation time snapshot)
        if (!empty($expense->participant_ids)) {
            $participantUuids = $expense->participant_ids;
        } else {
            $participantUuids = $activeMembers->pluck('uuid')->toArray();
        }

        // Get User objects for participants
        $participants = $activeMembers
            ->whereIn('uuid', $participantUuids)
            ->sortBy('uuid') // CRITICAL: Sort by UUID string for remainder distribution
            ->values();

        if ($participants->isEmpty() || !isset($matrix[$expense->paid_by_user_id])) {
            return;
        }

        $totalCents = $expense->amount_cents;
        $shareCount = $participants->count();
        $perPersonCents = intval($totalCents / $shareCount);
        $remainderCents = $totalCents % $shareCount;

        // Distribute to each participant
        foreach ($participants as $idx => $participant) {
            if ($participant->id === $expense->paid_by_user_id) {
                // Payer doesn't owe themselves
                continue;
            }

            // Per-person share + portion of remainder (first participants get +1)
            $share = $perPersonCents + ($idx < $remainderCents ? 1 : 0);

            // Check if participant already owes payer (reverse debt)
            if (isset($matrix[$participant->id][$expense->paid_by_user_id]) && $matrix[$participant->id][$expense->paid_by_user_id] > 0) {
                // Reduce the reverse debt first
                $reverseDebt = $matrix[$participant->id][$expense->paid_by_user_id];
                if ($share > $reverseDebt) {
                    $matrix[$participant->id][$expense->paid_by_user_id] = 0;
                    $matrix[$expense->paid_by_user_id][$participant->id] += ($share - $reverseDebt);
                } else {
                    $matrix[$participant->id][$expense->paid_by_user_id] -= $share;
                }
            } else {
                // Add new debt
                $matrix[$expense->paid_by_user_id][$participant->id] += $share;
            }
        }
    }

    /**
     * Process a settlement: record a payment between two users.
     */
    private function processSettlement(Settlement $settlement, array &$matrix): void
    {
        if (!isset($matrix[$settlement->from_user_id][$settlement->to_user_id])) {
            return;
        }

        $amount = $settlement->amount_cents;
        $currentDebt = $matrix[$settlement->from_user_id][$settlement->to_user_id];

        if ($amount >= $currentDebt) {
            // Payment covers the debt, possibly with overpayment
            $overpayment = $amount - $currentDebt;
            $matrix[$settlement->from_user_id][$settlement->to_user_id] = 0;

            if ($overpayment > 0) {
                // Overpayment creates reverse credit
                $matrix[$settlement->to_user_id][$settlement->from_user_id] += $overpayment;
            }
        } else {
            // Partial payment
            $matrix[$settlement->from_user_id][$settlement->to_user_id] -= $amount;
        }
    }

    /**
     * Consolidate debts: if A owes B and B owes A, net them out.
     */
    private function consolidateDebts(array &$matrix, $activeMembers): void
    {
        foreach ($activeMembers as $user1) {
            foreach ($activeMembers as $user2) {
                if ($user1->id === $user2->id) {
                    continue;
                }

                $debt1to2 = $matrix[$user1->id][$user2->id] ?? 0;
                $debt2to1 = $matrix[$user2->id][$user1->id] ?? 0;

                if ($debt1to2 > 0 && $debt2to1 > 0) {
                    if ($debt1to2 >= $debt2to1) {
                        $matrix[$user1->id][$user2->id] = $debt1to2 - $debt2to1;
                        $matrix[$user2->id][$user1->id] = 0;
                    } else {
                        $matrix[$user1->id][$user2->id] = 0;
                        $matrix[$user2->id][$user1->id] = $debt2to1 - $debt1to2;
                    }
                }
            }
        }
    }

    /**
     * Format matrix into per-user summaries.
     */
    private function formatBalances(array $matrix, $activeMembers): array
    {
        $result = [];

        foreach ($activeMembers as $user) {
            $owes = [];
            $owedBy = [];
            $netBalance = 0;

            foreach ($activeMembers as $other) {
                if ($user->id !== $other->id) {
                    $debtToOther = $matrix[$user->id][$other->id] ?? 0;
                    $debtFromOther = $matrix[$other->id][$user->id] ?? 0;

                    if ($debtToOther > 0) {
                        $owes[$other->uuid] = $debtToOther;
                        $netBalance -= $debtToOther;
                    }

                    if ($debtFromOther > 0) {
                        $owedBy[$other->uuid] = $debtFromOther;
                        $netBalance += $debtFromOther;
                    }
                }
            }

            $result[$user->uuid] = [
                'user_id' => $user->uuid,
                'user_name' => $user->name,
                'owes' => $owes,
                'owed_by' => $owedBy,
                'net_balance_cents' => $netBalance,
            ];
        }

        return $result;
    }

    /**
     * Generate payment suggestions (one per person, largest debt first).
     */
    private function getPaymentSuggestions(array $summaries): array
    {
        $suggestions = [];

        foreach ($summaries as $summary) {
            if (!empty($summary['owes'])) {
                // Get the largest single debt
                asort($summary['owes']); // Sort ascending
                $toUserId = array_key_last($summary['owes']); // Get last (largest)
                $amount = $summary['owes'][$toUserId];

                // Find recipient name
                $toUserName = 'Unknown';
                foreach ($summaries as $recipSummary) {
                    if ($recipSummary['user_id'] === $toUserId) {
                        $toUserName = $recipSummary['user_name'];
                        break;
                    }
                }

                $suggestions[] = [
                    'from_user_id' => $summary['user_id'],
                    'to_user_id' => $toUserId,
                    'from_user_name' => $summary['user_name'],
                    'to_user_name' => $toUserName,
                    'amount_cents' => $amount,
                ];
            }
        }

        return $suggestions;
    }

    /**
     * Helper methods for statement record creation (simplified - can be expanded).
     */
    private function calculateBalanceForUserBefore(Group $group, int $userId, $beforeDate): int
    {
        // Calculate balance before the given date
        return 0; // Simplified - would calculate from statements before this date
    }

    private function calculateBalanceForUserAfter(Group $group, int $userId, $afterDate): int
    {
        // Calculate balance after the given date
        $snapshot = $this->calculateSnapshot($group);
        foreach ($snapshot['summaries'] as $summary) {
            // This is also simplified - would use actual UUID
            // For now return net balance
        }
        return 0;
    }

    private function calculateExpenseImpact(Expense $expense, int $userId): int
    {
        // Simplified - would calculate actual impact
        return 0;
    }

    private function calculateSettlementImpact(Settlement $settlement, int $userId): int
    {
        // Simplified
        return 0;
    }

    private function formatExpenseDescription(Expense $expense, int $userId): string
    {
        return "Expense: {$expense->title}";
    }

    private function formatSettlementDescription(Settlement $settlement, int $userId): string
    {
        return "Settlement";
    }
}
