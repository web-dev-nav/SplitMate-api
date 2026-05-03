<?php

namespace App\Providers;

use App\Models\AdminSetting;
use Illuminate\Support\ServiceProvider;

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
                AdminSetting::applySmtpSettingsToRuntime();
            }
        } catch (\Throwable) {
            // Silently fall back to .env values
        }
    }
}
