<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Settlement;
use App\Models\StatementRecord;
use App\Models\User;
use App\Services\BalanceService;
use App\Support\ApiPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'proof_photo' => 'required|image|max:15360',
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

        // Create settlement
        $proofPath = $request->file('proof_photo')->store('settlement-proofs', 'public');

        $settlement = DB::transaction(function () use ($group, $validated, $fromUser, $toUser, $proofPath) {
            $settlement = Settlement::create([
                'uuid' => Str::uuid(),
                'group_id' => $group->id,
                'from_user_id' => $fromUser->id,
                'to_user_id' => $toUser->id,
                'amount_cents' => $validated['amount_cents'],
                // Backward compatibility: some databases still enforce legacy decimal `amount`.
                'amount' => $validated['amount_cents'] / 100,
                'settlement_date' => $validated['settlement_date'],
                'proof_photo' => $proofPath,
            ]);

            $this->balanceService->createStatementRecords($group, settlement: $settlement);

            return $settlement;
        });
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
     * Delete settlement record (payer only).
     */
    public function destroy(Request $request, Group $group, Settlement $settlement)
    {
        if ($settlement->group_id !== $group->id) {
            return response()->json(['message' => 'Settlement not found in this group'], 404);
        }

        if ((int) $settlement->from_user_id !== (int) $request->user()->id) {
            return response()->json([
                'message' => 'Only the member who created this settlement can delete it.',
            ], 403);
        }

        DB::transaction(function () use ($settlement) {
            StatementRecord::where('settlement_id', $settlement->id)->delete();

            if (!empty($settlement->proof_photo)) {
                Storage::disk('public')->delete((string) $settlement->proof_photo);
            }

            $settlement->delete();
        });

        return response()->json([
            'message' => 'Settlement deleted successfully.',
        ]);
    }
}
