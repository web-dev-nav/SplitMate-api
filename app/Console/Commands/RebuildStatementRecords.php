<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\StatementRecord;
use App\Services\BalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildStatementRecords extends Command
{
    protected $signature = 'statements:rebuild {groupId? : Optional group ID to rebuild only one group}';

    protected $description = 'Rebuild statement records chronologically using the current balance logic';

    public function __construct(private BalanceService $balanceService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $groupId = $this->argument('groupId');

        $groups = Group::query()
            ->when($groupId, fn ($query) => $query->where('id', $groupId))
            ->with(['expenses', 'settlements'])
            ->get();

        if ($groups->isEmpty()) {
            $this->error($groupId ? "Group {$groupId} not found." : 'No groups found.');
            return self::FAILURE;
        }

        foreach ($groups as $group) {
            $this->info("Rebuilding statements for group {$group->name} ({$group->id})");

            DB::transaction(function () use ($group) {
                StatementRecord::where('group_id', $group->id)->delete();

                $timeline = collect();

                foreach ($group->expenses as $expense) {
                    $timeline->push([
                        'type' => 'expense',
                        'id' => $expense->id,
                        'created_at' => $expense->created_at,
                    ]);
                }

                foreach ($group->settlements as $settlement) {
                    $timeline->push([
                        'type' => 'settlement',
                        'id' => $settlement->id,
                        'created_at' => $settlement->created_at,
                    ]);
                }

                $timeline = $timeline
                    ->sortBy([
                        ['created_at', 'asc'],
                        ['type', 'asc'],
                        ['id', 'asc'],
                    ])
                    ->values();

                foreach ($timeline as $entry) {
                    if ($entry['type'] === 'expense') {
                        $expense = $group->expenses->firstWhere('id', $entry['id']);
                        if ($expense) {
                            $this->balanceService->createStatementRecords($group, expense: $expense);
                        }
                        continue;
                    }

                    $settlement = $group->settlements->firstWhere('id', $entry['id']);
                    if ($settlement) {
                        $this->balanceService->createStatementRecords($group, settlement: $settlement);
                    }
                }
            });
        }

        $this->info('Statement rebuild complete.');

        return self::SUCCESS;
    }
}
