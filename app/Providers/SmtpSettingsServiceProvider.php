<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class SmtpSettingsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap SMTP settings from the database, overriding .env values.
     * Wrapped in a try/catch so it never breaks the app if the DB table
     * doesn't exist yet (e.g., before the migration runs).
     */
    public function boot(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('admin_settings')) {
                $settings = \App\Models\AdminSetting::smtpSettings();

                if (!empty($settings['mail_host'])) {
                    Config::set('mail.default', $settings['mail_mailer']);
                    Config::set('mail.mailers.smtp.host', $settings['mail_host']);
                    Config::set('mail.mailers.smtp.port', (int) $settings['mail_port']);
                    Config::set('mail.mailers.smtp.username', $settings['mail_username']);
                    Config::set('mail.mailers.smtp.password', $settings['mail_password']);
                    Config::set('mail.mailers.smtp.encryption', $settings['mail_encryption'] ?: null);
                    Config::set('mail.from.address', $settings['mail_from_address']);
                    Config::set('mail.from.name', $settings['mail_from_name']);
                }
            }
        } catch (\Throwable) {
            // Silently fall back to .env values
        }
    }
}
