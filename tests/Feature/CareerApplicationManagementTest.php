<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CareerApplicationManagementTest extends TestCase
{
    use RefreshDatabase;

    private JobPosting $career;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
        Permission::findOrCreate('career-applications.view', 'web');
        Permission::findOrCreate('career-applications.delete', 'web');
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo(['career-applications.view', 'career-applications.delete']);
        $this->career = JobPosting::create([
            'title' => ['en' => 'IT Application Implementer', 'id' => 'IT Application Implementer'],
            'slug' => ['en' => 'implementer', 'id' => 'implementer'],
            'description' => ['en' => 'Description', 'id' => 'Deskripsi'],
            'type' => 'full_time', 'is_active' => true,
        ]);
    }

    private function application(array $attributes = []): JobApplication
    {
        $path = 'cv-temporary/'.Str::uuid().'.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 Test CV');

        return JobApplication::create(array_merge([
            'job_posting_id' => $this->career->id,
            'name' => 'Ayu Pratama', 'email' => Str::uuid().'@example.com', 'phone' => '08123456789',
            'cv_path' => $path, 'cv_original_name' => 'Curriculum vitae.pdf', 'email_status' => 'failed',
        ], $attributes));
    }

    public function test_bulk_delete_only_removes_selected_records_files_and_logs_each_deletion(): void
    {
        $first = $this->application();
        $second = $this->application();
        $keep = $this->application();
        $this->actingAs($this->admin)->from(route('admin.careers.applications.index', $this->career))
            ->delete(route('admin.careers.applications.bulk-destroy', $this->career), ['ids' => [$first->id, $second->id]])
            ->assertRedirect()->assertSessionHas('success', '2 lamaran berhasil dihapus.');
        $this->assertModelMissing($first);
        $this->assertModelMissing($second);
        $this->assertModelExists($keep);
        Storage::disk('local')->assertMissing([$first->cv_path, $second->cv_path]);
        Storage::disk('local')->assertExists($keep->cv_path);
        $this->assertDatabaseCount('activity_logs', 2);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $this->admin->id, 'subject_id' => $first->id, 'event' => 'deleted']);
    }

    public function test_selection_from_another_job_is_rejected_before_any_deletion(): void
    {
        $valid = $this->application();
        $otherCareer = $this->career->replicate();
        $otherCareer->save();
        $foreign = $this->application(['job_posting_id' => $otherCareer->id]);
        $this->actingAs($this->admin)->delete(route('admin.careers.applications.bulk-destroy', $this->career), ['ids' => [$valid->id, $foreign->id]])
            ->assertSessionHasErrors('ids.1');
        $this->assertModelExists($valid);
        $this->assertModelExists($foreign);
        Storage::disk('local')->assertExists([$valid->cv_path, $foreign->cv_path]);
    }

    public function test_empty_duplicate_and_oversized_selections_are_rejected(): void
    {
        $application = $this->application();
        $route = route('admin.careers.applications.bulk-destroy', $this->career);
        $this->actingAs($this->admin)->delete($route, ['ids' => []])->assertSessionHasErrors('ids');
        $this->delete($route, ['ids' => [$application->id, $application->id]])->assertSessionHasErrors('ids.0');
        $this->delete($route, ['ids' => range(1, 101)])->assertSessionHasErrors('ids');
        $this->assertModelExists($application);
    }

    public function test_view_only_user_cannot_bulk_delete_or_see_selection_controls(): void
    {
        $application = $this->application();
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('career-applications.view');
        $this->actingAs($viewer)->delete(route('admin.careers.applications.bulk-destroy', $this->career), ['ids' => [$application->id]])->assertForbidden();
        $this->get(route('admin.careers.applications.index', $this->career))->assertOk()
            ->assertDontSee('Hapus terpilih')->assertDontSee('x-ref="deleteDialog"', false);
        $this->assertModelExists($application);
    }

    public function test_active_send_lock_prevents_entire_bulk_delete(): void
    {
        $first = $this->application();
        $second = $this->application();
        $lock = Cache::lock('career-application:'.$second->id, 300);
        $lock->get();
        try {
            $this->actingAs($this->admin)->delete(route('admin.careers.applications.bulk-destroy', $this->career), ['ids' => [$first->id, $second->id]])
                ->assertSessionHasErrors('ids');
            $this->assertModelExists($first);
            $this->assertModelExists($second);
            Storage::disk('local')->assertExists([$first->cv_path, $second->cv_path]);
            $released = Cache::lock('career-application:'.$first->id, 300);
            $this->assertTrue($released->get());
            $released->release();
        } finally {
            $lock->release();
        }
    }

    public function test_file_deletion_failure_preserves_record_and_reports_partial_result(): void
    {
        $first = $this->application();
        $second = $this->application();
        $disk = Storage::disk('local');
        $partial = Mockery::mock($disk);
        $partial->shouldReceive('delete')->with($first->cv_path)->once()->andReturn(false);
        $partial->shouldReceive('delete')->with($second->cv_path)->once()->andReturnUsing(fn ($path) => $disk->delete($path));
        Storage::shouldReceive('disk')->with('local')->andReturn($partial);
        $this->actingAs($this->admin)->delete(route('admin.careers.applications.bulk-destroy', $this->career), ['ids' => [$first->id, $second->id]])
            ->assertSessionHas('error', '1 lamaran dihapus. 1 lamaran gagal dihapus dan tetap tersimpan. Silakan coba kembali.');
        $this->assertModelExists($first);
        $this->assertModelMissing($second);
        $this->assertFileExists($disk->path($first->cv_path));
        $this->assertFileDoesNotExist($disk->path($second->cv_path));
    }

    public function test_single_delete_uses_same_file_cleanup_and_lock(): void
    {
        $application = $this->application();
        $route = route('admin.careers.applications.destroy', [$this->career, $application]);
        $lock = Cache::lock('career-application:'.$application->id, 300);
        $lock->get();
        $this->actingAs($this->admin)->delete($route)->assertSessionHasErrors('ids');
        $lock->release();
        $this->delete($route)->assertSessionHas('success');
        $this->assertModelMissing($application);
        Storage::disk('local')->assertMissing($application->cv_path);
    }

    public function test_filter_and_summary_counts_are_scoped_to_current_job(): void
    {
        $this->application(['name' => 'Nadia Putri', 'email_status' => 'sent']);
        $this->application(['name' => 'Rizky Pratama', 'email_status' => 'failed']);
        $this->application(['name' => 'Dimas Saputra', 'email_status' => 'pending']);
        $this->actingAs($this->admin)->get(route('admin.careers.applications.index', [$this->career, 'status' => 'failed', 'search' => 'Rizky']))
            ->assertOk()->assertSee('Rizky Pratama')->assertDontSee('Nadia Putri')
            ->assertViewHas('stats', ['total' => 3, 'sent' => 1, 'failed' => 1, 'pending' => 1])
            ->assertSee('Hapus terpilih')->assertSee('x-ref="deleteDialog"', false)->assertDontSee('return confirm(', false);
    }

    public function test_sent_application_without_cv_can_be_deleted(): void
    {
        $application = $this->application();
        Storage::disk('local')->delete($application->cv_path);
        $application->update(['email_status' => 'sent', 'cv_path' => null]);
        $this->actingAs($this->admin)->delete(route('admin.careers.applications.bulk-destroy', $this->career), ['ids' => [$application->id]])
            ->assertSessionHas('success');
        $this->assertModelMissing($application);
    }
}
