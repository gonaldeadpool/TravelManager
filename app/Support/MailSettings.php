<?php

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;
use Throwable;

class MailSettings
{
    private const KEYS = [
        'smtp_host' => 'mail.smtp.host',
        'smtp_port' => 'mail.smtp.port',
        'smtp_scheme' => 'mail.smtp.scheme',
        'smtp_username' => 'mail.smtp.username',
        'smtp_password' => 'mail.smtp.password',
        'from_address' => 'mail.from.address',
        'from_name' => 'mail.from.name',
    ];

    public function forForm(): array
    {
        $settings = $this->settings();

        return [
            'smtp_host' => $settings['mail.smtp.host'] ?? '',
            'smtp_port' => $settings['mail.smtp.port'] ?? '587',
            'smtp_scheme' => $settings['mail.smtp.scheme'] ?? 'smtp',
            'smtp_username' => $settings['mail.smtp.username'] ?? '',
            'smtp_password_configured' => filled($settings['mail.smtp.password'] ?? null),
            'from_address' => $settings['mail.from.address'] ?? '',
            'from_name' => $settings['mail.from.name'] ?? config('app.name'),
        ];
    }

    public function save(array $values): void
    {
        foreach (self::KEYS as $field => $key) {
            if ($field === 'smtp_password') {
                if ($values['remove_smtp_password'] ?? false) {
                    AppSetting::updateOrCreate(['key' => $key], ['value' => null]);
                } elseif (filled($values['smtp_password'] ?? null)) {
                    AppSetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => Crypt::encryptString($values['smtp_password'])]
                    );
                }

                continue;
            }

            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => trim((string) $values[$field])]
            );
        }
    }

    public function configure(): void
    {
        $settings = $this->settings();

        if (blank($settings['mail.smtp.host'] ?? null) || blank($settings['mail.from.address'] ?? null)) {
            throw new RuntimeException('La configurazione SMTP non è completa.');
        }

        $password = null;
        if (filled($settings['mail.smtp.password'] ?? null)) {
            try {
                $password = Crypt::decryptString($settings['mail.smtp.password']);
            } catch (Throwable $exception) {
                throw new RuntimeException('La password SMTP salvata non è leggibile. Inseriscila di nuovo.', previous: $exception);
            }
        }

        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'scheme' => $settings['mail.smtp.scheme'] ?? 'smtp',
            'host' => $settings['mail.smtp.host'],
            'port' => (int) ($settings['mail.smtp.port'] ?? 587),
            'username' => $settings['mail.smtp.username'] ?: null,
            'password' => $password,
            'timeout' => null,
            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
        ]);
        Config::set('mail.from.address', $settings['mail.from.address']);
        Config::set('mail.from.name', $settings['mail.from.name'] ?? config('app.name'));
        app('mail.manager')->purge('smtp');
    }

    private function settings(): array
    {
        return AppSetting::query()
            ->whereIn('key', array_values(self::KEYS))
            ->pluck('value', 'key')
            ->all();
    }
}
