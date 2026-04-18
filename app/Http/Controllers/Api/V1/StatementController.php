<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Services\BalanceService;
use App\Support\ApiPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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

        // Convert user UUID to ID if provided
        $userId = null;
        if ($validated['user_id'] ?? false) {
            $user = \App\Models\User::where('uuid', $validated['user_id'])->first();
            if ($user) {
                $userId = $user->id;
            }
        }

        $query = $group->statementRecords()
            ->with('user')
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

        $items = $this->buildFallbackFeed($group, $validated['user_id'] ?? null);

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
                    'type' => 'expense',
                    'description' => 'Expense: '.$expense->title,
                    'amount_cents' => (int) ($expense->amount_cents ?? 0),
                    'balance_before_cents' => 0,
                    'balance_after_cents' => 0,
                    'balance_change_cents' => 0,
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
                    'type' => 'settlement',
                    'description' => 'Settlement: '.($settlement->fromUser?->name ?? 'Unknown').' -> '.($settlement->toUser?->name ?? 'Unknown'),
                    'amount_cents' => (int) ($settlement->amount_cents ?? 0),
                    'balance_before_cents' => 0,
                    'balance_after_cents' => 0,
                    'balance_change_cents' => 0,
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
}
