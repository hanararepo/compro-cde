<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ClosedJobPostingTest extends TestCase
{
    use RefreshDatabase;

    private JobPosting $career;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['recaptcha.site_key' => null]);
        Storage::fake('local');
        Mail::fake();
        $this->career = JobPosting::create([
            'title' => ['en' => 'IT Implementer', 'id' => 'Implementer TI'],
            'slug' => ['en' => 'it-implementer', 'id' => 'implementer-ti'],
            'description' => ['en' => 'Requirements for this position.', 'id' => 'Persyaratan posisi ini.'],
            'type' => 'full_time', 'is_active' => true,
        ])->refresh();
        $this->admin = User::factory()->create();
        foreach (['careers.view', 'careers.create', 'careers.edit'] as $permission) {
            Permission::findOrCreate($permission, 'web');
            $this->admin->givePermissionTo($permission);
        }
    }

    private function jobPayload(array $overrides = []): array
    {
        return array_merge([
            'title_en' => 'IT Implementer', 'title_id' => 'Implementer TI',
            'description_en' => 'Requirements for this position.', 'description_id' => 'Persyaratan posisi ini.',
            'type' => 'full_time', 'is_active' => '1', 'is_closed' => '1',
        ], $overrides);
    }

    private function applicationPayload(): array
    {
        return ['name' => 'Ayu', 'email' => 'ayu@example.com', 'phone' => '08123456789',
            'cv' => UploadedFile::fake()->createWithContent('CV.pdf', "%PDF-1.4\nApplicant CV")];
    }

    public function test_closed_job_remains_public_but_has_no_application_form_or_job_schema(): void
    {
        $this->career->update(['is_closed' => true]);
        $this->get(route('careers.index'))->assertOk()->assertSee('IT Implementer')->assertSee('Closed')
            ->assertSee('View Details')->assertDontSee('View &amp; Apply', false);
        $this->get(route('careers.show', $this->career->getSlug()))->assertOk()
            ->assertSee('Applications are closed')->assertSee('Requirements for this position.')
            ->assertDontSee('id="career-apply-form"', false)->assertDontSee('"@type": "JobPosting"', false);
    }

    public function test_direct_submission_to_closed_job_does_not_store_cv_or_send_email(): void
    {
        $this->career->update(['is_closed' => true]);
        $this->from(route('careers.show', $this->career->getSlug()))
            ->post(route('careers.apply', $this->career->getSlug()), $this->applicationPayload())
            ->assertRedirect()->assertSessionHasErrors('application');
        $this->assertDatabaseCount('job_applications', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
        Mail::assertNothingSent();
    }

    public function test_job_closed_during_upload_is_rechecked_and_temporary_file_removed(): void
    {
        $disk = Storage::disk('local');
        $interleavedDisk = Mockery::mock($disk);
        $interleavedDisk->shouldReceive('putFileAs')->once()->andReturnUsing(function (...$arguments) use ($disk) {
            $path = $disk->putFileAs(...$arguments);
            $this->career->update(['is_closed' => true]);

            return $path;
        });
        Storage::shouldReceive('disk')->with('local')->andReturn($interleavedDisk);
        $this->post(route('careers.apply', $this->career->getSlug()), $this->applicationPayload())
            ->assertSessionHasErrors('application');
        $this->assertDatabaseCount('job_applications', 0);
        $this->assertSame([], $disk->allFiles());
        Mail::assertNothingSent();
    }

    public function test_admin_can_close_and_reopen_without_unpublishing_or_losing_applicants(): void
    {
        $application = JobApplication::create(['job_posting_id' => $this->career->id, 'name' => 'Existing Applicant',
            'email' => 'applicant@example.com', 'phone' => '081234', 'cv_original_name' => 'CV.pdf', 'cv_path' => null, 'email_status' => 'sent']);
        $this->actingAs($this->admin)->put(route('admin.careers.update', $this->career), $this->jobPayload())
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.careers.index'));
        $this->career->refresh();
        $this->assertTrue($this->career->is_active);
        $this->assertTrue($this->career->is_closed);
        $this->assertModelExists($application);
        $this->get(route('careers.show', $this->career->getSlug()))->assertDontSee('id="career-apply-form"', false);
        $this->put(route('admin.careers.update', $this->career), $this->jobPayload(['is_closed' => '0']))->assertSessionHasNoErrors();
        $this->assertFalse($this->career->fresh()->is_closed);
        $this->get(route('careers.show', $this->career->getSlug()))->assertOk()->assertSee('id="career-apply-form"', false);
    }

    public function test_admin_can_create_closed_job_and_filter_by_recruitment_status(): void
    {
        $this->actingAs($this->admin)->get(route('admin.careers.create'))->assertOk()->assertSee('Recruitment status');
        $this->get(route('admin.careers.edit', $this->career))->assertOk()->assertSee('name="is_closed"', false);
        $this->post(route('admin.careers.store'), $this->jobPayload(['title_en' => 'Closed vacancy']))
            ->assertSessionHasNoErrors();
        $closed = JobPosting::where('is_closed', true)->sole();
        $this->assertTrue($closed->is_active);
        $this->get(route('admin.careers.index', ['recruitment_status' => '1']))->assertOk()
            ->assertViewHas('jobs', fn ($jobs) => $jobs->count() === 1 && $jobs->first()->id === $closed->id);
    }

    public function test_unpublished_job_remains_hidden_even_if_recruitment_is_closed(): void
    {
        $this->career->update(['is_active' => false, 'is_closed' => true]);
        $this->get(route('careers.index'))->assertOk()->assertDontSee('IT Implementer');
        $this->get(route('careers.show', $this->career->getSlug()))->assertNotFound();
        $this->post(route('careers.apply', $this->career->getSlug()), $this->applicationPayload())->assertNotFound();
        $this->assertDatabaseCount('job_applications', 0);
    }

    public function test_invalid_status_and_unauthorized_updates_are_rejected(): void
    {
        $this->actingAs($this->admin)->put(route('admin.careers.update', $this->career), $this->jobPayload(['is_closed' => 'invalid']))
            ->assertSessionHasErrors('is_closed');
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('careers.view');
        $this->actingAs($viewer)->put(route('admin.careers.update', $this->career), $this->jobPayload())->assertForbidden();
        $this->assertFalse($this->career->fresh()->is_closed);
    }
}
