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
                        <th>Auth</th>
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
                                @if(!empty($user->google_id))
                                    <span class="badge" style="display:inline-flex;align-items:center;gap:6px;background:#fff3e0;color:#8a4200;border:1px solid #ffd9a8;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="#EA4335" d="M12 11v3h7.03c-.31 1.64-1.24 3.03-2.64 3.97l2.13 1.65C20.46 17.8 21.5 15.1 21.5 12c0-.67-.06-1.31-.18-1.93z"/>
                                            <path fill="#34A853" d="M12 21.5c2.43 0 4.46-.8 5.95-2.18l-2.13-1.65c-.8.54-1.83.86-3.82.86-2.94 0-5.43-1.98-6.32-4.66l-2.2 1.7A9.5 9.5 0 0 0 12 21.5"/>
                                            <path fill="#4A90E2" d="M5.68 13.87A5.72 5.72 0 0 1 5.33 12c0-.65.12-1.27.35-1.87l-2.2-1.7A9.5 9.5 0 0 0 2.5 12c0 1.53.37 2.98 1.02 4.25z"/>
                                            <path fill="#FBBC05" d="M12 5.47c1.32 0 2.5.46 3.44 1.35l1.88-1.88C16.46 4.06 14.43 3 12 3a9.5 9.5 0 0 0-8.52 5.43l2.2 1.7c.89-2.68 3.38-4.66 6.32-4.66"/>
                                        </svg>
                                        Google
                                    </span>
                                @else
                                    <span class="badge">Password</span>
                                @endif
                            </td>
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
