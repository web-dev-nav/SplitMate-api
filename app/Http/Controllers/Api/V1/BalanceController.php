<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Services\BalanceService;

class BalanceController extends Controller
{
    public function __construct(private BalanceService $balanceService)
    {
    }

    /**
     * Get balance snapshot for a group.
     * Returns per-user summaries and payment suggestions.
     */
    public function snapshot($groupId)
    {
        $group = Group::findOrFail($groupId);

        $snapshot = $this->balanceService->calculateSnapshot($group);

        return response()->json([
            'summaries' => array_values($snapshot['summaries']),
            'suggestions' => array_values($snapshot['suggestions']),
        ]);
    }
}
