<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Splitmate Admin Login</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: linear-gradient(135deg, #0f172a, #1d4ed8); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .card { width: min(420px, calc(100vw - 32px)); background: #fff; border-radius: 24px; padding: 32px; box-shadow: 0 30px 70px rgba(15, 23, 42, .28); }
        h1 { margin: 0 0 10px; font-size: 28px; color: #0f172a; }
        p { margin: 0 0 24px; color: #475569; }
        label { display: block; margin-bottom: 6px; font-size: 14px; color: #334155; font-weight: 600; }
        input { width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 12px; margin-bottom: 18px; box-sizing: border-box; }
        button { width: 100%; padding: 13px 16px; border: 0; border-radius: 12px; background: #1d4ed8; color: #fff; font-weight: 700; cursor: pointer; }
        .hint { margin-top: 14px; font-size: 13px; color: #64748b; }
        .error { margin-bottom: 16px; padding: 12px 14px; border-radius: 12px; background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>
    <form class="card" method="POST" action="{{ route('admin.login.submit') }}">
        @csrf
        <h1>Splitmate Admin</h1>
        <p>Use the admin credentials from your Laravel environment to manage the mobile backend.</p>

        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <label for="email">Admin Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>

        <label for="password">Admin Password</label>
        <input id="password" type="password" name="password" required>

        <button type="submit">Sign In</button>

        <div class="hint">
            Default credentials can be overridden with <span style="font-family: ui-monospace, monospace;">ADMIN_PANEL_EMAIL</span> and <span style="font-family: ui-monospace, monospace;">ADMIN_PANEL_PASSWORD</span>.
        </div>
    </form>
</body>
</html>
