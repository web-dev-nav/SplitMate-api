@extends('admin.layout', [
    'title' => $group->name,
    'subtitle' => 'Group-level ledger, balances, and statements.',
])

@section('content')
    <div class="grid cards">
        <div class="panel">
            <div class="kicker">Group Id</div>
            <div class="stat" style="font-size: 18px;">{{ $group->id }}</div>
        </div>
        <div class="panel">
            <div class="kicker">Currency</div>
            <div class="stat">{{ $group->currency_code }}</div>
        </div>
        <div class="panel">
            <div class="kicker">Invite Code</div>
            <div class="stat">{{ $group->invite_code }}</div>
        </div>
        <div class="panel">
            <div class="kicker">Created By</div>
            <div class="stat" style="font-size: 18px;">{{ optional($group->creator)->name ?: 'Unknown' }}</div>
        </div>
    </div>

    <div class="grid two" style="margin-top: 18px;">
        <div class="panel">
            <h2>Members</h2>
            @if($group->members->isEmpty())
                <div class="empty">No members.</div>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group->members as $member)
                            <tr>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->email }}</td>
                                <td>{{ $member->pivot->role }}</td>
                                <td>{{ $member->pivot->is_active ? 'Active' : 'Inactive' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="panel">
            <h2>Balances</h2>
            @if(empty($snapshot['summaries']))
                <div class="empty">No balance data available.</div>
            @else
                <div class="stack">
                    @foreach($snapshot['summaries'] as $summary)
                        <div>
                            <strong>{{ $summary['user_name'] }}</strong>
                            <div class="muted">Net: {{ number_format(($summary['net_balance_cents'] ?? 0) / 100, 2) }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="grid two" style="margin-top: 18px;">
        <div class="panel">
            <h2>Expenses</h2>
            @if($group->expenses->isEmpty())
                <div class="empty">No expenses yet.</div>
            @else
                <div class="stack">
                    @foreach($group->expenses->sortByDesc('created_at') as $expense)
                        <div>
                            <strong>{{ $expense->title }}</strong>
                            <div class="muted">{{ optional($expense->paidByUser)->name }} · {{ number_format(($expense->amount_cents ?? 0) / 100, 2) }} · {{ optional($expense->expense_date)->format('Y-m-d') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="panel">
            <h2>Settlements</h2>
            @if($group->settlements->isEmpty())
                <div class="empty">No settlements yet.</div>
            @else
                <div class="stack">
                    @foreach($group->settlements->sortByDesc('created_at') as $settlement)
                        <div>
                            <strong>{{ optional($settlement->fromUser)->name }} → {{ optional($settlement->toUser)->name }}</strong>
                            <div class="muted">{{ number_format(($settlement->amount_cents ?? 0) / 100, 2) }} · {{ optional($settlement->settlement_date)->format('Y-m-d') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="panel" style="margin-top: 18px;">
        <h2>Statements</h2>
        @if($statements->isEmpty())
            <div class="empty">No statement records have been generated yet.</div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statements as $statement)
                        <tr>
                            <td>{{ optional($statement->user)->name ?: $statement->user_id }}</td>
                            <td>{{ $statement->transaction_type }}</td>
                            <td>{{ $statement->description }}</td>
                            <td>{{ number_format(($statement->amount_cents ?? 0) / 100, 2) }}</td>
                            <td>{{ optional($statement->transaction_date)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
