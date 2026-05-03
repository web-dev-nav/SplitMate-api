<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
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
}
