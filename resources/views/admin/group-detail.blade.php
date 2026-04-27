@extends('admin.layout', [
    'title' => $group->name,
    'subtitle' => 'Group-level ledger, balances, and statements.',
])

@section('content')
    <div class="panel" style="margin-bottom: 18px;">
        <h2>Danger Zone</h2>
        <div class="actions" style="margin-bottom: 10px;">
            <a href="{{ route('admin.groups.edit', $group) }}" class="button primary">Edit Group</a>
        </div>
        <form method="POST" action="{{ url('/admin/groups/'.$group->id.'/delete') }}"
              onsubmit="return confirm('WARNING: Delete group {{ addslashes($group->name) }}?\\n\\nThis permanently deletes group, members links, expenses, settlements, and statements.\\n\\nThis action cannot be undone.');">
            @csrf
            <button class="button danger" type="submit">Delete Group</button>
        </form>
    </div>

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
                <table class="table">
                    <thead>
                        <tr>
                            <th>Payer</th>
                            <th>Payee</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Proof</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group->settlements->sortByDesc('settlement_date') as $settlement)
                            <tr>
                                <td>{{ optional($settlement->fromUser)->name ?: 'Unknown' }}</td>
                                <td>{{ optional($settlement->toUser)->name ?: 'Unknown' }}</td>
                                <td>{{ number_format(($settlement->amount_cents ?? 0) / 100, 2) }}</td>
                                <td>{{ optional($settlement->settlement_date)->format('Y-m-d') }}</td>
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
    </div>

    <div class="panel" style="margin-top: 18px;">
        <h2>Statements (All Records)</h2>
        @if($statements->isEmpty())
            <div class="empty">No statement records have been generated yet.</div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Ref</th>
                        <th>Amount</th>
                        <th>Before</th>
                        <th>After</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statements as $statement)
                        <tr>
                            <td>{{ optional($statement->user)->name ?: $statement->user_id }}</td>
                            <td>{{ $statement->transaction_type }}</td>
                            <td>{{ $statement->description }}</td>
                            <td>{{ $statement->reference_number ?: '-' }}</td>
                            <td>{{ number_format(($statement->amount_cents ?? 0) / 100, 2) }}</td>
                            <td>{{ number_format(($statement->balance_before_cents ?? 0) / 100, 2) }}</td>
                            <td>{{ number_format(($statement->balance_after_cents ?? 0) / 100, 2) }}</td>
                            <td>{{ $statement->status ?: '-' }}</td>
                            <td>{{ optional($statement->transaction_date)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
