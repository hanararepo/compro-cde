<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\User;
use App\Services\Gallery\GalleryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GalleryThumbnailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Permission::findOrCreate('galleries.approve', 'web');
    }

    public function test_uploads_generate_larger_uncropped_thumbnails_in_the_original_format(): void
    {
        foreach (['jpg' => 'image/jpeg', 'png' => 'image/png'] as $extension => $mime) {
            $gallery = $this->upload(2400, 1800, $extension);
            $dimensions = getimagesize(Storage::disk('public')->path($gallery->thumbnail_path));

            $this->assertSame([1600, 1200], array_slice($dimensions, 0, 2));
            $this->assertSame($mime, $dimensions['mime']);
            $original = getimagesize(Storage::disk('public')->path($gallery->image_path));
            $this->assertSame([2400, 1800], array_slice($original, 0, 2));
        }
    }

    public function test_small_originals_are_not_enlarged_and_replacement_uploads_use_the_new_size(): void
    {
        $gallery = $this->upload(600, 400);
        $dimensions = getimagesize(Storage::disk('public')->path($gallery->thumbnail_path));
        $this->assertSame([600, 400], array_slice($dimensions, 0, 2));

        $gallery = app(GalleryService::class)->updateGallery($gallery, [
            'image' => UploadedFile::fake()->image('portrait.jpg', 2000, 3000),
        ], $gallery->uploader);

        $dimensions = getimagesize(Storage::disk('public')->path($gallery->thumbnail_path));
        $this->assertSame([1600, 2400], array_slice($dimensions, 0, 2));
    }

    public function test_regeneration_uses_originals_and_changes_the_url_without_removing_existing_files(): void
    {
        $gallery = $this->upload(2400, 1800);
        $disk = Storage::disk('public');
        $oldPath = $gallery->thumbnail_path;
        $oldImage = UploadedFile::fake()->image('old.jpg', 400, 300);
        $disk->put($oldPath, file_get_contents($oldImage->getRealPath()));
        $originalHash = hash('sha256', $disk->get($gallery->image_path));
        $originalPath = $gallery->image_path;
        $createdAt = $gallery->created_at;

        $this->artisan('gallery:regenerate-thumbnails', ['--id' => [$gallery->id]])
            ->expectsOutput('Regenerated: 1. Failed: 0.')
            ->assertSuccessful();

        $gallery->refresh();
        $this->assertNotSame($oldPath, $gallery->thumbnail_path);
        $this->assertSame($originalPath, $gallery->image_path);
        $this->assertSame($originalHash, hash('sha256', $disk->get($gallery->image_path)));
        $this->assertTrue($createdAt->equalTo($gallery->created_at));
        $disk->assertExists($oldPath);
        $dimensions = getimagesize($disk->path($gallery->thumbnail_path));
        $this->assertSame([1600, 1200], array_slice($dimensions, 0, 2));
    }

    public function test_missing_original_leaves_the_existing_thumbnail_unchanged(): void
    {
        $gallery = $this->upload(600, 400);
        $oldPath = $gallery->thumbnail_path;
        Storage::disk('public')->delete($gallery->image_path);

        $this->artisan('gallery:regenerate-thumbnails', ['--id' => [$gallery->id]])
            ->expectsOutput('Regenerated: 0. Failed: 1.')
            ->assertFailed();

        $this->assertSame($oldPath, $gallery->fresh()->thumbnail_path);
        Storage::disk('public')->assertExists($oldPath);
    }

    private function upload(int $width, int $height, string $extension = 'jpg'): Gallery
    {
        return app(GalleryService::class)->createGallery([
            'title_en' => 'Gallery photo',
            'image' => UploadedFile::fake()->image('photo.'.$extension, $width, $height),
        ], User::factory()->create());
    }
}
