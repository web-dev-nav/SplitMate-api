<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Services\BalanceService;
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

        // Convert user UUID to ID if provided
        $userId = null;
        if ($validated['user_id'] ?? false) {
            $user = \App\Models\User::where('uuid', $validated['user_id'])->first();
            if ($user) {
                $userId = $user->id;
            }
        }

        // Get statements
        $statements = $group->statementRecords()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->orderBy('transaction_date', 'desc')
            ->paginate(50);

        return response()->json([
            'statements' => $statements->items(),
            'pagination' => [
                'total' => $statements->total(),
                'per_page' => $statements->perPage(),
                'current_page' => $statements->currentPage(),
                'last_page' => $statements->lastPage(),
            ],
        ]);
    }
}
