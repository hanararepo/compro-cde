<?php

namespace App\Support;

use Illuminate\View\ViewException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

class MailDeliveryError
{
    public static function describe(Throwable $exception): string
    {
        if ($exception instanceof ViewException) {
            return 'Email template could not be rendered. Check the deployed mail views and server log.';
        }

        if (! $exception instanceof TransportExceptionInterface) {
            return 'Email preparation or delivery failed. Check the exception type in the server log.';
        }

        // Classify locally; never persist the raw SMTP response, username or password.
        $detail = strtolower($exception->getMessage());
        $code = (int) $exception->getCode();
        if (in_array($code, [534, 535], true) || str_contains($detail, 'failed to authenticate')) {
            return 'SMTP authentication failed. Check MAIL_USERNAME, MAIL_PASSWORD and the SMTP account permissions.';
        }
        if (str_contains($detail, 'starttls') || str_contains($detail, 'certificate') || str_contains($detail, 'ssl') || str_contains($detail, 'tls required')) {
            return 'SMTP TLS connection failed. Check MAIL_SCHEME, MAIL_PORT and the server certificate.';
        }
        if (str_contains($detail, 'timed out') || str_contains($detail, 'timeout')) {
            return 'SMTP connection timed out. Check the SMTP host, port and outbound network access.';
        }
        if (str_contains($detail, 'connection could not be established') || str_contains($detail, 'connection refused') || str_contains($detail, 'getaddrinfo') || str_contains($detail, 'php_network_getaddresses')) {
            return 'SMTP server could not be reached. Check MAIL_HOST, MAIL_PORT, DNS and outbound network access.';
        }

        return $code >= 400 && $code <= 599
            ? 'SMTP server rejected the message (code '.$code.'). Check sender, recipient and SMTP provider restrictions.'
            : 'SMTP transport failed. Check the SMTP service and the exception type in the server log.';
    }
}
