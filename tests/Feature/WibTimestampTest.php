<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use App\Support\LocalTime;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class WibTimestampTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame('Asia/Jakarta', config('app.display_timezone'));
        $this->travelTo(CarbonImmutable::parse('2026-10-01 18:05:00', 'UTC'));
        $this->admin = User::factory()->create();
        foreach (['contact-messages.view', 'career-applications.view'] as $permission) {
            Permission::findOrCreate($permission, 'web');
            $this->admin->givePermissionTo($permission);
        }
    }

    public function test_contact_submission_displays_the_next_calendar_day_in_wib(): void
    {
        $this->post(route('contact.store'), [
            'recaptcha_token' => 'test-token', 'name' => 'Ayu', 'email' => 'ayu@example.com',
            'subject' => 'Kerja sama', 'message' => 'Pesan kontak pada pukul 01:05 WIB.',
        ])->assertRedirect()->assertSessionHas('success');

        $message = ContactMessage::sole();
        $this->assertSame('2026-10-01 18:05:00', $message->getRawOriginal('created_at'));
        $this->actingAs($this->admin)->get(route('admin.contact-messages.index'))
            ->assertOk()->assertSee('02 Oct 2026')->assertSee('01:05 WIB');
        $this->get(route('admin.contact-messages.show', $message))
            ->assertOk()->assertSee('02 October 2026, 01:05 WIB');
    }

    public function test_application_and_both_email_versions_use_wib(): void
    {
        Storage::fake('local');
        config(['recaptcha.site_key' => null, 'careers.mail_to' => 'recruitment@example.com', 'careers.mailer' => 'smtp']);
        $transport = new ArrayTransport;
        Mail::mailer('smtp')->setSymfonyTransport($transport);
        $career = JobPosting::create([
            'title' => ['en' => 'Engineer'], 'slug' => ['en' => 'engineer'],
            'description' => ['en' => 'Requirements'], 'type' => 'full_time', 'is_active' => true,
        ]);
        $this->post(route('careers.apply', $career->getSlug()), [
            'name' => 'Ayu', 'email' => 'ayu@example.com', 'phone' => '08123456789',
            'cv' => UploadedFile::fake()->createWithContent('CV.pdf', "%PDF-1.4\nApplicant CV"),
        ])->assertRedirect()->assertSessionHas('success');

        $application = JobApplication::sole();
        $this->assertSame('2026-10-01 18:05:00', $application->getRawOriginal('created_at'));
        $this->assertSame('2026-10-01 18:05:00', $application->getRawOriginal('email_sent_at'));
        $this->assertSame('sent', $application->email_status);
        $this->assertCount(1, $transport->messages());
        $email = $transport->messages()->first()->getOriginalMessage();
        $this->assertStringContainsString('02 Oct 2026, 01:05 WIB', $email->getHtmlBody());
        $this->assertStringContainsString('02 Oct 2026, 01:05 WIB', $email->getTextBody());
        $this->actingAs($this->admin)->get(route('admin.careers.applications.index', $career))
            ->assertOk()->assertSee('02 Oct 2026')->assertSee('01:05 WIB');
    }

    public function test_historical_utc_timestamps_are_converted_without_changing_the_original(): void
    {
        $message = ContactMessage::create([
            'name' => 'Pelamar lama', 'email' => 'old@example.com', 'subject' => 'Pesan lama', 'message' => 'Riwayat',
        ]);
        $message->forceFill(['created_at' => '2026-09-08 20:30:00'])->save();
        $date = $message->created_at;
        $this->assertSame('09 Sep 2026, 03:30 WIB', LocalTime::format($date));
        $this->assertSame('UTC', $date->getTimezone()->getName());
        $this->actingAs($this->admin)->get(route('admin.contact-messages.show', $message))
            ->assertOk()->assertSee('09 September 2026, 03:30 WIB');
        $this->assertSame('2026-09-08 20:30:00', $message->fresh()->getRawOriginal('created_at'));
    }
}
