<?php

namespace App\Services\Contact;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Setting;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DeliverContactMessage
{
    public static function recipient(): ?string
    {
        return Setting::get('contact_notification_email', Setting::get('contact_email'));
    }

    public function send(int $id): bool
    {
        return (bool) Cache::lock('contact-message:'.$id, 300)->get(function () use ($id) {
            $message = ContactMessage::find($id);
            if (! $message || $message->email_status === 'legacy') {
                return false;
            }
            if ($message->email_status === 'sent') {
                return true;
            }

            try {
                $recipient = self::recipient();
                if (! is_string($recipient) || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                    throw new DomainException('Set a valid notification email in Contact Messages settings.');
                }
                $mailer = config('mail.default');
                if (! in_array(config('mail.mailers.'.$mailer.'.transport'), ['smtp', 'sendmail', 'ses', 'ses-v2', 'postmark', 'resend', 'mailgun'], true)) {
                    throw new DomainException('Configure a live mail service to deliver contact notifications.');
                }
            } catch (DomainException $exception) {
                $message->update(['email_status' => 'failed', 'email_last_error' => $exception->getMessage()]);

                return false;
            }

            $message->update(['email_status' => 'pending', 'email_recipient' => $recipient, 'email_last_error' => null]);
            for ($attempt = 1; $attempt <= 3; $attempt++) {
                $message->increment('email_attempts');
                try {
                    if (! Mail::mailer($mailer)->to($recipient)->send(new ContactMessageReceived($message))) {
                        throw new DomainException('The mail service did not confirm delivery.');
                    }
                } catch (Throwable $exception) {
                    Log::warning('Contact notification failed.', ['message_id' => $id, 'attempt' => $attempt, 'exception_type' => $exception::class]);
                    if ($exception instanceof DomainException || $attempt === 3) {
                        $message->update(['email_status' => 'failed', 'email_last_error' => 'Email delivery failed. The message remains available in Contact Messages.']);

                        return false;
                    }

                    continue;
                }

                // A confirmed email must not be resent if saving its status fails.
                $message->update(['email_status' => 'sent', 'email_sent_at' => now(), 'email_last_error' => null]);

                return true;
            }

            return false;
        });
    }
}
