<?php

namespace Tests\Unit;

use App\Support\MailDeliveryError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;

class MailDeliveryErrorTest extends TestCase
{
    public static function failures(): array
    {
        return [
            ['Failed to authenticate secret-password', 535, 'SMTP authentication failed'],
            ['Unable to connect with STARTTLS: secret-password', 0, 'SMTP TLS connection failed'],
            ['Connection timed out secret-password', 0, 'SMTP connection timed out'],
            ['Connection could not be established with host secret-password', 0, 'SMTP server could not be reached'],
            ['Recipient secret-password rejected', 550, 'SMTP server rejected the message (code 550)'],
            ['secret-password', 0, 'SMTP transport failed'],
        ];
    }

    #[DataProvider('failures')]
    public function test_failure_reason_is_useful_without_exposing_raw_diagnostics(string $detail, int $code, string $expected): void
    {
        $reason = MailDeliveryError::describe(new TransportException($detail, $code));
        $this->assertStringContainsString($expected, $reason);
        $this->assertStringNotContainsString('secret-password', $reason);
    }
}
