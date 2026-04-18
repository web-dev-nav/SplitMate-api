@extends('admin.layout', [
    'title' => 'Overview',
    'subtitle' => 'Live state of the Laravel backend that powers the iOS app.',
])

@section('content')
    <section class="grid cards">
        @foreach($stats as $label => $value)
            <div class="panel">
                <div class="kicker">{{ ucfirst($label) }}</div>
                <div class="stat">{{ number_format($value) }}</div>
            </div>
        @endforeach
    </section>

    <section class="grid two" style="margin-top: 18px;">
        <div class="panel">
            <h2>Groups</h2>
            @if($groups->isEmpty())
                <div class="empty">No groups have been created yet.</div>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Currency</th>
                            <th>Members</th>
                            <th>Activity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groups as $group)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.groups.show', $group) }}"><strong>{{ $group->name }}</strong></a>
                                    <div class="muted">{{ $group->invite_code }}</div>
                                </td>
                                <td>{{ $group->currency_code }}</td>
                                <td>{{ $group->members_count }}</td>
                                <td>{{ $group->expenses_count }} expenses / {{ $group->settlements_count }} settlements</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="stack">
            <div class="panel">
                <h2>Recent Expenses</h2>
                @if($recentExpenses->isEmpty())
                    <div class="empty">No expenses yet.</div>
                @else
                    <div class="stack">
                        @foreach($recentExpenses as $expense)
                            <div>
                                <strong>{{ $expense->title }}</strong>
                                <div class="muted">{{ optional($expense->group)->name }} · {{ optional($expense->paidByUser)->name }} · {{ number_format(($expense->amount_cents ?? 0) / 100, 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="panel">
                <h2>Recent Settlements</h2>
                @if($recentSettlements->isEmpty())
                    <div class="empty">No settlements yet.</div>
                @else
                    <div class="stack">
                        @foreach($recentSettlements as $settlement)
                            <div>
                                <strong>{{ optional($settlement->fromUser)->name }} → {{ optional($settlement->toUser)->name }}</strong>
                                <div class="muted">{{ optional($settlement->group)->name }} · {{ number_format(($settlement->amount_cents ?? 0) / 100, 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
