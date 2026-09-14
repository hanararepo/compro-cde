<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Slider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeHeroSliderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Setting::flushCache();
    }

    public function test_home_shows_only_active_sliders_in_dashboard_order(): void
    {
        $last = $this->slider(['sort_order' => 10]);
        $first = $this->slider(['sort_order' => 1]);
        $second = $this->slider(['sort_order' => 1]);
        $inactive = $this->slider(['is_active' => false]);
        $deleted = $this->slider();
        $deleted->delete();

        $this->get(route('home'))
            ->assertOk()
            ->assertViewHas('sliders', fn ($sliders) => $sliders->modelKeys() === [$first->id, $second->id, $last->id])
            ->assertSeeInOrder([
                'data-slider-id="'.$first->id.'"',
                'data-slider-id="'.$second->id.'"',
                'data-slider-id="'.$last->id.'"',
            ], false)
            ->assertDontSee('data-slider-id="'.$inactive->id.'"', false)
            ->assertDontSee('data-slider-id="'.$deleted->id.'"', false)
            ->assertSee('home-hero-pagination');
    }

    public function test_hero_uses_localized_title_and_description_with_responsive_images(): void
    {
        $slider = $this->slider();

        foreach ([
            'en' => ['Committed', 'Commitment to sustainability'],
            'id' => ['Berkomitmen', 'Komitmen terhadap keberlanjutan'],
        ] as $locale => [$title, $description]) {
            $response = $this->withSession(['locale' => $locale])->get(route('home'));

            $response->assertOk()
                ->assertSee('<h4 class="sub-heading">'.$title.'</h4>', false)
                ->assertSee('<h2 class="section-title cursor-effect text-white">'.$description.'</h2>', false)
                ->assertSee('<source media="(max-width: 767px)" srcset="'.$slider->mobileImageUrl().'">', false)
                ->assertSee('src="'.$slider->desktopImageUrl().'"', false)
                ->assertSee('fetchpriority="high"', false)
                ->assertDontSee('The Art of Stunning')
                ->assertDontSee('slider-img-1.jpg');
        }
    }

    public function test_single_slider_without_mobile_image_or_description_uses_desktop_image(): void
    {
        $slider = $this->slider(['image_mobile' => null, 'description' => null]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('src="'.$slider->desktopImageUrl().'"', false)
            ->assertDontSee('<source media="(max-width: 767px)"', false)
            ->assertDontSee('home-hero-pagination');

        $hero = explode('<!-- ./ slider-section -->', $response->getContent())[0];
        $this->assertStringNotContainsString('<h2 class="section-title cursor-effect text-white">', $hero);
    }

    public function test_dashboard_data_changes_are_visible_on_the_next_request(): void
    {
        $slider = $this->slider();
        $this->get(route('home'))->assertSee('Committed');

        $slider->update([
            'title' => ['en' => 'New campaign', 'id' => 'Kampanye baru'],
            'description' => ['en' => '<script>alert("test")</script>', 'id' => 'Deskripsi baru'],
        ]);
        $this->get(route('home'))
            ->assertSee('New campaign')
            ->assertSee('<script>alert("test")</script>')
            ->assertDontSee('<script>alert("test")</script>', false)
            ->assertDontSee('Committed');

        $newSlider = $this->slider();
        $this->get(route('home'))->assertViewHas('sliders', fn ($sliders) => $sliders->count() === 2);

        $slider->update(['is_active' => false]);
        $newSlider->delete();
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('class="antra-slider', false)
            ->assertSee('home-header-background');
    }

    private function slider(array $attributes = []): Slider
    {
        return Slider::create(array_replace([
            'title' => ['en' => 'Committed', 'id' => 'Berkomitmen'],
            'description' => ['en' => 'Commitment to sustainability', 'id' => 'Komitmen terhadap keberlanjutan'],
            'image_desktop' => 'sliders/desktop/hero.jpg',
            'image_mobile' => 'sliders/mobile/hero.jpg',
            'sort_order' => 0,
            'is_active' => true,
        ], $attributes));
    }
}
