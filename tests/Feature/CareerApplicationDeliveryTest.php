<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use App\Services\Careers\DeliverJobApplication;
use Fiber;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Spatie\Permission\Models\Permission;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Tests\TestCase;

class CareerApplicationDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private JobPosting $career;

    private ArrayTransport $transport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['recaptcha.site_key' => null, 'careers.mail_to' => 'recruitment@example.com', 'careers.mailer' => 'smtp']);
        Storage::fake('local');
        $this->transport = new ArrayTransport;
        // Exercise real message rendering and attachments without making network requests.
        Mail::mailer('smtp')->setSymfonyTransport($this->transport);
        $this->career = JobPosting::create([
            'title' => ['en' => 'Engineer', 'id' => 'Insinyur'],
            'slug' => ['en' => 'engineer', 'id' => 'insinyur'],
            'description' => ['en' => 'Job description', 'id' => 'Deskripsi pekerjaan'],
            'type' => 'full_time',
            'is_active' => true,
        ]);
    }

    private function payload(): array
    {
        return [
            'name' => 'Ayu Pelamar',
            'email' => 'ayu@example.com',
            'phone' => '081234567890',
            'cv' => UploadedFile::fake()->createWithContent('Ayu CV.pdf', "%PDF-1.4\nApplicant CV contents"),
        ];
    }

    private function pendingApplication(array $attributes = []): JobApplication
    {
        Storage::disk('local')->put('cv-temporary/test.pdf', '%PDF-1.4 CV contents');

        return JobApplication::create(array_merge([
            'job_posting_id' => $this->career->id,
            'name' => 'Ayu Pelamar',
            'email' => 'ayu@example.com',
            'phone' => '081234567890',
            'cv_path' => 'cv-temporary/test.pdf',
            'cv_original_name' => 'Ayu CV.pdf',
            'email_status' => 'pending',
        ], $attributes));
    }

    private function failTransport(): void
    {
        $transport = Mockery::mock(TransportInterface::class);
        $transport->shouldReceive('send')->andThrow(new TransportException('Failed to authenticate SMTP secret diagnostic', 535));
        Mail::mailer('smtp')->setSymfonyTransport($transport);
    }

    public function test_submission_sends_complete_email_and_removes_only_cv(): void
    {
        config(['app.name' => 'PT Cakrawala Dinamika Energi']);
        $this->post(route('careers.apply', $this->career->getSlug()), $this->payload())
            ->assertSessionHasNoErrors()->assertSessionHas('success');

        $application = JobApplication::sole();
        $this->assertSame('sent', $application->email_status);
        $this->assertSame('Ayu Pelamar', $application->name);
        $this->assertNull($application->linkedin);
        $this->assertSame('Ayu CV.pdf', $application->cv_original_name);
        $this->assertSame('recruitment@example.com', $application->email_recipient);
        $this->assertNotNull($application->email_sent_at);
        $this->assertNull($application->cv_path);
        $this->assertSame([], Storage::disk('local')->allFiles());
        $message = $this->transport->messages()->sole()->getOriginalMessage();
        $this->assertSame('recruitment@example.com', $message->getTo()[0]->getAddress());
        $this->assertSame('ayu@example.com', $message->getReplyTo()[0]->getAddress());
        $this->assertSame('PT Cakrawala Dinamika Energi - Insinyur', $message->getSubject());
        $this->assertStringContainsString('Lamaran PT Cakrawala Dinamika Energi', $message->getHtmlBody());
        $this->assertStringContainsString('Lamaran PT Cakrawala Dinamika Energi', $message->getTextBody());
        $this->assertStringNotContainsString('LinkedIn', $message->getHtmlBody());
        $this->assertStringContainsString('081234567890', $message->getHtmlBody());
        $this->assertCount(1, $message->getAttachments());
        $this->assertSame('Ayu CV.pdf', $message->getAttachments()[0]->getFilename());
        $this->assertStringContainsString('Applicant CV contents', $message->getAttachments()[0]->getBody());
        $this->assertTrue(app(DeliverJobApplication::class)->send($application->id));
        $this->assertCount(1, $this->transport->messages());
    }

    public function test_submit_retries_immediately_then_cleans_up_after_success(): void
    {
        $transport = Mockery::mock(TransportInterface::class);
        $attempts = 0;
        $transport->shouldReceive('send')->times(3)->andReturnUsing(function ($message, $envelope = null) use (&$attempts) {
            $attempts++;
            $application = JobApplication::sole();
            Storage::disk('local')->assertExists($application->cv_path);
            $this->assertFalse(app(DeliverJobApplication::class)->send($application->id));
            if ($attempts < 3) {
                throw new TransportException('Temporary SMTP failure');
            }

            return $this->transport->send($message, $envelope);
        });
        Mail::mailer('smtp')->setSymfonyTransport($transport);
        $this->post(route('careers.apply', $this->career->getSlug()), $this->payload())
            ->assertSessionHasNoErrors()->assertSessionHas('success');
        $application = JobApplication::sole();
        $this->assertSame('sent', $application->email_status);
        $this->assertSame(3, $application->email_attempts);
        $this->assertNull($application->cv_path);
        $this->assertNull($application->email_next_attempt_at);
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertCount(1, $this->transport->messages());
    }

    public function test_submit_stops_after_three_failures_and_preserves_data_and_cv(): void
    {
        $this->failTransport();
        $this->post(route('careers.apply', $this->career->getSlug()), $this->payload())
            ->assertSessionHasNoErrors()->assertSessionHas('success');
        $application = JobApplication::sole();
        $this->assertSame('failed', $application->email_status);
        $this->assertSame(3, $application->email_attempts);
        $this->assertNull($application->email_next_attempt_at);
        $this->assertNull($application->email_sent_at);
        $this->assertStringNotContainsString('secret', $application->email_last_error);
        $this->assertStringContainsString('SMTP authentication failed', $application->email_last_error);
        $this->assertSame('recruitment@example.com', $application->email_recipient);
        Storage::disk('local')->assertExists($application->cv_path);
        $this->assertCount(0, $this->transport->messages());
    }

    public function test_log_mailer_and_missing_recipient_never_delete_cv(): void
    {
        $application = $this->pendingApplication();
        config(['careers.mailer' => 'log']);
        app(DeliverJobApplication::class)->send($application->id);
        Storage::disk('local')->assertExists($application->cv_path);
        $this->assertSame('failed', $application->fresh()->email_status);

        config(['careers.mailer' => 'smtp', 'careers.mail_to' => null]);
        app(DeliverJobApplication::class)->send($application->id);
        Storage::disk('local')->assertExists($application->cv_path);
        $this->assertNull($application->fresh()->email_sent_at);
        $this->assertCount(0, $this->transport->messages());
    }

    public function test_cancelled_send_and_missing_cv_are_not_marked_sent(): void
    {
        $application = $this->pendingApplication();
        Event::listen(MessageSending::class, fn () => false);
        $this->assertFalse(app(DeliverJobApplication::class)->send($application->id));
        Storage::disk('local')->assertExists($application->cv_path);
        Storage::disk('local')->delete($application->cv_path);
        $this->assertFalse(app(DeliverJobApplication::class)->send($application->id));
        $this->assertSame('failed', $application->fresh()->email_status);
        $this->assertCount(0, $this->transport->messages());
    }

    public function test_cleanup_failure_is_retried_without_duplicate_email(): void
    {
        $application = $this->pendingApplication();
        $disk = Storage::disk('local');
        $failingDisk = Mockery::mock($disk);
        $failingDisk->shouldReceive('delete')->once()->andReturn(false);
        $failingDisk->shouldReceive('delete')->once()->andReturnUsing(fn ($path) => $disk->delete($path));
        Storage::shouldReceive('disk')->with('local')->andReturn($failingDisk);

        $this->assertTrue(app(DeliverJobApplication::class)->send($application->id));
        $application->refresh();
        $this->assertSame('sent', $application->email_status);
        $this->assertNull($application->fresh()->cv_path);
        $this->assertNull($application->fresh()->email_last_error);
        $this->assertCount(1, $this->transport->messages());
    }

    public function test_existing_applications_and_concurrent_send_are_skipped(): void
    {
        $application = $this->pendingApplication(['email_status' => 'legacy']);
        $this->assertFalse(app(DeliverJobApplication::class)->send($application->id));
        $this->assertCount(0, $this->transport->messages());
        $application->update(['email_status' => 'pending']);
        $lock = Cache::lock('career-application:'.$application->id, 300);
        $lock->get();
        try {
            $this->assertFalse(app(DeliverJobApplication::class)->send($application->id));
            $this->assertSame(0, $application->fresh()->email_attempts);
        } finally {
            $lock->release();
        }
    }

    public function test_admin_can_read_applicant_data_but_not_download_deleted_cv(): void
    {
        Permission::findOrCreate('career-applications.view', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('career-applications.view');
        $application = $this->pendingApplication();
        $this->actingAs($user)->get(route('admin.careers.applications.cv', [$this->career, $application]))->assertOk();
        app(DeliverJobApplication::class)->send($application->id);
        $this->get(route('admin.careers.applications.index', $this->career))
            ->assertOk()->assertSee('Ayu Pelamar')->assertSee('Sent')
            ->assertDontSee(route('admin.careers.applications.cv', [$this->career, $application]), false)
            ->assertDontSee('Kirim ulang email');
        $this->get(route('admin.careers.applications.cv', [$this->career, $application]))->assertNotFound();
    }

    public function test_invalid_cv_does_not_create_application_or_send_email(): void
    {
        $payload = $this->payload();
        $payload['cv'] = UploadedFile::fake()->createWithContent('script.php', '<?php echo 1;');
        $this->post(route('careers.apply', $this->career->getSlug()), $payload)->assertSessionHasErrors('cv');
        $this->assertDatabaseCount('job_applications', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertCount(0, $this->transport->messages());
    }

    public function test_three_overlapping_deliveries_keep_each_applicants_file_isolated(): void
    {
        // Upload the same original filename three times; each must get its own path.
        config(['careers.mail_to' => null]);
        foreach (['A', 'B', 'C'] as $label) {
            $payload = $this->payload();
            $payload['email'] = strtolower($label).'@example.com';
            $payload['cv'] = UploadedFile::fake()->createWithContent('CV.pdf', "%PDF-1.4\nCV applicant {$label}");
            $this->post(route('careers.apply', $this->career->getSlug()), $payload)->assertSessionHasNoErrors();
        }

        $applications = JobApplication::orderBy('id')->get();
        $this->assertCount(3, $applications);
        $paths = $applications->pluck('cv_path')->all();
        $this->assertCount(3, array_unique($paths));
        $disk = Storage::disk('local');
        foreach ($paths as $path) {
            $this->assertFileExists($disk->path($path));
        }

        config(['careers.mail_to' => 'recruitment@example.com']);
        $transport = Mockery::mock(TransportInterface::class);
        $transport->shouldReceive('send')->times(5)->andReturnUsing(function ($message, $envelope = null) {
            // Pause each send while holding its application lock and keeping its CV.
            Fiber::suspend();
            if ($message->getReplyTo()[0]->getAddress() === 'c@example.com') {
                throw new TransportException('Temporary SMTP failure for applicant C');
            }

            return $this->transport->send($message, $envelope);
        });
        Mail::mailer('smtp')->setSymfonyTransport($transport);
        $delivery = app(DeliverJobApplication::class);
        $fibers = $applications->map(fn ($application) => new Fiber(fn () => $delivery->send($application->id)));
        foreach ($fibers as $fiber) {
            $fiber->start();
            $this->assertTrue($fiber->isSuspended());
        }

        // A competing worker cannot process an application already being sent.
        foreach ($applications as $application) {
            $this->assertFalse($delivery->send($application->id));
        }
        foreach ($paths as $path) {
            $this->assertFileExists($disk->path($path));
        }

        // B finishes first: only B's physical file and database path are removed.
        $fibers[1]->resume();
        $this->assertTrue($fibers[1]->getReturn());
        $this->assertFileDoesNotExist($disk->path($paths[1]));
        $this->assertNull($applications[1]->fresh()->cv_path);
        $this->assertFileExists($disk->path($paths[0]));
        $this->assertFileExists($disk->path($paths[2]));

        $fibers[2]->resume();
        $this->assertTrue($fibers[2]->isSuspended());
        $this->assertFalse($delivery->send($applications[2]->id));
        $fibers[2]->resume();
        $this->assertTrue($fibers[2]->isSuspended());
        $fibers[2]->resume();
        $this->assertFalse($fibers[2]->getReturn());
        $this->assertSame('failed', $applications[2]->fresh()->email_status);
        $this->assertSame($paths[2], $applications[2]->fresh()->cv_path);
        $this->assertFileExists($disk->path($paths[2]));

        $fibers[0]->resume();
        $this->assertTrue($fibers[0]->getReturn());
        $this->assertFileDoesNotExist($disk->path($paths[0]));
        $this->assertNull($applications[0]->fresh()->cv_path);
        $this->assertSame([$paths[2]], $disk->allFiles('cv-temporary'));

        $this->assertNull($applications[2]->fresh()->email_next_attempt_at);
        $this->assertDatabaseCount('job_applications', 3);
        $this->assertCount(2, $this->transport->messages());
        foreach ($this->transport->messages() as $sent) {
            $message = $sent->getOriginalMessage();
            $label = strtoupper($message->getReplyTo()[0]->getAddress()[0]);
            $this->assertSame("%PDF-1.4\nCV applicant {$label}", $message->getAttachments()[0]->getBody());
        }
    }

    public function test_path_is_retained_if_storage_reports_success_but_file_still_exists(): void
    {
        $application = $this->pendingApplication();
        $disk = Storage::disk('local');
        $failingDisk = Mockery::mock($disk);
        $failingDisk->shouldReceive('delete')->times(3)->andReturn(true);
        Storage::shouldReceive('disk')->with('local')->andReturn($failingDisk);

        $this->assertTrue(app(DeliverJobApplication::class)->send($application->id));
        $application->refresh();
        $this->assertSame('sent', $application->email_status);
        $this->assertNotNull($application->cv_path);
        $this->assertFileExists($disk->path($application->cv_path));
        $this->assertNotNull($application->email_last_error);

        $this->assertCount(1, $this->transport->messages());
    }

    public function test_career_delivery_has_no_scheduled_command(): void
    {
        $commands = Artisan::all();
        $this->assertArrayNotHasKey('careers:retry-emails', $commands);
        $events = app(Schedule::class)->events();
        foreach ($events as $event) {
            $this->assertStringNotContainsString('careers:retry-emails', $event->command ?? '');
        }
    }
}
