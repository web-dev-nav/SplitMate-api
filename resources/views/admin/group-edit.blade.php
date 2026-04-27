@extends('admin.layout', [
    'title' => 'Edit Group',
    'subtitle' => 'Update group details and ownership.',
])

@section('content')
    <div class="panel" style="max-width: 760px;">
        <h2>Edit Group</h2>

        @if($errors->any())
            <div class="badge danger" style="display:block; border-radius:12px; padding:12px 14px; margin-bottom:14px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.groups.update', $group) }}" class="stack">
            @csrf

            <label class="stack">
                <span class="kicker">Group Name</span>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $group->name) }}"
                    required
                    maxlength="255"
                    style="padding:10px 12px; border:1px solid var(--line); border-radius:12px;"
                >
            </label>

            <label class="stack">
                <span class="kicker">Currency Code</span>
                <input
                    type="text"
                    name="currency_code"
                    value="{{ old('currency_code', $group->currency_code) }}"
                    required
                    maxlength="3"
                    style="padding:10px 12px; border:1px solid var(--line); border-radius:12px; text-transform:uppercase;"
                >
            </label>

            <label class="stack">
                <span class="kicker">Owner</span>
                <select name="owner_user_id" required style="padding:10px 12px; border:1px solid var(--line); border-radius:12px;">
                    @foreach($group->members as $member)
                        <option
                            value="{{ $member->uuid }}"
                            @selected(old('owner_user_id', optional($group->creator)->uuid) === $member->uuid)
                        >
                            {{ $member->name }} ({{ $member->email ?: 'no-email' }})
                        </option>
                    @endforeach
                </select>
            </label>

            <div class="actions">
                <button type="submit" class="button primary">Save Group</button>
                <a href="{{ route('admin.groups') }}" class="button">Cancel</a>
            </div>
        </form>
    </div>
@endsection
