<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Services\BalanceService;
use App\Support\ApiPayload;
use Illuminate\Http\Request;

class StatementController extends Controller
{
    public function __construct(private BalanceService $balanceService)
    {
    }

    /**
     * Get statement records for a group (optionally filtered by user).
     */
    public function index(Request $request, Group $group)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|string|exists:users,uuid',
        ]);

        // Default to authenticated user so mobile history reflects personal spend/impact.
        $targetUserUuid = $validated['user_id'] ?? $request->user()?->uuid;

        // Convert user UUID to ID if provided/resolved
        $userId = null;
        if ($targetUserUuid) {
            $user = User::where('uuid', $targetUserUuid)->first();
            if ($user) {
                $userId = $user->id;
            }
        }

        $query = $group->statementRecords()
            ->with(['user', 'expense.paidByUser', 'settlement.fromUser', 'settlement.toUser'])
            ->when($userId, fn ($q) => $q->where('user_id', $userId));

        if ($query->exists()) {
            $statements = $query
                ->orderBy('transaction_date', 'desc')
                ->paginate(50);

            return response()->json([
                'statements' => collect($statements->items())->map(fn ($statement) => ApiPayload::statement($statement))->values(),
                'pagination' => [
                    'total' => $statements->total(),
                    'per_page' => $statements->perPage(),
                    'current_page' => $statements->currentPage(),
                    'last_page' => $statements->lastPage(),
                ],
            ]);
        }

        $items = $this->buildFallbackFeed($group, $targetUserUuid);

        return response()->json([
            'statements' => $items,
            'pagination' => [
                'total' => count($items),
                'per_page' => count($items),
                'current_page' => 1,
                'last_page' => 1,
            ],
        ]);
    }

    private function buildFallbackFeed(Group $group, ?string $userUuid): array
    {
        if ($userUuid) {
            return $this->buildFallbackFeedForUser($group, $userUuid);
        }

        $expenses = $group->expenses()
            ->with('paidByUser')
            ->get()
            ->filter(function ($expense) use ($userUuid) {
                if (!$userUuid) {
                    return true;
                }

                return $expense->paidByUser?->uuid === $userUuid
                    || in_array($userUuid, $expense->participant_ids ?? [], true);
            })
            ->map(function ($expense) {
                return [
                    'id' => $expense->uuid,
                    'user_id' => $expense->paidByUser?->uuid,
                    'user_name' => $expense->paidByUser?->name,
                    'type' => 'expense',
                    'description' => 'Expense: '.$expense->title,
                    'amount_cents' => (int) ($expense->amount_cents ?? 0),
                    'balance_before_cents' => 0,
                    'balance_after_cents' => 0,
                    'balance_change_cents' => 0,
                    'paid_by_user_name' => $expense->paidByUser?->name,
                    'from_user_name' => null,
                    'to_user_name' => null,
                    'transaction_date' => optional($expense->expense_date)?->toIso8601String(),
                    'created_at' => optional($expense->created_at)?->toIso8601String(),
                ];
            });

        $settlements = $group->settlements()
            ->with(['fromUser', 'toUser'])
            ->get()
            ->filter(function ($settlement) use ($userUuid) {
                if (!$userUuid) {
                    return true;
                }

                return $settlement->fromUser?->uuid === $userUuid
                    || $settlement->toUser?->uuid === $userUuid;
            })
            ->map(function ($settlement) {
                return [
                    'id' => $settlement->uuid,
                    'user_id' => $settlement->fromUser?->uuid,
                    'user_name' => $settlement->fromUser?->name,
                    'type' => 'settlement',
                    'description' => 'Settlement: '.($settlement->fromUser?->name ?? 'Unknown').' -> '.($settlement->toUser?->name ?? 'Unknown'),
                    'amount_cents' => (int) ($settlement->amount_cents ?? 0),
                    'balance_before_cents' => 0,
                    'balance_after_cents' => 0,
                    'balance_change_cents' => 0,
                    'paid_by_user_name' => null,
                    'from_user_name' => $settlement->fromUser?->name,
                    'to_user_name' => $settlement->toUser?->name,
                    'transaction_date' => optional($settlement->settlement_date)?->toIso8601String(),
                    'created_at' => optional($settlement->created_at)?->toIso8601String(),
                ];
            });

        return $expenses
            ->concat($settlements)
            ->sortByDesc('transaction_date')
            ->values()
            ->all();
    }

    private function buildFallbackFeedForUser(Group $group, string $userUuid): array
    {
        $memberUuidToName = $group->members()
            ->wherePivot('is_active', true)
            ->pluck('name', 'uuid');

        $expenses = $group->expenses()
            ->with('paidByUser')
            ->get()
            ->map(function ($expense) use ($userUuid, $memberUuidToName) {
                $participantUuids = collect($expense->participant_ids ?? [])
                    ->filter()
                    ->map(fn ($id) => (string) $id)
                    ->values();

                if ($participantUuids->isEmpty()) {
                    $participantUuids = collect($memberUuidToName->keys())->values();
                }

                $participantUuids = $participantUuids->sort()->values();
                $payerUuid = (string) ($expense->paidByUser?->uuid ?? '');

                if ($payerUuid !== $userUuid && !$participantUuids->contains($userUuid)) {
                    return null;
                }

                $participantCount = max(1, $participantUuids->count());
                $totalCents = (int) ($expense->amount_cents ?? 0);
                $baseShare = intdiv($totalCents, $participantCount);
                $remainder = $totalCents % $participantCount;

                $userShareCents = 0;
                foreach ($participantUuids as $idx => $participantUuid) {
                    if ($participantUuid === $userUuid) {
                        $userShareCents = $baseShare + ($idx < $remainder ? 1 : 0);
                        break;
                    }
                }

                $impactCents = $payerUuid === $userUuid
                    ? ($totalCents - $userShareCents)
                    : (-1 * $userShareCents);

                return [
                    'id' => (string) $expense->uuid,
                    'user_id' => $userUuid,
                    'user_name' => $memberUuidToName[$userUuid] ?? null,
                    'type' => 'expense',
                    'description' => 'Expense: '.$expense->title,
                    'amount_cents' => $impactCents,
                    'balance_before_cents' => 0,
                    'balance_after_cents' => 0,
                    'balance_change_cents' => $impactCents,
                    'paid_by_user_name' => $expense->paidByUser?->name,
                    'from_user_name' => null,
                    'to_user_name' => null,
                    'transaction_date' => optional($expense->expense_date)?->toIso8601String(),
                    'created_at' => optional($expense->created_at)?->toIso8601String(),
                ];
            })
            ->filter()
            ->values();

        $settlements = $group->settlements()
            ->with(['fromUser', 'toUser'])
            ->get()
            ->map(function ($settlement) use ($userUuid) {
                $fromUuid = (string) ($settlement->fromUser?->uuid ?? '');
                $toUuid = (string) ($settlement->toUser?->uuid ?? '');
                $amountCents = (int) ($settlement->amount_cents ?? 0);

                if ($fromUuid !== $userUuid && $toUuid !== $userUuid) {
                    return null;
                }

                $impactCents = $fromUuid === $userUuid ? (-1 * $amountCents) : $amountCents;

                return [
                    'id' => (string) $settlement->uuid,
                    'user_id' => $userUuid,
                    'user_name' => $fromUuid === $userUuid
                        ? ($settlement->fromUser?->name ?? null)
                        : ($settlement->toUser?->name ?? null),
                    'type' => 'settlement',
                    'description' => 'Settlement: '.($settlement->fromUser?->name ?? 'Unknown').' -> '.($settlement->toUser?->name ?? 'Unknown'),
                    'amount_cents' => $impactCents,
                    'balance_before_cents' => 0,
                    'balance_after_cents' => 0,
                    'balance_change_cents' => $impactCents,
                    'paid_by_user_name' => null,
                    'from_user_name' => $settlement->fromUser?->name,
                    'to_user_name' => $settlement->toUser?->name,
                    'transaction_date' => optional($settlement->settlement_date)?->toIso8601String(),
                    'created_at' => optional($settlement->created_at)?->toIso8601String(),
                ];
            })
            ->filter()
            ->values();

        return $expenses
            ->concat($settlements)
            ->sortByDesc('transaction_date')
            ->values()
            ->all();
    }
}
