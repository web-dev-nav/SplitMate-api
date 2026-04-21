@extends('admin.layout', [
    'title' => 'Users',
    'subtitle' => 'Application users synced to the iOS client.',
])

@section('content')
    <div class="panel">
        <h2>All Users</h2>
        @if($users->isEmpty())
            <div class="empty">No users found.</div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->name }}</strong>
                                <div class="muted"><span class="code">{{ $user->uuid }}</span></div>
                            </td>
                            <td>{{ $user->email ?: 'No email' }}</td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'success' : 'warn' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ optional($user->created_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                                        @csrf
                                        <button class="button {{ $user->is_active ? 'warn' : 'success' }}" type="submit">
                                            {{ $user->is_active ? 'Deactivate' : 'Reactivate' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.users.delete', $user) }}"
                                          onsubmit="return confirm('Delete this user account? This cannot be undone.');">
                                        @csrf
                                        <button class="button danger" type="submit">
                                            Delete User
                                        </button>
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
