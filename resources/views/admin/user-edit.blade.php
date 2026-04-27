@extends('admin.layout', [
    'title' => 'Edit User',
    'subtitle' => 'Update account details and access state.',
])

@section('content')
    <div class="panel" style="max-width: 760px;">
        <h2>Edit User</h2>

        @if($errors->any())
            <div class="badge danger" style="display:block; border-radius:12px; padding:12px 14px; margin-bottom:14px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="stack">
            @csrf

            <label class="stack">
                <span class="kicker">Name</span>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                    maxlength="255"
                    style="padding:10px 12px; border:1px solid var(--line); border-radius:12px;"
                >
            </label>

            <label class="stack">
                <span class="kicker">Email</span>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                    maxlength="255"
                    style="padding:10px 12px; border:1px solid var(--line); border-radius:12px;"
                >
            </label>

            <label class="stack">
                <span class="kicker">Status</span>
                <select name="is_active" style="padding:10px 12px; border:1px solid var(--line); border-radius:12px;">
                    <option value="1" @selected(old('is_active', (int) $user->is_active) == 1)>Active</option>
                    <option value="0" @selected(old('is_active', (int) $user->is_active) == 0)>Inactive</option>
                </select>
            </label>

            <label class="stack">
                <span class="kicker">New Password (Optional)</span>
                <input
                    type="password"
                    name="password"
                    minlength="8"
                    autocomplete="new-password"
                    style="padding:10px 12px; border:1px solid var(--line); border-radius:12px;"
                >
            </label>

            <label class="stack">
                <span class="kicker">Confirm New Password</span>
                <input
                    type="password"
                    name="password_confirmation"
                    minlength="8"
                    autocomplete="new-password"
                    style="padding:10px 12px; border:1px solid var(--line); border-radius:12px;"
                >
            </label>

            <div class="actions">
                <button type="submit" class="button primary">Save Changes</button>
                <a href="{{ route('admin.users') }}" class="button">Cancel</a>
            </div>
        </form>
    </div>
@endsection
