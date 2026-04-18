<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Settlement;
use App\Models\User;
use App\Services\BalanceService;
use App\Support\ApiPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SettlementController extends Controller
{
    public function __construct(private BalanceService $balanceService)
    {
    }

    /**
     * List all settlements in a group.
     */
    public function index(Request $request, Group $group)
    {
        $settlements = $group->settlements()
            ->with('fromUser', 'toUser')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'settlements' => collect($settlements->items())->map(fn (Settlement $settlement) => ApiPayload::settlement($settlement))->values(),
            'pagination' => [
                'total' => $settlements->total(),
                'per_page' => $settlements->perPage(),
                'current_page' => $settlements->currentPage(),
                'last_page' => $settlements->lastPage(),
            ],
        ]);
    }

    /**
     * Create a new settlement.
     */
    public function store(Request $request, Group $group)
    {
        $validated = $request->validate([
            'from_user_id' => 'required|string|exists:users,uuid',
            'to_user_id' => 'required|string|exists:users,uuid|different:from_user_id',
            'amount_cents' => 'required|integer|min:1',
            'settlement_date' => 'required|date',
        ]);

        // Get users
        $fromUser = User::where('uuid', $validated['from_user_id'])->firstOrFail();
        $toUser = User::where('uuid', $validated['to_user_id'])->firstOrFail();

        // Verify both users are members of the group
        $fromMember = $group->members()->where('user_id', $fromUser->id)->wherePivot('is_active', true)->first();
        $toMember = $group->members()->where('user_id', $toUser->id)->wherePivot('is_active', true)->first();

        if (!$fromMember || !$toMember) {
            return response()->json([
                'message' => 'Both users must be active members of the group',
            ], 400);
        }

        // Validate that payment doesn't exceed outstanding debt
        $maxPayable = $this->balanceService->maxPayable($group, $fromUser->uuid, $toUser->uuid);

        if ($validated['amount_cents'] > $maxPayable) {
            throw ValidationException::withMessages([
                'amount_cents' => ["Payment amount exceeds outstanding debt of {$maxPayable} cents"],
            ]);
        }

        // Create settlement
        $settlement = Settlement::create([
            'uuid' => Str::uuid(),
            'group_id' => $group->id,
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
            'amount_cents' => $validated['amount_cents'],
            'settlement_date' => $validated['settlement_date'],
        ]);
        $settlement->load(['fromUser', 'toUser']);

        return response()->json([
            'settlement' => ApiPayload::settlement($settlement),
        ], 201);
    }

    /**
     * Get settlement details.
     */
    public function show(Request $request, Group $group, Settlement $settlement)
    {
        if ($settlement->group_id !== $group->id) {
            return response()->json(['message' => 'Settlement not found in this group'], 404);
        }

        return response()->json([
            'settlement' => ApiPayload::settlement($settlement->load(['fromUser', 'toUser'])),
        ]);
    }

    /**
     * Get maximum payable amount between two users.
     */
    public function maxPayable(Request $request, Group $group)
    {
        $validated = $request->validate([
            'from_user_id' => 'required|string|exists:users,uuid',
            'to_user_id' => 'required|string|exists:users,uuid',
        ]);

        $fromUser = User::where('uuid', $validated['from_user_id'])->firstOrFail();
        $toUser = User::where('uuid', $validated['to_user_id'])->firstOrFail();

        $maxAmount = $this->balanceService->maxPayable($group, $fromUser->uuid, $toUser->uuid);

        return response()->json([
            'from_user_id' => $fromUser->uuid,
            'to_user_id' => $toUser->uuid,
            'max_payable_cents' => $maxAmount,
        ]);
    }
}
