@extends('admin.layout', [
    'title' => 'System Logs',
    'subtitle' => 'Review backend warnings and errors from Laravel log file.',
])

@php
    $badgeClass = [
        'emergency' => 'danger',
        'alert' => 'danger',
        'critical' => 'danger',
        'error' => 'danger',
        'warning' => 'warn',
        'notice' => 'primary',
        'info' => 'primary',
        'debug' => 'success',
    ];
@endphp

@section('content')
    <div class="grid" style="gap:24px;">
        <div class="panel">
            <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap;">
                <div class="stack" style="gap:6px;">
                    <h2 style="margin:0;">Laravel Log Viewer</h2>
                    <p class="muted" style="margin:0;font-size:14px;">
                        Source: <span class="code">{{ $logFilePath }}</span>
                    </p>
                </div>
                <div class="actions">
                    <a href="{{ route('admin.logs', request()->query()) }}" class="button">Refresh</a>
                    <form method="POST" action="{{ route('admin.logs.clear', request()->query()) }}" onsubmit="return confirm('Clear laravel.log file? This cannot be undone.');">
                        @csrf
                        <button type="submit" class="button danger">Clear Log File</button>
                    </form>
                    <a href="{{ route('admin.settings') }}" class="button">Back To Settings</a>
                </div>
            </div>
        </div>

        <div class="panel">
            <form method="GET" action="{{ route('admin.logs') }}" class="grid" style="grid-template-columns:2fr 1fr 1fr auto;gap:14px;align-items:end;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Search text</label>
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="expense notification, smtp, exception..."
                           style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:12px;font-size:14px;">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Level</label>
                    <select name="level" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:12px;background:#fff;font-size:14px;">
                        @foreach(['all', 'error', 'warning', 'info', 'debug', 'critical', 'alert', 'emergency', 'notice'] as $option)
                            <option value="{{ $option }}" {{ $level === $option ? 'selected' : '' }}>{{ strtoupper($option) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Rows per page</label>
                    <select name="per_page" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:12px;background:#fff;font-size:14px;">
                        @foreach([25, 50, 100, 150, 250] as $option)
                            <option value="{{ $option }}" {{ $perPage === $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="button primary">Apply</button>
            </form>
        </div>

        <div class="panel">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:12px;">
                <h2 style="margin:0;">Visible Entries</h2>
                <div class="muted" style="font-size:13px;display:flex;gap:10px;flex-wrap:wrap;">
                    <span>Total: {{ $totalMatches }}</span>
                    <span>Error: {{ $counts['error'] ?? 0 }}</span>
                    <span>Warning: {{ $counts['warning'] ?? 0 }}</span>
                    <span>Info: {{ $counts['info'] ?? 0 }}</span>
                    <span>Debug: {{ $counts['debug'] ?? 0 }}</span>
                </div>
            </div>

            @if(!$logFileExists)
                <div class="empty">Log file not found yet. Generate activity first, then refresh.</div>
            @elseif($entries->isEmpty())
                <div class="empty">No log entries match current filters.</div>
            @else
                <div style="overflow:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width:180px;">Time</th>
                                <th style="width:110px;">Level</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entries as $entry)
                                <tr>
                                    <td><span class="code">{{ $entry['timestamp'] }}</span></td>
                                    <td><span class="badge {{ $badgeClass[$entry['level']] ?? 'primary' }}">{{ strtoupper($entry['level']) }}</span></td>
                                    <td>
                                        <div style="font-weight:600;line-height:1.5;white-space:pre-wrap;">{{ $entry['message'] }}</div>

                                        @if($entry['context'] !== '')
                                            <details style="margin-top:10px;">
                                                <summary style="cursor:pointer;color:var(--primary);font-weight:600;">Context</summary>
                                                <pre style="margin:10px 0 0;padding:12px;border-radius:12px;background:#0f172a;color:#e2e8f0;overflow:auto;font-size:12px;white-space:pre-wrap;">{{ $entry['context'] }}</pre>
                                            </details>
                                        @endif

                                        @if($entry['details'] !== '')
                                            <details style="margin-top:10px;">
                                                <summary style="cursor:pointer;color:var(--primary);font-weight:600;">Stack / Details</summary>
                                                <pre style="margin:10px 0 0;padding:12px;border-radius:12px;background:#0f172a;color:#e2e8f0;overflow:auto;font-size:12px;white-space:pre-wrap;">{{ $entry['details'] }}</pre>
                                            </details>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;margin-top:16px;">
                    <div class="muted" style="font-size:13px;">
                        Showing {{ $entries->firstItem() ?? 0 }}-{{ $entries->lastItem() ?? 0 }} of {{ $entries->total() }}
                    </div>
                    <div class="actions">
                        @if($entries->onFirstPage())
                            <span class="button" style="opacity:.5;cursor:not-allowed;">Previous</span>
                        @else
                            <a class="button" href="{{ $entries->previousPageUrl() }}">Previous</a>
                        @endif

                        <span class="code">Page {{ $entries->currentPage() }} / {{ $entries->lastPage() }}</span>

                        @if($entries->hasMorePages())
                            <a class="button" href="{{ $entries->nextPageUrl() }}">Next</a>
                        @else
                            <span class="button" style="opacity:.5;cursor:not-allowed;">Next</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
