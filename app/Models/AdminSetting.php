<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class AdminSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key, with an optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set (upsert) a setting value.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Return all SMTP-related settings as an associative array.
     */
    public static function smtpSettings(): array
    {
        $keys = ['mail_mailer', 'mail_host', 'mail_port', 'mail_username',
                 'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name'];

        $settings = static::whereIn('key', $keys)->pluck('value', 'key');

        return [
            'mail_mailer'       => $settings['mail_mailer']       ?? config('mail.default', 'smtp'),
            'mail_host'         => $settings['mail_host']         ?? config('mail.mailers.smtp.host', ''),
            'mail_port'         => $settings['mail_port']         ?? config('mail.mailers.smtp.port', '587'),
            'mail_username'     => $settings['mail_username']     ?? config('mail.mailers.smtp.username', ''),
            'mail_password'     => $settings['mail_password']     ?? '',
            'mail_encryption'   => $settings['mail_encryption']   ?? config('mail.mailers.smtp.encryption', 'tls'),
            'mail_from_address' => $settings['mail_from_address'] ?? config('mail.from.address', ''),
            'mail_from_name'    => $settings['mail_from_name']    ?? config('mail.from.name', 'SplitMate'),
        ];
    }

    /**
     * Apply saved SMTP settings to current runtime.
     */
    public static function applySmtpSettingsToRuntime(): void
    {
        $smtp = static::smtpSettings();

        if (empty($smtp['mail_host'])) {
            return;
        }

        Config::set('mail.default', $smtp['mail_mailer'] ?? 'smtp');
        Config::set('mail.mailers.smtp.host', $smtp['mail_host'] ?? '');
        Config::set('mail.mailers.smtp.port', (int) ($smtp['mail_port'] ?? 587));
        Config::set('mail.mailers.smtp.username', $smtp['mail_username'] ?? '');
        Config::set('mail.mailers.smtp.password', $smtp['mail_password'] ?? '');
        Config::set('mail.mailers.smtp.encryption', ($smtp['mail_encryption'] ?? '') ?: null);
        Config::set('mail.from.address', $smtp['mail_from_address'] ?? '');
        Config::set('mail.from.name', $smtp['mail_from_name'] ?? 'SplitMate');
    }
}
