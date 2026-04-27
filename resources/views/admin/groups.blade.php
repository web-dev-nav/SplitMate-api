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
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <a class="button" href="{{ route('admin.groups.records', $group) }}">
                                        Records
                                    </a>
                                    <a class="button primary" href="{{ route('admin.groups.edit', $group) }}">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ url('/admin/groups/'.$group->id.'/delete') }}"
                                          onsubmit="return confirm('WARNING: Delete group {{ addslashes($group->name) }}?\\n\\nThis permanently deletes group, members links, expenses, settlements, and statements.\\n\\nThis action cannot be undone.');">
                                        @csrf
                                        <button class="button danger" type="submit">Delete Group</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
