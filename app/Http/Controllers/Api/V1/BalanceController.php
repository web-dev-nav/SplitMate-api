<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Services\BalanceService;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function __construct(private BalanceService $balanceService)
    {
    }

    /**
     * Get balance snapshot for a group.
     * Returns per-user summaries and payment suggestions.
     */
    public function snapshot(Request $request, Group $group)
    {
        $snapshot = $this->balanceService->calculateSnapshot($group);
        $summaries = collect($snapshot['summaries'] ?? [])
            ->map(function (array $summary): array {
                $summary['owes'] = (object) ($summary['owes'] ?? []);
                $summary['owed_by'] = (object) ($summary['owed_by'] ?? []);

                return $summary;
            })
            ->values()
            ->all();

        return response()->json([
            'summaries' => $summaries,
            'suggestions' => array_values($snapshot['suggestions']),
        ]);
    }
}
