<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UniqueJobApplicationTest extends TestCase
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
        Mail::mailer('smtp')->setSymfonyTransport($this->transport);
        $this->career = JobPosting::create([
            'title' => ['en' => 'Engineer'], 'slug' => ['en' => 'engineer'],
            'description' => ['en' => 'Job description'], 'type' => 'full_time', 'is_active' => true,
        ]);
    }

    private function payload(string $email = 'ayu@example.com'): array
    {
        return ['name' => 'Ayu', 'email' => $email, 'phone' => '08123456789',
            'cv' => UploadedFile::fake()->createWithContent('CV.pdf', "%PDF-1.4\nApplicant CV")];
    }

    private function existingApplication(string $status = 'sent'): JobApplication
    {
        return JobApplication::create([
            'job_posting_id' => $this->career->id, 'name' => 'Original Applicant',
            'email' => 'ayu@example.com', 'phone' => '08123456789',
            'cv_original_name' => 'Original.pdf', 'cv_path' => null, 'email_status' => $status,
        ]);
    }

    public function test_repeated_submit_does_not_create_another_record_or_send_another_email(): void
    {
        $url = route('careers.apply', $this->career->getSlug());
        $this->post($url, $this->payload())->assertSessionHasNoErrors();
        $this->assertCount(1, $this->transport->messages());
        $this->post($url, $this->payload())->assertSessionHasErrors('email');
        $this->assertDatabaseCount('job_applications', 1);
        $this->assertCount(1, $this->transport->messages());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_same_email_can_apply_to_a_different_job(): void
    {
        $other = $this->career->replicate();
        $other->slug = ['en' => 'another-position'];
        $other->save();
        $this->post(route('careers.apply', $this->career->getSlug()), $this->payload())->assertSessionHasNoErrors();
        $this->post(route('careers.apply', $other->getSlug()), $this->payload())->assertSessionHasNoErrors();
        $this->assertDatabaseCount('job_applications', 2);
        $this->assertDatabaseHas('job_applications', ['job_posting_id' => $other->id, 'email' => 'ayu@example.com']);
        $this->assertCount(2, $this->transport->messages());
    }

    public function test_email_is_normalized_and_case_or_whitespace_cannot_bypass_limit(): void
    {
        $url = route('careers.apply', $this->career->getSlug());
        $this->post($url, $this->payload('  Ayu@Example.COM  '))->assertSessionHasNoErrors();
        $this->assertSame('ayu@example.com', JobApplication::sole()->email);
        $this->post($url, $this->payload('AYU@example.com'))->assertSessionHasErrors('email');
        $this->assertDatabaseCount('job_applications', 1);
        $this->assertCount(1, $this->transport->messages());
    }

    public static function deliveryStatuses(): array
    {
        return [['sent'], ['failed'], ['pending'], ['legacy']];
    }

    #[DataProvider('deliveryStatuses')]
    public function test_duplicate_is_rejected_regardless_of_delivery_status(string $status): void
    {
        $original = $this->existingApplication($status);
        Storage::disk('local')->put('cv-temporary/original.pdf', 'Original CV');
        $original->update(['cv_path' => 'cv-temporary/original.pdf']);
        $this->post(route('careers.apply', $this->career->getSlug()), $this->payload())->assertSessionHasErrors('email');
        $this->assertSame('Original Applicant', $original->fresh()->name);
        $this->assertSame($status, $original->fresh()->email_status);
        $this->assertSame(['cv-temporary/original.pdf'], Storage::disk('local')->allFiles());
        $this->assertCount(0, $this->transport->messages());
    }

    public function test_competing_submit_during_upload_is_rejected_and_only_losing_cv_is_removed(): void
    {
        $disk = Storage::disk('local');
        $interleavedDisk = Mockery::mock($disk);
        $interleavedDisk->shouldReceive('putFileAs')->once()->andReturnUsing(function (...$arguments) use ($disk) {
            $path = $disk->putFileAs(...$arguments);
            // A competing request commits after validation but before this request inserts.
            $winner = $this->existingApplication('pending');
            $disk->put('cv-temporary/winner.pdf', 'Winner CV');
            $winner->update(['cv_path' => 'cv-temporary/winner.pdf']);

            return $path;
        });
        Storage::shouldReceive('disk')->with('local')->andReturn($interleavedDisk);
        $this->post(route('careers.apply', $this->career->getSlug()), $this->payload())->assertSessionHasErrors('email');
        $this->assertDatabaseCount('job_applications', 1);
        $this->assertSame('Original Applicant', JobApplication::sole()->name);
        $this->assertSame(['cv-temporary/winner.pdf'], $disk->allFiles());
        $this->assertCount(0, $this->transport->messages());
    }

    public function test_database_unique_index_rejects_duplicate_outside_the_form(): void
    {
        $this->existingApplication();
        $this->expectException(UniqueConstraintViolationException::class);
        $this->existingApplication();
    }
}
