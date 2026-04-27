@extends('admin.layout', [
    'title' => $group->name . ' Records',
    'subtitle' => 'All receipts, settlements, and participant credit/debit details.',
])

@section('content')
    <div class="actions" style="margin-bottom: 14px;">
        <a href="{{ route('admin.groups.show', $group) }}" class="button">Back To Group</a>
        <a href="{{ route('admin.groups.edit', $group) }}" class="button primary">Edit Group</a>
    </div>

    <div class="grid cards">
        <div class="panel">
            <div class="kicker">Members</div>
            <div class="stat">{{ $group->members->count() }}</div>
        </div>
        <div class="panel">
            <div class="kicker">Expenses</div>
            <div class="stat">{{ $group->expenses->count() }}</div>
        </div>
        <div class="panel">
            <div class="kicker">Settlements</div>
            <div class="stat">{{ $group->settlements->count() }}</div>
        </div>
        <div class="panel">
            <div class="kicker">Statement Records</div>
            <div class="stat">{{ $statements->count() }}</div>
        </div>
    </div>

    <div class="panel" style="margin-top: 18px;">
        <h2>Participant Credit / Debit</h2>
        @if(empty($snapshot['summaries']))
            <div class="empty">No participant balance data found.</div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Participant</th>
                        <th>Total Credit</th>
                        <th>Total Debit</th>
                        <th>Net</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @php($summaryByUuid = collect($snapshot['summaries'])->keyBy('user_id'))
                    @foreach($summaryByUuid as $userUuid => $summary)
                        @php
                            $creditCents = array_sum($summary['owed_by'] ?? []);
                            $debitCents = array_sum($summary['owes'] ?? []);
                            $netCents = (int) ($summary['net_balance_cents'] ?? 0);
                        @endphp
                        <tr>
                            <td><strong>{{ $summary['user_name'] ?? $userUuid }}</strong></td>
                            <td>{{ number_format($creditCents / 100, 2) }}</td>
                            <td>{{ number_format($debitCents / 100, 2) }}</td>
                            <td>{{ $netCents >= 0 ? '+' : '' }}{{ number_format($netCents / 100, 2) }}</td>
                            <td>
                                @php
                                    $owesText = collect($summary['owes'] ?? [])->map(function ($amount, $otherUuid) use ($summaryByUuid) {
                                        $name = data_get($summaryByUuid->get($otherUuid), 'user_name', $otherUuid);
                                        return "owes {$name}: " . number_format(((int) $amount) / 100, 2);
                                    })->values()->all();
                                    $owedByText = collect($summary['owed_by'] ?? [])->map(function ($amount, $otherUuid) use ($summaryByUuid) {
                                        $name = data_get($summaryByUuid->get($otherUuid), 'user_name', $otherUuid);
                                        return "gets from {$name}: " . number_format(((int) $amount) / 100, 2);
                                    })->values()->all();
                                    $allText = array_merge($owesText, $owedByText);
                                @endphp
                                @if(empty($allText))
                                    <span class="muted">No dues</span>
                                @else
                                    <div class="stack">
                                        @foreach($allText as $line)
                                            <div class="muted">• {{ $line }}</div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="panel" style="margin-top: 18px;">
        <h2>All Receipts / Expenses By Members</h2>
        @if($group->expenses->isEmpty())
            <div class="empty">No expenses found for this group.</div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Added By</th>
                        <th>Title</th>
                        <th>Participants</th>
                        <th>Amount</th>
                        <th>Split Per Participant</th>
                        <th>Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group->expenses as $expense)
                        @php
                            $participantUuids = collect($expense->participant_ids ?? [])->filter()->values();
                            if ($participantUuids->isEmpty()) {
                                $participantUuids = $group->members->pluck('uuid')->sort()->values();
                            } else {
                                $participantUuids = $participantUuids->sort()->values();
                            }

                            $participantNames = $participantUuids->map(function ($uuid) use ($group) {
                                return optional($group->members->firstWhere('uuid', $uuid))->name ?? $uuid;
                            })->all();

                            $participantCount = max(1, $participantUuids->count());
                            $totalCents = (int) ($expense->amount_cents ?? 0);
                            $baseShare = intdiv($totalCents, $participantCount);
                            $remainder = $totalCents % $participantCount;

                            $splits = [];
                            foreach ($participantUuids as $index => $uuid) {
                                $share = $baseShare + ($index < $remainder ? 1 : 0);
                                $name = optional($group->members->firstWhere('uuid', $uuid))->name ?? $uuid;
                                $splits[] = $name . ': ' . number_format($share / 100, 2);
                            }
                        @endphp
                        <tr>
                            <td>{{ optional($expense->expense_date)->format('Y-m-d') ?: optional($expense->created_at)->format('Y-m-d') }}</td>
                            <td>{{ optional($expense->paidByUser)->name ?: 'Unknown' }}</td>
                            <td>{{ $expense->title ?: $expense->description }}</td>
                            <td>{{ implode(', ', $participantNames) }}</td>
                            <td>{{ number_format(($expense->amount_cents ?? 0) / 100, 2) }}</td>
                            <td>
                                <div class="stack">
                                    @foreach($splits as $splitLine)
                                        <div class="muted">{{ $splitLine }}</div>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if($expense->receipt_photo)
                                    <a href="{{ url('storage/'.$expense->receipt_photo) }}" target="_blank">View</a>
                                @else
                                    <span class="muted">None</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="panel" style="margin-top: 18px;">
        <h2>All Settlements By Members</h2>
        @if($group->settlements->isEmpty())
            <div class="empty">No settlements found for this group.</div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Amount</th>
                        <th>Proof</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group->settlements as $settlement)
                        <tr>
                            <td>{{ optional($settlement->settlement_date)->format('Y-m-d') }}</td>
                            <td>{{ optional($settlement->fromUser)->name ?: 'Unknown' }}</td>
                            <td>{{ optional($settlement->toUser)->name ?: 'Unknown' }}</td>
                            <td>{{ number_format(($settlement->amount_cents ?? 0) / 100, 2) }}</td>
                            <td>
                                @if($settlement->proof_photo)
                                    <a href="{{ url('storage/'.$settlement->proof_photo) }}" target="_blank">View</a>
                                @else
                                    <span class="muted">None</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="panel" style="margin-top: 18px;">
        <h2>All Statement Records</h2>
        @if($statements->isEmpty())
            <div class="empty">No statement records found.</div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Member</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Ref</th>
                        <th>Amount</th>
                        <th>Before</th>
                        <th>After</th>
                        <th>Change</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statements as $statement)
                        <tr>
                            <td>{{ optional($statement->transaction_date)->format('Y-m-d H:i') }}</td>
                            <td>{{ optional($statement->user)->name ?: $statement->user_id }}</td>
                            <td>{{ $statement->transaction_type }}</td>
                            <td>{{ $statement->description }}</td>
                            <td>{{ $statement->reference_number ?: '-' }}</td>
                            <td>{{ number_format(($statement->amount_cents ?? 0) / 100, 2) }}</td>
                            <td>{{ number_format(($statement->balance_before_cents ?? 0) / 100, 2) }}</td>
                            <td>{{ number_format(($statement->balance_after_cents ?? 0) / 100, 2) }}</td>
                            <td>{{ number_format(($statement->balance_change_cents ?? 0) / 100, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
