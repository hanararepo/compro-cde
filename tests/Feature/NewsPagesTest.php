<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleTag;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsPagesTest extends TestCase
{
    use RefreshDatabase;

    private ArticleCategory $csr;

    private ArticleCategory $insights;

    private User $author;

    private int $number = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Setting::flushCache();
        $this->csr = ArticleCategory::create(['slug' => 'csr-environment', 'name' => ['en' => 'CSR & Environment', 'id' => 'CSR & Lingkungan']]);
        $this->insights = ArticleCategory::create(['slug' => 'insights-trends', 'name' => ['en' => 'Insights & Trends', 'id' => 'Wawasan & Tren']]);
        $this->author = User::factory()->create();
    }

    public function test_each_category_page_contains_only_its_published_articles_and_tags(): void
    {
        $csr = $this->article($this->csr);
        $insights = $this->article($this->insights);
        $draft = $this->article($this->csr, ArticleStatus::Draft);
        $tag = ArticleTag::create(['slug' => 'only-insights', 'name' => ['en' => 'Only Insights', 'id' => 'Khusus Wawasan']]);
        $insights->tags()->attach($tag);

        foreach (['en', 'id'] as $locale) {
            foreach ([[$this->csr, $csr, $insights], [$this->insights, $insights, $csr]] as [$category, $expected, $other]) {
                $response = $this->withSession(['locale' => $locale])->get(route('news.category', ['category' => $category->slug]))->assertOk();
                $response->assertViewHas('activeCategory', fn ($active) => $active->is($category))
                    ->assertViewHas('articles', fn ($articles) => $articles->modelKeys() === [$expected->id]);
                $main = $this->main($response->getContent());
                $this->assertStringContainsString(e($expected->getTranslation('title', $locale)), $main);
                $this->assertStringNotContainsString(e($other->getTranslation('title', $locale)), $main);
                $this->assertStringNotContainsString(e($draft->getTranslation('title', $locale)), $main);
                $this->assertStringNotContainsString('news-categories', $main);
                $this->assertStringNotContainsString('name="category"', $main);
                $this->assertStringNotContainsString('/articles', $response->getContent());
                if ($category->is($this->csr)) {
                    $this->assertStringNotContainsString('Only Insights', $main);
                    $this->assertStringNotContainsString('Khusus Wawasan', $main);
                }
            }
        }
    }

    public function test_search_tags_and_pagination_cannot_escape_the_url_category(): void
    {
        $tag = ArticleTag::create(['slug' => 'energy', 'name' => ['en' => 'Energy', 'id' => 'Energi']]);
        for ($i = 0; $i < 11; $i++) {
            $this->article($this->csr)->tags()->attach($tag);
        }
        $other = $this->article($this->insights);
        $other->tags()->attach($tag);
        $this->article($this->csr, ArticleStatus::Draft)->tags()->attach($tag);

        $filters = ['search' => 'Story', 'tag' => $tag->slug];
        $response = $this->get(route('news.category', ['category' => $this->csr->slug] + $filters))->assertOk();
        $response->assertViewHas('articles', fn ($articles) => $articles->total() === 11 && $articles->count() === 9);
        $response->assertSee(e(route('news.category', ['category' => $this->csr->slug] + $filters + ['page' => 2])), false);

        $this->get(route('news.category', ['category' => $this->csr->slug] + $filters + ['page' => 2]).'&category=insights-trends')
            ->assertOk()->assertViewHas('articles', fn ($articles) => $articles->count() === 2 && $articles->every(fn ($article) => $article->article_category_id === $this->csr->id));
        $empty = $this->get(route('news.category', ['category' => $this->csr->slug, 'search' => 'no-match-here']))->assertOk();
        $this->assertStringContainsString('href="'.route('news.category', ['category' => $this->csr->slug]).'">'.__('Clear all filters'), $this->main($empty->getContent()));
    }

    public function test_legacy_urls_redirect_to_the_new_canonical_urls(): void
    {
        $article = $this->article($this->csr);
        $this->get(route('articles.index', ['category' => $this->csr->slug, 'search' => 'Story', 'page' => 2]))
            ->assertStatus(301)->assertRedirect(route('news.category', ['category' => $this->csr->slug, 'search' => 'Story', 'page' => 2]));
        $this->get(route('articles.show', $article->slug))->assertStatus(301)->assertRedirect($article->publicUrl());
        $this->get(route('articles.index'))->assertStatus(301)->assertRedirect(route('news.index'));
        $this->get(route('articles.index', ['category' => 'unknown-category']))->assertNotFound();
    }

    public function test_detail_routes_reject_other_categories_drafts_and_unknown_categories(): void
    {
        $article = $this->article($this->csr);
        $related = $this->article($this->csr);
        $other = $this->article($this->insights);
        $draft = $this->article($this->csr, ArticleStatus::Draft);

        $response = $this->get($article->publicUrl())->assertOk()
            ->assertViewHas('relatedArticles', fn ($articles) => $articles->modelKeys() === [$related->id]);
        $this->assertStringNotContainsString($other->title, $this->main($response->getContent()));
        $this->assertSame(1, $article->fresh()->views_count);
        $this->get(route('news.show', ['category' => $this->insights->slug, 'slug' => $article->slug]))->assertNotFound();
        $this->get($draft->publicUrl())->assertNotFound();
        $this->get('/unknown-category')->assertNotFound();
        $this->get('/contact')->assertOk();
        $this->get('/about-us/introduction')->assertOk();
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_language_switch_and_sitemap_use_category_urls(): void
    {
        $article = $this->article($this->insights);
        $this->from($article->publicUrl('en'))->get(route('locale.switch', 'id'))
            ->assertRedirect($article->publicUrl('id'));
        $this->get($article->publicUrl('id'))->assertOk()->assertSee($article->getTranslation('title', 'id'));
        $this->get(route('sitemap'))->assertOk()
            ->assertSee(route('news.category', ['category' => $this->insights->slug]), false)
            ->assertSee($article->publicUrl('en'), false)->assertSee($article->publicUrl('id'), false)
            ->assertDontSee('/articles', false);
    }

    private function article(ArticleCategory $category, ArticleStatus $status = ArticleStatus::Published): Article
    {
        $number = ++$this->number;

        return Article::create([
            'title' => ['en' => "Story {$number}", 'id' => "Berita {$number}"],
            'slug' => ['en' => "story-{$number}", 'id' => "berita-{$number}"],
            'content' => ['en' => 'Article content.', 'id' => 'Isi berita.'],
            'article_category_id' => $category->id,
            'author_id' => $this->author->id,
            'status' => $status,
            'published_at' => now(),
        ])->setRelation('category', $category);
    }

    private function main(string $html): string
    {
        return explode('</main>', explode('<main>', $html)[1])[0];
    }
}
