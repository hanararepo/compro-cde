<?php

namespace App\Services\Careers;

use App\Mail\JobApplicationReceived;
use App\Models\JobApplication;
use App\Support\MailDeliveryError;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class DeliverJobApplication
{
    public function send(int $id): bool
    {
        // Hold the same application lock across every attempt and CV cleanup.
        return (bool) Cache::lock('career-application:'.$id, 300)->get(function () use ($id) {
            $application = JobApplication::find($id);
            if (! $application || $application->email_status === 'legacy') {
                return false;
            }

            if ($application->email_status === 'sent') {
                $this->cleanup($application);

                return true;
            }

            $maxAttempts = max(1, min(3, (int) config('careers.max_attempts', 3)));
            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                $application->update([
                    'email_status' => 'pending',
                    'email_attempts' => $application->email_attempts + 1,
                    'email_next_attempt_at' => null,
                ]);

                try {
                    $recipient = config('careers.mail_to');
                    if (! is_string($recipient) || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                        throw new DomainException('CAREERS_MAIL_TO belum diisi dengan alamat email yang valid.');
                    }
                    $application->update(['email_recipient' => $recipient]);

                    $mailer = config('careers.mailer');
                    $transport = config('mail.mailers.'.$mailer.'.transport');
                    // Simulated transports must never trigger deletion or write CVs to logs.
                    if (! in_array($transport, ['smtp', 'sendmail', 'ses', 'ses-v2', 'postmark', 'resend', 'mailgun'], true)) {
                        throw new DomainException('CAREERS_MAILER harus menggunakan layanan email pengiriman nyata.');
                    }

                    if (! $application->cv_path || ! Storage::disk('local')->exists($application->cv_path)) {
                        throw new DomainException('CV sementara tidak ditemukan.');
                    }

                    $sent = Mail::mailer($mailer)->to($recipient)->send(new JobApplicationReceived($application));
                    if (! $sent) {
                        throw new DomainException('Layanan email tidak mengonfirmasi pengiriman.');
                    }
                } catch (Throwable $exception) {
                    // Do not persist SMTP diagnostics containing credentials or applicant data.
                    $reason = $exception instanceof DomainException ? $exception->getMessage() : MailDeliveryError::describe($exception);
                    $application->update([
                        'email_status' => 'failed',
                        'email_last_error' => $exception instanceof DomainException
                            ? $reason
                            : $reason.' The application and CV are retained; no scheduled retry is pending.',
                    ]);
                    Log::warning('Career application email failed.', ['application_id' => $id, 'attempt' => $attempt, 'mailer' => config('careers.mailer'), 'exception_type' => $exception::class, 'reason' => $reason]);

                    // Invalid configuration or missing files cannot recover in this request.
                    if ($exception instanceof DomainException || $attempt === $maxAttempts) {
                        return false;
                    }

                    continue;
                }

                // Keep persistence/cleanup outside the send retry: never resend a confirmed email.
                $application->update([
                    'email_status' => 'sent',
                    'email_recipient' => $recipient,
                    'email_sent_at' => now(),
                    'email_last_error' => null,
                    'email_next_attempt_at' => null,
                ]);
                $this->cleanup($application);

                return true;
            }

            return false;
        });
    }

    private function cleanup(JobApplication $application): void
    {
        if (! $application->cv_path) {
            return;
        }

        // Retry deletion immediately, independently from sending the email.
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $disk = Storage::disk('local');
                $path = $application->cv_path;
                if ($disk->exists($path) && ! $disk->delete($path)) {
                    throw new RuntimeException('CV deletion failed.');
                }
                if ($disk->exists($path)) {
                    throw new RuntimeException('CV still exists after deletion.');
                }
                $application->update(['cv_path' => null, 'email_last_error' => null]);

                return;
            } catch (Throwable $exception) {
                Log::warning('Career CV cleanup failed.', ['application_id' => $application->id, 'attempt' => $attempt, 'exception_type' => $exception::class]);
            }
        }

        $application->update(['email_last_error' => 'Email terkirim, tetapi CV gagal dihapus setelah 3 percobaan. File dan path tetap tersimpan; tidak ada retry terjadwal.']);
    }
}
