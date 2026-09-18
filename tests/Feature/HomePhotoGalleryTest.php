<?php

namespace Tests\Feature;

use App\Enums\GalleryStatus;
use App\Models\Gallery;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePhotoGalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Setting::flushCache();
    }

    public function test_only_the_latest_eight_public_photos_appear_after_core_values(): void
    {
        $photos = collect(range(1, 9))->map(fn () => $this->photo());
        $hidden = collect([
            $this->photo(['status' => GalleryStatus::Draft]),
            $this->photo(['status' => GalleryStatus::Pending]),
            $this->photo(['is_active' => false]),
            $this->photo(),
        ]);
        $hidden->last()->delete();
        $visible = $photos->reverse()->take(8);

        $response = $this->get(route('home'))->assertOk()
            ->assertViewHas('galleries', fn ($galleries) => $galleries->modelKeys() === $visible->pluck('id')->all())
            ->assertSeeInOrder(['id="home-core-values"', 'id="home-photo-gallery"'], false);

        foreach ($visible as $photo) {
            $response->assertSee('data-gallery-photo-id="'.$photo->id.'"', false);
        }
        foreach ($hidden->push($photos->first()) as $photo) {
            $response->assertDontSee('data-gallery-photo-id="'.$photo->id.'"', false);
        }
    }

    public function test_single_photo_uses_localized_escaped_titles_and_full_image_link(): void
    {
        $photo = $this->photo(['thumbnail_path' => null]);

        foreach (['en' => 'Mine <view>', 'id' => 'Area <tambang>'] as $locale => $title) {
            $this->withSession(['locale' => $locale])->get(route('home'))
                ->assertOk()
                ->assertSee('aria-label="'.e($title).'"', false)
                ->assertSee('href="'.$photo->imageUrl().'"', false)
                ->assertSee('src="'.$photo->imageUrl().'"', false)
                ->assertSee('data-gall="home-photo-gallery"', false)
                ->assertSee('href="'.route('about.photo-gallery').'"', false)
                ->assertDontSee($title, false);
        }
    }

    public function test_gallery_is_hidden_when_no_photos_are_published(): void
    {
        $this->photo(['status' => GalleryStatus::Draft]);

        $this->get(route('home'))->assertOk()->assertDontSee('id="home-photo-gallery"', false);
    }

    private function photo(array $attributes = []): Gallery
    {
        return Gallery::create(array_replace([
            'title' => ['en' => 'Mine <view>', 'id' => 'Area <tambang>'],
            'image_path' => 'galleries/mine.jpg',
            'thumbnail_path' => 'galleries/thumbs/mine.jpg',
            'uploaded_by' => User::factory()->create()->id,
            'is_active' => true,
            'status' => GalleryStatus::Published,
        ], $attributes));
    }
}
