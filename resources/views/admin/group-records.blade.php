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
            <div class="stat">{{ $members->count() }}</div>
        </div>
        <div class="panel">
            <div class="kicker">Expenses</div>
            <div class="stat">{{ $expenses->total() }}</div>
        </div>
        <div class="panel">
            <div class="kicker">Settlements</div>
            <div class="stat">{{ $settlements->total() }}</div>
        </div>
        <div class="panel">
            <div class="kicker">Statement Records</div>
            <div class="stat">{{ $statements->total() }}</div>
        </div>
    </div>

    <div class="panel" style="margin-top: 18px;">
        <h2>Participant Credit / Debit</h2>
        @if(empty($snapshot['summaries']))
            <div class="empty">No participant balance data found.</div>
        @else
            @php
                $summaryByUuid = collect($snapshot['summaries'])->keyBy('user_id');
            @endphp
            <div class="grid two">
                @foreach($summaryByUuid as $userUuid => $summary)
                    @php
                        $creditCents = (int) array_sum($summary['owed_by'] ?? []);
                        $debitCents = (int) array_sum($summary['owes'] ?? []);
                        $netCents = (int) ($summary['net_balance_cents'] ?? 0);
                    @endphp
                    <div class="panel" style="border-radius: 14px; padding: 16px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:10px;">
                            <strong style="font-size:16px;">{{ $summary['user_name'] ?? $userUuid }}</strong>
                            <span class="badge {{ $netCents >= 0 ? 'success' : 'warn' }}">
                                Net {{ $netCents >= 0 ? '+' : '' }}{{ number_format($netCents / 100, 2) }}
                            </span>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
                            <div style="background:#ecfdf5; border:1px solid #bbf7d0; border-radius:10px; padding:10px;">
                                <div class="kicker">You Get</div>
                                <div style="font-weight:700; color:#166534;">{{ number_format($creditCents / 100, 2) }}</div>
                            </div>
                            <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:10px;">
                                <div class="kicker">You Owe</div>
                                <div style="font-weight:700; color:#9a3412;">{{ number_format($debitCents / 100, 2) }}</div>
                            </div>
                        </div>

                        <div class="stack" style="gap:8px;">
                            @php
                                $owesLines = collect($summary['owes'] ?? [])->map(function ($amount, $otherUuid) use ($summaryByUuid) {
                                    $name = data_get($summaryByUuid->get($otherUuid), 'user_name', $otherUuid);
                                    return "Owes {$name}: " . number_format(((int) $amount) / 100, 2);
                                })->values();

                                $getsLines = collect($summary['owed_by'] ?? [])->map(function ($amount, $otherUuid) use ($summaryByUuid) {
                                    $name = data_get($summaryByUuid->get($otherUuid), 'user_name', $otherUuid);
                                    return "Gets from {$name}: " . number_format(((int) $amount) / 100, 2);
                                })->values();
                            @endphp

                            @if($owesLines->isEmpty() && $getsLines->isEmpty())
                                <div class="muted">No dues right now.</div>
                            @else
                                @foreach($getsLines as $line)
                                    <div style="padding:8px 10px; border-radius:8px; background:#f0fdf4; color:#14532d;">{{ $line }}</div>
                                @endforeach
                                @foreach($owesLines as $line)
                                    <div style="padding:8px 10px; border-radius:8px; background:#fff7ed; color:#7c2d12;">{{ $line }}</div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="panel" style="margin-top: 18px;">
        <h2>All Receipts / Expenses By Members</h2>
        @if($expenses->isEmpty())
            <div class="empty">No expenses found for this group.</div>
        @else
            <div style="border:1px solid var(--line); border-radius:14px; overflow:hidden;">
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
                    @foreach($expenses as $expense)
                        @php
                            $participantUuids = collect($expense->participant_ids ?? [])->filter()->values();
                            if ($participantUuids->isEmpty()) {
                                $participantUuids = $members->pluck('uuid')->sort()->values();
                            } else {
                                $participantUuids = $participantUuids->sort()->values();
                            }

                            $participantNames = $participantUuids->map(function ($uuid) use ($members) {
                                return optional($members->firstWhere('uuid', $uuid))->name ?? $uuid;
                            })->all();

                            $participantCount = max(1, $participantUuids->count());
                            $totalCents = (int) ($expense->amount_cents ?? 0);
                            $baseShare = intdiv($totalCents, $participantCount);
                            $remainder = $totalCents % $participantCount;

                            $splits = [];
                            foreach ($participantUuids as $index => $uuid) {
                                $share = $baseShare + ($index < $remainder ? 1 : 0);
                                $name = optional($members->firstWhere('uuid', $uuid))->name ?? $uuid;
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
                                    <a href="{{ route('media.public', ['path' => $expense->receipt_photo]) }}" target="_blank">View</a>
                                @else
                                    <span class="muted">None</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @if($expenses->lastPage() > 1)
                <div style="margin-top: 12px; display:flex; justify-content:center; align-items:center; gap:10px; flex-wrap:wrap;">
                    @if($expenses->onFirstPage())
                        <span class="button" style="opacity:.45; pointer-events:none;">Previous</span>
                    @else
                        <a href="{{ $expenses->previousPageUrl() }}" class="button">Previous</a>
                    @endif
                    <span class="muted">Page {{ $expenses->currentPage() }} of {{ $expenses->lastPage() }}</span>
                    @if($expenses->hasMorePages())
                        <a href="{{ $expenses->nextPageUrl() }}" class="button">Next</a>
                    @else
                        <span class="button" style="opacity:.45; pointer-events:none;">Next</span>
                    @endif
                </div>
            @endif
        @endif
    </div>

    <div class="panel" style="margin-top: 18px;">
        <h2>All Settlements By Members</h2>
        @if($settlements->isEmpty())
            <div class="empty">No settlements found for this group.</div>
        @else
            <div style="border:1px solid var(--line); border-radius:14px; overflow:hidden;">
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
                    @foreach($settlements as $settlement)
                        <tr>
                            <td>{{ optional($settlement->settlement_date)->format('Y-m-d') }}</td>
                            <td>{{ optional($settlement->fromUser)->name ?: 'Unknown' }}</td>
                            <td>{{ optional($settlement->toUser)->name ?: 'Unknown' }}</td>
                            <td>{{ number_format(($settlement->amount_cents ?? 0) / 100, 2) }}</td>
                            <td>
                                @if($settlement->proof_photo)
                                    <a href="{{ route('media.public', ['path' => $settlement->proof_photo]) }}" target="_blank">View</a>
                                @else
                                    <span class="muted">None</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @if($settlements->lastPage() > 1)
                <div style="margin-top: 12px; display:flex; justify-content:center; align-items:center; gap:10px; flex-wrap:wrap;">
                    @if($settlements->onFirstPage())
                        <span class="button" style="opacity:.45; pointer-events:none;">Previous</span>
                    @else
                        <a href="{{ $settlements->previousPageUrl() }}" class="button">Previous</a>
                    @endif
                    <span class="muted">Page {{ $settlements->currentPage() }} of {{ $settlements->lastPage() }}</span>
                    @if($settlements->hasMorePages())
                        <a href="{{ $settlements->nextPageUrl() }}" class="button">Next</a>
                    @else
                        <span class="button" style="opacity:.45; pointer-events:none;">Next</span>
                    @endif
                </div>
            @endif
        @endif
    </div>

    <div class="panel" style="margin-top: 18px;">
        <h2>Member Activity Timeline (Easy View)</h2>
        @if($statements->isEmpty())
            <div class="empty">No statement records found.</div>
        @else
            <div class="stack">
                @foreach($statements as $statement)
                    @php
                        $changeCents = (int) ($statement->balance_change_cents ?? 0);
                        $afterCents = (int) ($statement->balance_after_cents ?? 0);
                        $changeLabel = $changeCents > 0 ? 'Credit' : ($changeCents < 0 ? 'Debit' : 'No Change');
                        $changeClass = $changeCents > 0 ? 'success' : ($changeCents < 0 ? 'warn' : 'primary');
                    @endphp
                    <div class="panel" style="border-radius: 14px; padding: 14px;">
                        <div style="display:flex; justify-content:space-between; gap:10px; align-items:center; flex-wrap:wrap;">
                            <div>
                                <strong>{{ optional($statement->user)->name ?: 'Unknown member' }}</strong>
                                <div class="muted">{{ optional($statement->transaction_date)->format('Y-m-d H:i') }}</div>
                            </div>
                            <span class="badge {{ $changeClass }}">
                                {{ $changeLabel }} {{ $changeCents > 0 ? '+' : '' }}{{ number_format($changeCents / 100, 2) }}
                            </span>
                        </div>

                        <div style="margin-top:10px;">
                            <strong>{{ ucfirst((string) $statement->transaction_type) }}:</strong>
                            {{ $statement->description ?: 'Activity record' }}
                        </div>

                        <div class="muted" style="margin-top:6px;">
                            Ref: {{ $statement->reference_number ?: '-' }} |
                            Balance now: {{ $afterCents > 0 ? '+' : '' }}{{ number_format($afterCents / 100, 2) }}
                        </div>
                    </div>
                @endforeach
            </div>
            @if($statements->lastPage() > 1)
                <div style="margin-top: 12px; display:flex; justify-content:center; align-items:center; gap:10px; flex-wrap:wrap;">
                    @if($statements->onFirstPage())
                        <span class="button" style="opacity:.45; pointer-events:none;">Previous</span>
                    @else
                        <a href="{{ $statements->previousPageUrl() }}" class="button">Previous</a>
                    @endif
                    <span class="muted">Page {{ $statements->currentPage() }} of {{ $statements->lastPage() }}</span>
                    @if($statements->hasMorePages())
                        <a href="{{ $statements->nextPageUrl() }}" class="button">Next</a>
                    @else
                        <span class="button" style="opacity:.45; pointer-events:none;">Next</span>
                    @endif
                </div>
            @endif
        @endif
    </div>
@endsection
