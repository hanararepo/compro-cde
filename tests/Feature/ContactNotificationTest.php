<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Setting;
use App\Models\User;
use App\Services\Contact\DeliverContactMessage;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Spatie\Permission\Models\Permission;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Tests\TestCase;

class ContactNotificationTest extends TestCase
{
    use RefreshDatabase;

    private ArrayTransport $transport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Setting::flushCache();
        Setting::set('contact_email', 'public@example.com');
        Setting::set('contact_notification_email', 'inbox@example.com');
        config(['mail.default' => 'smtp', 'contact.mailer' => 'smtp']);
        $this->transport = new ArrayTransport;
        Mail::mailer('smtp')->setSymfonyTransport($this->transport);
    }

    private function payload(): array
    {
        return ['recaptcha_token' => 'test-token', 'name' => 'Ayu', 'email' => 'ayu@example.com',
            'subject' => 'Partnership', 'message' => "First line\n<script>alert(1)</script>"];
    }

    public function test_message_is_saved_and_emailed_with_reply_to_and_wib_timestamp(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-01 18:05:00', 'UTC'));
        $this->post(route('contact.store'), $this->payload())->assertRedirect(route('contact'))->assertSessionHas('success');
        $message = ContactMessage::sole();
        $this->assertSame($this->payload()['message'], $message->message);
        $this->assertSame('sent', $message->email_status);
        $this->assertSame(1, $message->email_attempts);
        $this->assertSame('inbox@example.com', $message->email_recipient);
        $this->assertNotNull($message->email_sent_at);
        $email = $this->transport->messages()->first()->getOriginalMessage();
        $this->assertSame('inbox@example.com', $email->getTo()[0]->getAddress());
        $this->assertSame('ayu@example.com', $email->getReplyTo()[0]->getAddress());
        $this->assertSame(config('app.name').' - Contact: Partnership', $email->getSubject());
        $this->assertStringContainsString('02 Oct 2026, 01:05 WIB', $email->getHtmlBody());
        $this->assertStringContainsString('&lt;script&gt;', $email->getHtmlBody());
        $this->assertStringNotContainsString('<script>', $email->getHtmlBody());
        $this->assertStringContainsString('First line', $email->getTextBody());
        $this->assertTrue(app(DeliverContactMessage::class)->send($message->id));
        $this->assertCount(1, $this->transport->messages());
    }

    public function test_contact_uses_smtp_even_when_the_default_mailer_is_log(): void
    {
        config(['mail.default' => 'log']);

        $this->post(route('contact.store'), $this->payload())->assertSessionHas('success');

        $message = ContactMessage::sole();
        $this->assertSame('sent', $message->email_status);
        $this->assertSame(1, $message->email_attempts);
        $this->assertCount(1, $this->transport->messages());
    }

    public function test_delivery_failure_retries_three_times_and_keeps_the_admin_message(): void
    {
        $transport = Mockery::mock(TransportInterface::class);
        $transport->shouldReceive('send')->times(3)->andThrow(new TransportException('Failed to authenticate secret SMTP diagnostic', 535));
        Mail::mailer('smtp')->setSymfonyTransport($transport);
        $this->post(route('contact.store'), $this->payload())->assertRedirect()->assertSessionHas('success');
        $message = ContactMessage::sole();
        $this->assertSame('failed', $message->email_status);
        $this->assertSame(3, $message->email_attempts);
        $this->assertSame($this->payload()['message'], $message->message);
        $this->assertStringNotContainsString('secret', $message->email_last_error);
        $this->assertStringContainsString('SMTP authentication failed', $message->email_last_error);
        $this->assertSame('inbox@example.com', $message->email_recipient);
    }

    public function test_mail_diagnostics_do_not_send_email_or_expose_credentials(): void
    {
        config(['mail.default' => 'log', 'careers.mailer' => 'smtp',
            'mail.mailers.smtp.username' => 'private-user', 'mail.mailers.smtp.password' => 'private-password',
            'mail.mailers.smtp.url' => 'smtp://private-user:private-password@example.com:587']);
        Artisan::call('mail:diagnose');
        $output = Artisan::output();
        $diagnostic = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('smtp', $diagnostic['contact']['mailer']);
        $this->assertSame('smtp', $diagnostic['careers']['transport']);
        $this->assertTrue($diagnostic['contact']['recipient_valid']);
        $this->assertTrue($diagnostic['smtp']['password_configured']);
        $this->assertTrue($diagnostic['smtp']['url_override_configured']);
        $this->assertStringNotContainsString('private-', $output);
        $this->assertCount(0, $this->transport->messages());
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_a_transient_failure_succeeds_on_the_next_immediate_attempt(): void
    {
        $transport = Mockery::mock(TransportInterface::class);
        $attempts = 0;
        $transport->shouldReceive('send')->twice()->andReturnUsing(function ($mail, $envelope = null) use (&$attempts) {
            if (++$attempts === 1) {
                throw new TransportException('Temporary failure');
            }

            return $this->transport->send($mail, $envelope);
        });
        Mail::mailer('smtp')->setSymfonyTransport($transport);
        $this->post(route('contact.store'), $this->payload())->assertSessionHas('success');
        $message = ContactMessage::sole();
        $this->assertSame('sent', $message->email_status);
        $this->assertSame(2, $message->email_attempts);
        $this->assertCount(1, $this->transport->messages());
    }

    public function test_invalid_configuration_keeps_message_without_claiming_delivery(): void
    {
        Setting::set('contact_notification_email', '');
        $this->post(route('contact.store'), $this->payload())->assertSessionHas('success');
        $this->assertSame('failed', ContactMessage::sole()->email_status);
        $this->assertSame(0, ContactMessage::sole()->email_attempts);
        $this->assertCount(0, $this->transport->messages());
    }

    public function test_recipient_settings_require_permission_and_a_valid_email(): void
    {
        foreach (['contact-messages.view', 'settings.edit'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $admin = User::factory()->create();
        $admin->givePermissionTo('contact-messages.view');
        $url = route('admin.contact-messages.email-settings');
        $this->actingAs($admin)->put($url, ['notification_email' => 'new@example.com'])->assertForbidden();
        $admin->givePermissionTo('settings.edit');
        $this->put($url, ['notification_email' => 'invalid'])->assertSessionHasErrors('notification_email');
        $this->assertSame('inbox@example.com', DeliverContactMessage::recipient());
        $this->put($url, ['notification_email' => ' new@example.com '])->assertRedirect(route('admin.contact-messages.index'));
        $this->assertSame('new@example.com', DeliverContactMessage::recipient());
        $this->get(route('admin.contact-messages.index'))->assertOk()->assertSee('new@example.com')->assertSee('Email notifications');
        $this->post(route('contact.store'), $this->payload())->assertSessionHas('success');
        $this->assertSame('new@example.com', $this->transport->messages()->first()->getOriginalMessage()->getTo()[0]->getAddress());
    }

    public function test_old_messages_are_not_emailed_and_public_contact_email_is_the_initial_default(): void
    {
        Setting::where('key', 'contact_notification_email')->delete();
        Setting::flushCache();
        $this->assertSame('public@example.com', DeliverContactMessage::recipient());
        $old = ContactMessage::create(collect($this->payload())->except('recaptcha_token')->all());
        $this->assertFalse(app(DeliverContactMessage::class)->send($old->id));
        $this->assertCount(0, $this->transport->messages());
        $this->assertSame('legacy', $old->fresh()->email_status);
    }
}
