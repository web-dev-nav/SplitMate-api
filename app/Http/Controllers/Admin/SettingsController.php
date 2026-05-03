<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $smtp = AdminSetting::smtpSettings();

        return view('admin.settings', [
            'title'    => 'Settings',
            'subtitle' => 'Configure SMTP and other server-level settings.',
            'smtp'     => $smtp,
        ]);
    }

    public function logs(Request $request): View
    {
        $level = strtolower((string) $request->query('level', 'all'));
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 50);
        $perPage = max(25, min($perPage, 250));
        $page = max(1, (int) $request->query('page', 1));
        $allowedLevels = ['all', 'emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];
        $allEntries = collect($this->readLogEntries());

        if (!in_array($level, $allowedLevels, true)) {
            $level = 'all';
        }

        $entries = $allEntries
            ->when($level !== 'all', fn ($items) => $items->where('level', $level))
            ->when($search !== '', function ($items) use ($search) {
                $needle = mb_strtolower($search);

                return $items->filter(function (array $entry) use ($needle) {
                    return str_contains(mb_strtolower($entry['message']), $needle)
                        || str_contains(mb_strtolower($entry['context']), $needle)
                        || str_contains(mb_strtolower($entry['details']), $needle);
                });
            })
            ->values();

        $counts = $allEntries
            ->countBy('level')
            ->all();

        $paginator = new LengthAwarePaginator(
            $entries->forPage($page, $perPage)->values(),
            $entries->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.logs', [
            'title' => 'System Logs',
            'subtitle' => 'Review backend warnings and errors from Laravel log file.',
            'entries' => $paginator,
            'totalMatches' => $entries->count(),
            'level' => $level,
            'search' => $search,
            'perPage' => $perPage,
            'counts' => $counts,
            'logFilePath' => storage_path('logs/laravel.log'),
            'logFileExists' => is_file(storage_path('logs/laravel.log')),
        ]);
    }

    public function updateSmtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mail_mailer'       => 'required|string|in:smtp,sendmail,mailgun,ses,log,array',
            'mail_host'         => 'required|string|max:255',
            'mail_port'         => 'required|integer|min:1|max:65535',
            'mail_username'     => 'nullable|string|max:255',
            'mail_password'     => 'nullable|string|max:255',
            'mail_encryption'   => 'nullable|string|in:tls,ssl,starttls,',
            'mail_from_address' => 'required|email',
            'mail_from_name'    => 'required|string|max:255',
        ]);

        // If password field is blank, keep existing stored password
        if (empty($validated['mail_password'])) {
            $validated['mail_password'] = AdminSetting::get('mail_password', '');
        }

        foreach ($validated as $key => $value) {
            AdminSetting::set($key, $value ?? '');
        }

        // Apply to current runtime so the test below works immediately
        AdminSetting::applySmtpSettingsToRuntime();

        return redirect()->route('admin.settings')->with('status', 'SMTP settings saved successfully.');
    }

    public function testSmtp(Request $request): RedirectResponse
    {
        $smtp = AdminSetting::smtpSettings();
        AdminSetting::applySmtpSettingsToRuntime();

        $to = $request->input('test_email', $smtp['mail_from_address']);

        try {
            Mail::raw('This is a test email from SplitMate Admin. If you received this, your SMTP configuration is working correctly.', function ($message) use ($smtp, $to) {
                $message->to($to)
                    ->subject('SplitMate — SMTP Test')
                    ->from($smtp['mail_from_address'], $smtp['mail_from_name']);
            });

            return redirect()->route('admin.settings')->with('status', "Test email sent to {$to}. Check your inbox.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.settings')->with('error', 'SMTP test failed: ' . $e->getMessage());
        }
    }

    /**
     * Read recent Laravel log entries from end of file.
     *
     * @return array<int, array<string, string>>
     */
    private function readLogEntries(): array
    {
        $path = storage_path('logs/laravel.log');

        if (!is_file($path) || !is_readable($path)) {
            return [];
        }

        $raw = $this->readTail($path, 1024 * 1024);
        $lines = preg_split("/\r\n|\n|\r/", $raw) ?: [];
        $entries = [];

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            if (preg_match('/^\[(?<time>[^\]]+)\]\s+\S+\.(?<level>[A-Z]+):\s(?<message>.*)$/', $line, $matches)) {
                $entries[] = [
                    'timestamp' => trim($matches['time']),
                    'level' => strtolower(trim($matches['level'])),
                    'message' => trim($matches['message']),
                    'context' => '',
                    'details' => '',
                ];
                continue;
            }

            if (!empty($entries)) {
                $index = array_key_last($entries);
                $entries[$index]['details'] .= ($entries[$index]['details'] === '' ? '' : "\n") . $line;
            }
        }

        foreach ($entries as &$entry) {
            if (preg_match('/^(?<message>.*?)(?<context>\s\{.*\})$/', $entry['message'], $matches)) {
                $entry['message'] = trim($matches['message']);
                $entry['context'] = trim($matches['context']);
            }
        }
        unset($entry);

        return array_reverse($entries);
    }

    private function readTail(string $path, int $bytes): string
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return '';
        }

        try {
            $size = filesize($path) ?: 0;
            $offset = max(0, $size - $bytes);
            fseek($handle, $offset);
            $content = stream_get_contents($handle) ?: '';

            if ($offset > 0) {
                $firstNewline = strpos($content, "\n");
                if ($firstNewline !== false) {
                    $content = substr($content, $firstNewline + 1);
                }
            }

            return $content;
        } finally {
            fclose($handle);
        }
    }
}
