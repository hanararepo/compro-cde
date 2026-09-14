<?php

namespace Tests\Feature;

use App\Models\GalleryVideo;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeVideoGalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Setting::flushCache();
    }

    public function test_active_dashboard_videos_appear_after_the_hero_in_dashboard_order(): void
    {
        $last = $this->video(['sort_order' => 10]);
        $older = $this->video(['sort_order' => 1]);
        $older->forceFill(['created_at' => now()->subDay()])->save();
        $newer = $this->video(['sort_order' => 1]);
        $inactive = $this->video(['is_active' => false]);
        $deleted = $this->video();
        $deleted->delete();

        $this->get(route('home'))
            ->assertOk()
            ->assertViewHas('galleryVideos', fn ($videos) => $videos->modelKeys() === [$newer->id, $older->id, $last->id])
            ->assertSeeInOrder([
                '<!-- ./ slider-section -->',
                'home-video-section',
                'data-gallery-video-id="'.$newer->id.'"',
                'data-gallery-video-id="'.$older->id.'"',
                'data-gallery-video-id="'.$last->id.'"',
                'id="home-csr"',
            ], false)
            ->assertDontSee('data-gallery-video-id="'.$inactive->id.'"', false)
            ->assertDontSee('data-gallery-video-id="'.$deleted->id.'"', false);
    }

    public function test_video_titles_and_controls_follow_the_selected_language(): void
    {
        $video = $this->video();

        foreach ([
            'en' => ['Company Profile', 'Video Gallery', 'Watch Video', 'Play video: Company Profile'],
            'id' => ['Profil Perusahaan', 'Galeri Video', 'Tonton Video', 'Putar video: Profil Perusahaan'],
        ] as $locale => [$title, $heading, $action, $label]) {
            $this->withSession(['locale' => $locale])->get(route('home'))
                ->assertOk()
                ->assertSee('class="home-video-name">'.$title.'</h3>', false)
                ->assertSee($heading)
                ->assertSee($action)
                ->assertSee('aria-label="'.$label.'"', false)
                ->assertSee('href="'.$video->embedUrl().'"', false)
                ->assertSee('src="'.$video->thumbnailUrl(highResolution: true).'"', false)
                ->assertSee('data-fallback="'.$video->thumbnailUrl().'"', false)
                ->assertSee($locale === 'id' ? 'Kenali Lebih Dekat' : 'A Closer Look')
                ->assertSee($locale === 'id' ? 'Kenali tim kami' : 'Meet our people')
                ->assertSee('data-vbtype="video"', false);
        }
    }

    public function test_dashboard_updates_are_reflected_and_an_empty_gallery_is_hidden(): void
    {
        $this->get(route('home'))->assertOk()->assertDontSee('home-video-section');

        $video = $this->video();
        $this->get(route('home'))->assertSee('class="home-video-name">Company Profile</h3>', false);

        $video->update([
            'title' => ['en' => '<b>Updated company profile</b>', 'id' => 'Profil terbaru'],
            'youtube_url' => 'https://youtu.be/abcdefghijk',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<b>Updated company profile</b>')
            ->assertDontSee('<b>Updated company profile</b>', false)
            ->assertSee('https://www.youtube.com/embed/abcdefghijk')
            ->assertDontSee('class="home-video-name">Company Profile</h3>', false);

        $video->update(['is_active' => false]);
        $this->get(route('home'))->assertOk()->assertDontSee('home-video-section');

        $video->update(['is_active' => true]);
        $video->delete();
        $this->get(route('home'))->assertOk()->assertDontSee('home-video-section');
    }

    private function video(array $attributes = []): GalleryVideo
    {
        return GalleryVideo::create(array_replace([
            'title' => ['en' => 'Company Profile', 'id' => 'Profil Perusahaan'],
            'youtube_url' => 'https://www.youtube.com/watch?v=vBlyTby1HvE',
            'sort_order' => 0,
            'is_active' => true,
        ], $attributes));
    }
}
