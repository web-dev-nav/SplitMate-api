@extends('admin.layout')

@section('content')

@if(session('error'))
    <div style="margin-bottom:18px;padding:12px 14px;border-radius:12px;background:var(--danger-soft);color:var(--danger);">
        {{ session('error') }}
    </div>
@endif

<div class="grid two" style="gap:24px;">

    {{-- SMTP Configuration --}}
    <div class="panel" style="grid-column:1/-1;">
        <h2 style="margin:0 0 4px;">SMTP Email Configuration</h2>
        <p class="muted" style="margin:0 0 20px;font-size:14px;">
            These settings control all outbound emails (expense notifications, invitations, password resets).
            Values saved here override the <code>.env</code> file at runtime.
        </p>

        <form method="POST" action="{{ route('admin.settings.smtp') }}">
            @csrf

            <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-bottom:16px;">

                <div>
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Mailer Driver</label>
                    <select name="mail_mailer" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:12px;background:#fff;font-size:14px;">
                        @foreach(['smtp'=>'SMTP','sendmail'=>'Sendmail','log'=>'Log (dev/test)','array'=>'Array (no-send)'] as $val=>$label)
                            <option value="{{ $val }}" {{ ($smtp['mail_mailer'] ?? 'smtp') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">SMTP Host</label>
                    <input type="text" name="mail_host" value="{{ $smtp['mail_host'] ?? '' }}"
                           placeholder="smtp.hostinger.com"
                           style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:12px;font-size:14px;box-sizing:border-box;">
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Port</label>
                    <input type="number" name="mail_port" value="{{ $smtp['mail_port'] ?? '587' }}"
                           placeholder="587"
                           style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:12px;font-size:14px;box-sizing:border-box;">
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Encryption</label>
                    <select name="mail_encryption" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:12px;background:#fff;font-size:14px;">
                        <option value=""   {{ ($smtp['mail_encryption'] ?? '') === ''      ? 'selected' : '' }}>None</option>
                        <option value="tls" {{ ($smtp['mail_encryption'] ?? '') === 'tls'  ? 'selected' : '' }}>TLS (STARTTLS · port 587)</option>
                        <option value="ssl" {{ ($smtp['mail_encryption'] ?? '') === 'ssl'  ? 'selected' : '' }}>SSL (port 465)</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Username</label>
                    <input type="text" name="mail_username" value="{{ $smtp['mail_username'] ?? '' }}"
                           placeholder="you@yourdomain.com" autocomplete="off"
                           style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:12px;font-size:14px;box-sizing:border-box;">
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">
                        Password
                        @if(!empty($smtp['mail_password']))
                            <span class="badge success" style="margin-left:6px;">Saved</span>
                        @endif
                    </label>
                    <input type="password" name="mail_password" value=""
                           placeholder="{{ !empty($smtp['mail_password']) ? '••••••••  (leave blank to keep)' : 'Enter password' }}"
                           autocomplete="new-password"
                           style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:12px;font-size:14px;box-sizing:border-box;">
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">From Address</label>
                    <input type="email" name="mail_from_address" value="{{ $smtp['mail_from_address'] ?? '' }}"
                           placeholder="noreply@yourdomain.com"
                           style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:12px;font-size:14px;box-sizing:border-box;">
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">From Name</label>
                    <input type="text" name="mail_from_name" value="{{ $smtp['mail_from_name'] ?? 'SplitMate' }}"
                           placeholder="SplitMate"
                           style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:12px;font-size:14px;box-sizing:border-box;">
                </div>

            </div>

            @if($errors->any())
                <div style="margin-bottom:14px;padding:12px 14px;border-radius:12px;background:var(--danger-soft);color:var(--danger);font-size:13px;">
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin:6px 0 0;padding-left:18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <button type="submit" class="button primary" style="margin-top:4px;">Save SMTP Settings</button>
        </form>
    </div>

    {{-- Send Test Email --}}
    <div class="panel">
        <h2 style="margin:0 0 4px;">Send Test Email</h2>
        <p class="muted" style="margin:0 0 16px;font-size:14px;">
            Verify that your SMTP settings are working by sending a test message.
        </p>
        <form method="POST" action="{{ route('admin.settings.smtp.test') }}">
            @csrf
            <div style="display:flex;gap:10px;align-items:flex-end;">
                <div style="flex:1;">
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Recipient email</label>
                    <input type="email" name="test_email" value="{{ $smtp['mail_from_address'] ?? '' }}"
                           placeholder="test@example.com"
                           style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:12px;font-size:14px;box-sizing:border-box;">
                </div>
                <button type="submit" class="button" style="white-space:nowrap;padding:11px 18px;">Send Test</button>
            </div>
        </form>
    </div>

    {{-- Current .env reference --}}
    <div class="panel">
        <h2 style="margin:0 0 4px;">Current .env Reference</h2>
        <p class="muted" style="margin:0 0 14px;font-size:14px;">
            Values currently loaded from the environment file (before any DB overrides).
            Edit the DB settings above to override these without touching the server.
        </p>
        <table class="table" style="font-size:13px;">
            <tbody>
                @foreach([
                    'MAIL_MAILER'       => config('mail.default'),
                    'MAIL_HOST'         => env('MAIL_HOST', '—'),
                    'MAIL_PORT'         => env('MAIL_PORT', '—'),
                    'MAIL_ENCRYPTION'   => env('MAIL_SCHEME', env('MAIL_ENCRYPTION', '—')),
                    'MAIL_USERNAME'     => env('MAIL_USERNAME', '—'),
                    'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS', '—'),
                    'MAIL_FROM_NAME'    => env('MAIL_FROM_NAME', '—'),
                ] as $key => $value)
                <tr>
                    <td><code>{{ $key }}</code></td>
                    <td style="color:var(--muted);">{{ $value }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
