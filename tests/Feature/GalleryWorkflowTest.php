<?php

namespace Tests\Feature;

use App\Enums\GalleryStatus;
use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Models\User;
use App\Services\Gallery\GalleryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');
    }

    public function test_author_can_create_gallery_as_draft_and_request_approval(): void
    {
        $author = User::where('email', 'author@cms.local')->first();
        $admin = User::where('email', 'admin@cms.local')->first();
        $category = GalleryCategory::first();

        // 1. Author creates gallery as draft
        $galleryService = new GalleryService;
        $file = UploadedFile::fake()->image('test-photo.jpg', 600, 400);

        $gallery = $galleryService->createGallery([
            'title_en' => 'Author Sunset View',
            'title_id' => 'Pemandangan Matahari Terbenam',
            'description_en' => 'Beautiful sunset.',
            'description_id' => 'Matahari terbenam yang indah.',
            'image' => $file,
            'gallery_category_id' => $category->id,
            '_action' => 'set_draft',
        ], $author);

        $this->assertEquals(GalleryStatus::Draft, $gallery->status);

        // 2. Draft is not visible on public site
        $publicResponse = $this->get(route('about.photo-gallery'));
        $publicResponse->assertDontSee('Author Sunset View');

        // 3. Author submits for approval
        $gallery = $galleryService->updateGallery($gallery, [
            'title_en' => 'Author Sunset View',
            'title_id' => 'Pemandangan Matahari Terbenam',
            '_action' => 'request_approval',
        ], $author);

        $this->assertEquals(GalleryStatus::Pending, $gallery->status);

        // 4. Admin can view pending gallery queue
        $pendingResponse = $this->actingAs($admin)->get(route('admin.galleries.pending'));
        $pendingResponse->assertStatus(200);
        $pendingResponse->assertSee('Author Sunset View');

        // 5. Admin approves gallery
        $galleryService->approveGallery($gallery, $admin);
        $gallery->refresh();

        $this->assertEquals(GalleryStatus::Published, $gallery->status);
        $this->assertEquals($admin->id, $gallery->approved_by);
        $this->assertNotNull($gallery->approved_at);

        // 6. Now visible on public site
        $publicResponseAfter = $this->get(route('about.photo-gallery'));
        $publicResponseAfter->assertSee('Author Sunset View');
    }

    public function test_gallery_ownership_scoping(): void
    {
        $author = User::where('email', 'author@cms.local')->first();
        $admin = User::where('email', 'admin@cms.local')->first();

        // Admin uploads a gallery item
        $galleryService = new GalleryService;
        $file1 = UploadedFile::fake()->image('admin-photo.jpg', 600, 400);
        $galleryService->createGallery([
            'title_en' => 'Admin Secret Photo',
            'title_id' => 'Foto Rahasia Admin',
            'image' => $file1,
        ], $admin);

        // Author uploads a gallery item
        $file2 = UploadedFile::fake()->image('author-photo.jpg', 600, 400);
        $galleryService->createGallery([
            'title_en' => 'Author Personal Photo',
            'title_id' => 'Foto Pribadi Penulis',
            'image' => $file2,
            '_action' => 'set_draft',
        ], $author);

        // Author without view-others sees only own photo
        $authorResponse = $this->actingAs($author)->get(route('admin.galleries.index'));
        $authorResponse->assertStatus(200);
        $authorResponse->assertSee('Author Personal Photo');
        $authorResponse->assertDontSee('Admin Secret Photo');

        // Admin sees both
        $adminResponse = $this->actingAs($admin)->get(route('admin.galleries.index'));
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Author Personal Photo');
        $adminResponse->assertSee('Admin Secret Photo');
    }

    public function test_editor_can_reject_and_author_can_revert_to_draft(): void
    {
        $author = User::where('email', 'author@cms.local')->first();
        $editor = User::where('email', 'editor@cms.local')->first();

        $galleryService = new GalleryService;
        $file = UploadedFile::fake()->image('gallery-sample.jpg', 600, 400);

        $gallery = $galleryService->createGallery([
            'title_en' => 'Needs Improvement Photo',
            'title_id' => 'Foto Perlu Perbaikan',
            'image' => $file,
            '_action' => 'request_approval',
        ], $author);

        $this->assertEquals(GalleryStatus::Pending, $gallery->status);

        // Editor rejects
        $galleryService->rejectGallery($gallery, $editor, 'Image quality too low.');
        $gallery->refresh();

        $this->assertEquals(GalleryStatus::Rejected, $gallery->status);
        $this->assertEquals('Image quality too low.', $gallery->rejection_reason);

        // Author views edit page and sees rejection reason
        $editResponse = $this->actingAs($author)->get(route('admin.galleries.edit', $gallery));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Image quality too low.');

        // Author sets to draft
        $gallery = $galleryService->updateGallery($gallery, [
            'title_en' => 'Fixed Photo',
            'title_id' => 'Foto Sudah Diperbaiki',
            '_action' => 'set_draft',
        ], $author);

        $this->assertEquals(GalleryStatus::Draft, $gallery->status);
        $this->assertNull($gallery->rejection_reason);
    }
}
