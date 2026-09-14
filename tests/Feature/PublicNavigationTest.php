<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Setting;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Setting::flushCache();
    }

    public function test_news_dropdown_uses_category_order_translations_and_filter_urls(): void
    {
        $second = ArticleCategory::create(['name' => ['en' => 'Insights', 'id' => 'Wawasan'], 'slug' => 'insights', 'sort_order' => 20]);
        $first = ArticleCategory::create(['name' => ['en' => 'Community', 'id' => 'Masyarakat'], 'slug' => 'community', 'sort_order' => 10]);

        foreach (['en', 'id'] as $locale) {
            $html = $this->withSession(['locale' => $locale])->get(route('home'))->assertOk()->getContent();
            $links = $this->xpath($html)->query('//header//li[@data-nav-item="news"]/ul/li/a');
            $this->assertCount(2, $links);
            foreach ([$first, $second] as $index => $category) {
                $this->assertSame($category->getTranslation('name', $locale), trim($links->item($index)->textContent));
                $this->assertSame(route('news.category', ['category' => $category->slug]), $links->item($index)->getAttribute('href'));
            }
        }
    }

    public function test_category_updates_and_deletions_are_reflected_on_the_next_request(): void
    {
        $category = ArticleCategory::create(['name' => ['en' => 'Original Category', 'id' => 'Kategori Awal'], 'slug' => 'original-category']);
        $this->withSession(['locale' => 'en'])->get(route('about.introduction'))->assertOk()->assertSee('Original Category');

        $category->update(['name' => ['en' => 'Renamed Category', 'id' => 'Kategori Baru'], 'slug' => 'renamed-category']);
        $this->get(route('about.introduction'))->assertOk()
            ->assertSee('Renamed Category')->assertDontSee('Original Category')
            ->assertSee(route('news.category', ['category' => 'renamed-category']), false);

        $category->delete();
        $html = $this->get(route('about.introduction'))->assertOk()->assertDontSee('Renamed Category')->getContent();
        $this->assertCount(0, $this->xpath($html)->query('//header//li[@data-nav-item="news"]/ul'));
    }

    public function test_news_category_link_filters_articles_and_marks_only_news_active(): void
    {
        $category = ArticleCategory::create(['name' => ['en' => 'News', 'id' => 'Berita'], 'slug' => 'company-updates']);
        $author = User::factory()->create();
        $articles = [];
        foreach (['matching', 'other', 'draft'] as $type) {
            $articles[$type] = Article::create([
                'title' => ['en' => 'Story '.$type, 'id' => 'Berita '.$type],
                'slug' => ['en' => 'story-'.$type, 'id' => 'berita-'.$type],
                'content' => ['en' => 'Article content.', 'id' => 'Isi artikel.'],
                'author_id' => $author->id,
                'article_category_id' => $type === 'other' ? null : $category->id,
                'status' => $type === 'draft' ? ArticleStatus::Draft : ArticleStatus::Published,
                'published_at' => now(),
            ]);
        }

        $response = $this->get(route('news.category', ['category' => $category->slug]))->assertOk();
        $response->assertViewHas('articles', fn ($result) => $result->modelKeys() === [$articles['matching']->id]);
        $xpath = $this->xpath($response->getContent());
        $this->assertCount(1, $xpath->query('//header//li[@data-nav-item="news" and contains(@class, "active")]/ul/li/a[@aria-current="page"]'));
        $this->assertCount(0, $xpath->query('//header//li[@data-nav-item="about" and contains(@class, "active")]'));
    }

    public function test_about_submenus_link_to_separate_pages_and_the_photo_gallery(): void
    {
        Setting::set('site_logo', '/storage/company-logo.png');
        foreach (['en', 'id'] as $locale) {
            foreach (['about.introduction', 'about.vision-mission', 'about.awards-certificates', 'about.corporate-logo', 'about.photo-gallery'] as $route) {
                $response = $this->withSession(['locale' => $locale])->get(route($route))->assertOk();
                $xpath = $this->xpath($response->getContent());
                $links = $xpath->query('//header//li[@data-nav-item="about"]/ul/li/a');
                $this->assertCount(5, $links);
                $this->assertSame($locale === 'en' ? 'Vision & Mission' : 'Visi-Misi', trim($links->item(1)->textContent));
                $this->assertSame($locale === 'en' ? 'Awards & Certification' : 'Penghargaan & Sertifikat', trim($links->item(2)->textContent));
                $current = $xpath->query('//header//li[@data-nav-item="about" and contains(@class, "active")]/ul/li/a[@aria-current="page"]');
                $this->assertCount(1, $current);
                $this->assertSame(route($route), $current->item(0)->getAttribute('href'));
                $this->assertCount(1, $xpath->query('//main//h1'));
                if ($route === 'about.corporate-logo') {
                    $this->assertSame('/storage/company-logo.png', $xpath->query('//main//figure/img')->item(0)->getAttribute('src'));
                }
            }
        }
    }

    private function xpath(string $html): DOMXPath
    {
        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument;
        $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }
}
