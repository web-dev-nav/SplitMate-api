<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Splitmate Admin' }}</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f7fb;
            --panel: #ffffff;
            --line: #d9e2ef;
            --text: #132238;
            --muted: #5f7188;
            --primary: #1d4ed8;
            --primary-soft: #dbeafe;
            --success: #166534;
            --success-soft: #dcfce7;
            --warn: #92400e;
            --warn-soft: #fef3c7;
            --danger: #b91c1c;
            --danger-soft: #fee2e2;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: var(--bg); color: var(--text); }
        a { color: inherit; text-decoration: none; }
        .shell { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
        .sidebar { background: #0f172a; color: #e2e8f0; padding: 28px 20px; }
        .brand { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
        .sub { color: #94a3b8; font-size: 14px; margin-bottom: 28px; }
        .nav { display: grid; gap: 8px; }
        .nav a { padding: 12px 14px; border-radius: 12px; color: #cbd5e1; }
        .nav a.active, .nav a:hover { background: rgba(148, 163, 184, 0.16); color: #fff; }
        .content { padding: 28px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .page-title { font-size: 28px; font-weight: 700; margin: 0; }
        .page-copy { margin: 6px 0 0; color: var(--muted); }
        .logout { background: var(--panel); border: 1px solid var(--line); border-radius: 12px; padding: 10px 14px; cursor: pointer; }
        .grid { display: grid; gap: 18px; }
        .grid.cards { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
        .grid.two { grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); }
        .panel { background: var(--panel); border: 1px solid var(--line); border-radius: 18px; padding: 20px; }
        .panel h2 { margin: 0 0 12px; font-size: 18px; }
        .stat { font-size: 30px; font-weight: 700; margin-top: 4px; }
        .muted { color: var(--muted); }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { text-align: left; padding: 12px 10px; border-bottom: 1px solid var(--line); vertical-align: top; }
        .table th { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); }
        .badge { display: inline-flex; align-items: center; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge.success { background: var(--success-soft); color: var(--success); }
        .badge.warn { background: var(--warn-soft); color: var(--warn); }
        .badge.primary { background: var(--primary-soft); color: var(--primary); }
        .badge.danger { background: var(--danger-soft); color: var(--danger); }
        .actions { display: flex; gap: 10px; align-items: center; }
        .button { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 14px; border-radius: 12px; border: 1px solid var(--line); background: var(--panel); cursor: pointer; }
        .button.primary { background: var(--primary); color: #fff; border-color: var(--primary); }
        .button.warn { background: var(--warn-soft); color: var(--warn); border-color: transparent; }
        .button.success { background: var(--success-soft); color: var(--success); border-color: transparent; }
        .button.danger { background: var(--danger-soft); color: var(--danger); border-color: transparent; }
        .stack { display: grid; gap: 12px; }
        .kicker { color: var(--muted); font-size: 13px; text-transform: uppercase; letter-spacing: .08em; }
        .flash { margin-bottom: 18px; padding: 12px 14px; border-radius: 12px; background: var(--success-soft); color: var(--success); }
        .code { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 13px; background: #eff6ff; padding: 3px 6px; border-radius: 8px; }
        .empty { padding: 24px; text-align: center; color: var(--muted); border: 1px dashed var(--line); border-radius: 16px; }
        @media (max-width: 900px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { padding-bottom: 12px; }
            .content { padding: 18px; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">Splitmate Admin</div>
            <div class="sub">Backend control panel for the iOS app</div>
            <nav class="nav">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Overview</a>
                <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">Users</a>
                <a href="{{ route('admin.groups') }}" class="{{ request()->routeIs('admin.groups') || request()->routeIs('admin.groups.show') ? 'active' : '' }}">Groups</a>
                <a href="{{ route('admin.api-docs') }}" class="{{ request()->routeIs('admin.api-docs') ? 'active' : '' }}">API Access</a>
            </nav>
        </aside>
        <main class="content">
            <div class="topbar">
                <div>
                    <h1 class="page-title">{{ $title ?? 'Splitmate Admin' }}</h1>
                    <p class="page-copy">{{ $subtitle ?? 'Monitor and control the mobile platform backend.' }}</p>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button class="logout" type="submit">Sign Out</button>
                </form>
            </div>

            @if(session('status'))
                <div class="flash">{{ session('status') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
