<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class JobApplicationReceived extends Mailable
{
    public function __construct(public JobApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->application->email, $this->application->name)],
            subject: config('app.name').' - '.$this->position(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.job-application-received',
            text: 'mail.job-application-received-text',
            with: [
                'companyName' => config('app.name'),
                'position' => $this->position(),
            ],
        );
    }

    private function position(): string
    {
        return $this->application->jobPosting->getTranslation('title', 'id', false)
            ?: $this->application->jobPosting->getTranslation('title', 'en', false);
    }

    public function attachments(): array
    {
        return [Attachment::fromStorageDisk('local', $this->application->cv_path)
            ->as($this->application->cv_original_name)];
    }
}
