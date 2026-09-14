<?php

namespace Tests\Feature;

use App\Models\Award;
use App\Models\Setting;
use App\Models\User;
use App\Services\Acl\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AwardsAndCertificatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Setting::flushCache();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        (new RoleService)->seedPermissions();
        Storage::fake('public');
    }

    private function userWithPermissions(array $actions): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo(array_map(fn ($action) => 'awards.'.$action, $actions));

        return $user;
    }

    public function test_public_awards_page_renders_active_items_correctly(): void
    {
        $award = Award::create([
            'title' => ['en' => 'Best Coal Mining Company 2025', 'id' => 'Perusahaan Tambang Batubara Terbaik 2025'],
            'description' => ['en' => '<p>Excellence in mining operations.</p>', 'id' => '<p>Keunggulan operasi tambang.</p>'],
            'image_path' => 'awards/test-award.jpg',
            'type' => 'award',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $cert = Award::create([
            'title' => ['en' => 'ISO 9001:2015 Certification', 'id' => 'Sertifikasi ISO 9001:2015'],
            'description' => ['en' => '<p>Quality Management System</p>', 'id' => '<p>Sistem Manajemen Mutu</p>'],
            'image_path' => 'awards/test-cert.jpg',
            'type' => 'certificate',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $inactive = Award::create([
            'title' => ['en' => 'Hidden Award', 'id' => 'Penghargaan Tersembunyi'],
            'image_path' => 'awards/test-inactive.jpg',
            'type' => 'award',
            'is_active' => false,
            'sort_order' => 3,
        ]);

        $response = $this->get(route('about.awards-certificates'));

        $response->assertOk();
        $response->assertSee('Best Coal Mining Company 2025');
        $response->assertSee('ISO 9001:2015 Certification');
        $response->assertDontSee('Hidden Award');
    }

    public function test_navbar_contains_awards_and_certification_link(): void
    {
        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee(route('about.awards-certificates'));
    }

    public function test_unauthenticated_user_cannot_access_admin_awards(): void
    {
        $this->get(route('admin.awards.index'))->assertRedirect(route('login'));
        $this->get(route('admin.awards.create'))->assertRedirect(route('login'));
        $this->post(route('admin.awards.store'), [])->assertRedirect(route('login'));
    }

    public function test_acl_view_permission(): void
    {
        $userWithoutPermission = User::factory()->create();
        $this->actingAs($userWithoutPermission)
            ->get(route('admin.awards.index'))
            ->assertForbidden();

        $userWithPermission = $this->userWithPermissions(['view']);
        $this->actingAs($userWithPermission)
            ->get(route('admin.awards.index'))
            ->assertOk();
    }

    public function test_acl_create_and_store_award(): void
    {
        $viewer = $this->userWithPermissions(['view']);
        $creator = $this->userWithPermissions(['view', 'create']);

        // Viewer cannot access create form
        $this->actingAs($viewer)
            ->get(route('admin.awards.create'))
            ->assertForbidden();

        // Creator can access create form
        $this->actingAs($creator)
            ->get(route('admin.awards.create'))
            ->assertOk();

        // Creator can store an award with bilingual content and image
        $image = UploadedFile::fake()->image('trophy.jpg', 800, 600);
        $payload = [
            'type' => 'award',
            'title_en' => 'Green Mining Award 2026',
            'title_id' => 'Penghargaan Tambang Hijau 2026',
            'description_en' => '<p>Environmental sustainability award.</p>',
            'description_id' => '<p>Penghargaan keberlanjutan lingkungan.</p>',
            'image' => $image,
            'sort_order' => 1,
            'is_active' => '1',
        ];

        $response = $this->actingAs($creator)->post(route('admin.awards.store'), $payload);
        $response->assertRedirect(route('admin.awards.index'));

        $this->assertDatabaseHas('awards', [
            'type' => 'award',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $award = Award::where('type', 'award')->first();
        $this->assertNotNull($award);
        $this->assertSame('Green Mining Award 2026', $award->getTranslation('title', 'en'));
        $this->assertSame('Penghargaan Tambang Hijau 2026', $award->getTranslation('title', 'id'));
        Storage::disk('public')->assertExists($award->image_path);
    }

    public function test_acl_update_award(): void
    {
        $award = Award::create([
            'title' => ['en' => 'Initial Title', 'id' => 'Judul Awal'],
            'image_path' => 'awards/old-pic.jpg',
            'type' => 'certificate',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $editor = $this->userWithPermissions(['view', 'edit']);
        $viewer = $this->userWithPermissions(['view']);

        // Viewer cannot edit
        $this->actingAs($viewer)
            ->get(route('admin.awards.edit', $award))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->put(route('admin.awards.update', $award), ['title_en' => 'Hacked'])
            ->assertForbidden();

        // Editor can access edit form
        $this->actingAs($editor)
            ->get(route('admin.awards.edit', $award))
            ->assertOk()
            ->assertSee('Initial Title');

        // Editor can update
        $updatePayload = [
            'type' => 'certificate',
            'title_en' => 'Updated Certificate 2026',
            'title_id' => 'Sertifikat Diperbarui 2026',
            'description_en' => '<p>Updated details</p>',
            'description_id' => '<p>Detail diperbarui</p>',
            'sort_order' => 5,
            'is_active' => '1',
        ];

        $this->actingAs($editor)
            ->put(route('admin.awards.update', $award), $updatePayload)
            ->assertRedirect(route('admin.awards.index'));

        $award->refresh();
        $this->assertSame('Updated Certificate 2026', $award->getTranslation('title', 'en'));
        $this->assertSame(5, $award->sort_order);
    }

    public function test_acl_delete_award(): void
    {
        $award = Award::create([
            'title' => ['en' => 'To Delete', 'id' => 'Akan Dihapus'],
            'image_path' => 'awards/delete-me.jpg',
            'type' => 'award',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $viewer = $this->userWithPermissions(['view']);
        $deleter = $this->userWithPermissions(['view', 'delete']);

        // Viewer cannot delete
        $this->actingAs($viewer)
            ->delete(route('admin.awards.destroy', $award))
            ->assertForbidden();

        // Deleter can delete
        $this->actingAs($deleter)
            ->delete(route('admin.awards.destroy', $award))
            ->assertRedirect(route('admin.awards.index'));

        $this->assertSoftDeleted('awards', ['id' => $award->id]);
    }
}
