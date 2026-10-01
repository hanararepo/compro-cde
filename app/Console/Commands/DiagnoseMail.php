<?php

namespace App\Console\Commands;

use App\Services\Contact\DeliverContactMessage;
use Illuminate\Console\Command;
use Throwable;

class DiagnoseMail extends Command
{
    protected $signature = 'mail:diagnose';

    protected $description = 'Show active mail configuration without sending email or exposing credentials';

    public function handle(): int
    {
        try {
            $contactRecipient = filter_var(DeliverContactMessage::recipient(), FILTER_VALIDATE_EMAIL) !== false;
        } catch (Throwable) {
            $contactRecipient = 'Cannot read recipient settings from the database.';
        }

        $contactMailer = config('contact.mailer', 'smtp');
        $careersMailer = config('careers.mailer', 'smtp');
        $smtpHost = (string) config('mail.mailers.smtp.host');
        $this->line(json_encode([
            'configuration_cached' => app()->configurationIsCached(),
            'default_mailer' => config('mail.default'),
            'contact' => [
                'mailer' => $contactMailer,
                'transport' => config('mail.mailers.'.$contactMailer.'.transport'),
                'recipient_valid' => $contactRecipient,
            ],
            'careers' => [
                'mailer' => $careersMailer,
                'transport' => config('mail.mailers.'.$careersMailer.'.transport'),
                'recipient_valid' => filter_var(config('careers.mail_to'), FILTER_VALIDATE_EMAIL) !== false,
            ],
            'smtp' => [
                'host' => preg_match('/^[a-zA-Z0-9_.:\[\]-]+$/', $smtpHost) ? $smtpHost : '[empty or invalid host]',
                'port' => (int) config('mail.mailers.smtp.port'),
                'scheme' => config('mail.mailers.smtp.scheme'),
                'username_configured' => filled(config('mail.mailers.smtp.username')),
                'password_configured' => filled(config('mail.mailers.smtp.password')),
                'url_override_configured' => filled(config('mail.mailers.smtp.url')),
                'from_address_valid' => filter_var(config('mail.from.address'), FILTER_VALIDATE_EMAIL) !== false,
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
