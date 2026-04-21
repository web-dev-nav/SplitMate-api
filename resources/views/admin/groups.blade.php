@extends('admin.layout', [
    'title' => 'Groups',
    'subtitle' => 'Inspect shared ledgers, members, and mobile activity.',
])

@section('content')
    <div class="panel">
        <h2>All Groups</h2>
        @if($groups->isEmpty())
            <div class="empty">No groups available.</div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Owner</th>
                        <th>Invite</th>
                        <th>Counts</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $group)
                        <tr>
                            <td>
                                <a href="{{ route('admin.groups.show', $group) }}"><strong>{{ $group->name }}</strong></a>
                                <div class="muted"><span class="code">{{ $group->id }}</span></div>
                            </td>
                            <td>{{ optional($group->creator)->name ?: 'Unknown' }}</td>
                            <td><span class="badge primary">{{ $group->invite_code }}</span></td>
                            <td>{{ $group->members_count }} members · {{ $group->expenses_count }} expenses · {{ $group->settlements_count }} settlements</td>
                            <td>
                                <form method="POST" action="{{ url('/admin/groups/'.$group->id.'/delete') }}"
                                      onsubmit="return confirm('Delete this group completely? This cannot be undone.');">
                                    @csrf
                                    <button class="button danger" type="submit">Delete Group</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
