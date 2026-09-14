<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\HomeArticleCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeArticleSectionsTest extends TestCase
{
    use RefreshDatabase;

    private User $author;

    private int $articleNumber = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Setting::flushCache();
        $this->author = User::factory()->create();
    }

    public function test_csr_shows_three_articles_and_insights_includes_more_than_three_in_order(): void
    {
        $this->seed(HomeArticleCategorySeeder::class);
        $expected = [];
        $hidden = [];

        foreach (['csr-environment' => 'csrCategory', 'insights-trends' => 'insightsCategory'] as $slug => $variable) {
            $category = ArticleCategory::where('slug', $slug)->firstOrFail();
            $older = $this->article($category, ['published_at' => now()->subDays(3), 'is_featured' => true]);
            $first = $this->article($category, ['published_at' => now()->subDay()->startOfDay()]);
            $second = $this->article($category, ['published_at' => $first->published_at]);
            $latest = $this->article($category);
            $expected[$variable] = [$latest->id, $second->id, $first->id];
            if ($slug === 'insights-trends') {
                $expected[$variable][] = $older->id;
            } else {
                $hidden[] = $older;
            }
            $hidden[] = $this->article($category, ['is_featured' => false]);

            foreach ([ArticleStatus::Draft, ArticleStatus::Pending, ArticleStatus::Rejected, ArticleStatus::Unpublished] as $status) {
                $hidden[] = $this->article($category, ['status' => $status]);
            }

            $deleted = $this->article($category);
            $deleted->delete();
            $hidden[] = $deleted;
        }

        $otherCategory = ArticleCategory::create(['slug' => 'other', 'name' => ['en' => 'Other', 'id' => 'Lainnya']]);
        $hidden[] = $this->article($otherCategory, ['is_featured' => true]);
        $hidden[] = $this->article(null);

        $response = $this->get(route('home'))->assertOk()
            ->assertSeeInOrder(['id="home-csr"', 'id="home-insights"'], false);

        foreach ($expected as $variable => $ids) {
            $response->assertViewHas($variable, fn ($category) => $category->articles->modelKeys() === $ids);
        }

        foreach ($hidden as $article) {
            $response->assertDontSee($article->title);
        }

        $html = $response->getContent();
        $csrSection = explode('</section>', explode('id="home-csr"', $html)[1])[0];
        $insightsSection = explode('</section>', explode('id="home-insights"', $html)[1])[0];
        $this->assertSame(4, substr_count($insightsSection, 'data-insights-article-id='));
        $this->assertStringContainsString('home-insights-next', $insightsSection);
        $this->assertStringNotContainsString('home-insights-excerpt', $insightsSection);

        foreach ($expected['csrCategory'] as $id) {
            $title = Article::findOrFail($id)->title;
            $this->assertStringContainsString($title, $csrSection);
            $this->assertStringNotContainsString($title, $insightsSection);
        }

        foreach ($expected['insightsCategory'] as $id) {
            $title = Article::findOrFail($id)->title;
            $this->assertStringContainsString($title, $insightsSection);
            $this->assertStringNotContainsString($title, $csrSection);
        }
    }

    public function test_headings_descriptions_cards_and_links_follow_the_selected_language(): void
    {
        $this->seed(HomeArticleCategorySeeder::class);
        $articles = ArticleCategory::all()->map(fn ($category) => $this->article($category));

        foreach (['en', 'id'] as $locale) {
            $response = $this->withSession(['locale' => $locale])->get(route('home'))->assertOk()
                ->assertSee($locale === 'id' ? 'Wawasan &amp;' : 'Insights &amp;', false)
                ->assertSee($locale === 'id' ? 'Tren' : 'Trends')
                ->assertSee($locale === 'id' ? 'LINGKUNGAN' : 'ENVIRONMENT')
                ->assertSee($locale === 'id' ? 'Jelajahi sudut pandang seputar industri batubara' : 'Explore perspectives on the coal industry')
                ->assertSee($locale === 'id' ? 'Kenali upaya kami' : 'Discover our efforts')
                ->assertSee($locale === 'id' ? 'Lihat Semua' : 'View All');

            foreach ($articles as $article) {
                $response->assertSee($article->getTranslation('title', $locale))
                    ->assertSee($article->category->getTranslation('name', $locale))
                    ->assertSee($article->publicUrl($locale), false)
                    ->assertSee(route('news.category', ['category' => $article->category->slug]), false);

                if ($article->category->slug === 'insights-trends') {
                    $response->assertDontSee($article->getTranslation('content', $locale));
                } else {
                    $response->assertSee($article->getTranslation('content', $locale));
                }
            }
        }
    }

    public function test_missing_or_empty_categories_never_fall_back_to_unrelated_articles(): void
    {
        $other = ArticleCategory::create(['slug' => 'technology', 'name' => ['en' => 'Technology']]);
        $article = $this->article($other, ['is_featured' => true]);

        foreach ([false, true] as $categoriesExist) {
            if ($categoriesExist) {
                $this->seed(HomeArticleCategorySeeder::class);
                foreach (ArticleCategory::whereIn('slug', ['csr-environment', 'insights-trends'])->get() as $category) {
                    $this->article($category, ['is_featured' => false]);
                }
            }

            foreach (['en', 'id'] as $locale) {
                $response = $this->withSession(['locale' => $locale])->get(route('home'))->assertOk()
                    ->assertSee('id="home-csr"', false)
                    ->assertSee('id="home-insights"', false)
                    ->assertDontSee($article->getTranslation('title', $locale));

                $message = $locale === 'id'
                    ? 'Belum ada artikel untuk ditampilkan dalam kategori ini.'
                    : 'No articles to display in this category yet.';
                $this->assertSame(2, substr_count($response->getContent(), $message));
            }
        }
    }

    public function test_category_featured_checkbox_and_publication_changes_are_reflected_on_the_next_request(): void
    {
        $this->seed(HomeArticleCategorySeeder::class);
        $csr = ArticleCategory::where('slug', 'csr-environment')->firstOrFail();
        $insights = ArticleCategory::where('slug', 'insights-trends')->firstOrFail();
        $article = $this->article($csr);

        $this->get(route('home'))->assertOk()
            ->assertViewHas('csrCategory', fn ($category) => $category->articles->modelKeys() === [$article->id])
            ->assertViewHas('insightsCategory', fn ($category) => $category->articles->isEmpty());

        $article->update(['article_category_id' => $insights->id]);

        $this->get(route('home'))->assertOk()
            ->assertViewHas('csrCategory', fn ($category) => $category->articles->isEmpty())
            ->assertViewHas('insightsCategory', fn ($category) => $category->articles->modelKeys() === [$article->id]);

        $article->update(['is_featured' => false]);
        $this->get(route('home'))->assertOk()->assertDontSee($article->title);

        $article->update(['is_featured' => true]);
        $this->get(route('home'))->assertOk()->assertSee($article->title);

        $article->update(['status' => ArticleStatus::Unpublished]);
        $this->get(route('home'))->assertOk()->assertDontSee($article->title);
    }

    public function test_view_all_destinations_only_list_the_selected_category(): void
    {
        $this->seed(HomeArticleCategorySeeder::class);
        $articles = ArticleCategory::all()->map(fn ($category) => $this->article($category));

        foreach ($articles as $article) {
            $this->get(route('news.category', ['category' => $article->category->slug]))
                ->assertOk()
                ->assertViewHas('articles', fn ($results) => $results->modelKeys() === [$article->id]);
        }
    }

    public function test_category_setup_is_repeatable_and_preserves_existing_content(): void
    {
        $category = ArticleCategory::create([
            'slug' => 'csr-environment',
            'name' => ['en' => 'Our community', 'id' => 'Masyarakat kami'],
            'color' => '#123456',
        ]);
        $other = ArticleCategory::create(['slug' => 'other', 'name' => ['en' => 'Other']]);

        $this->seed(HomeArticleCategorySeeder::class);
        $this->seed(HomeArticleCategorySeeder::class);

        $this->assertDatabaseCount('article_categories', 3);
        $this->assertSame('Our community', $category->fresh()->getTranslation('name', 'en'));
        $this->assertSame('#123456', $category->fresh()->color);
        $this->assertModelExists($other);
        $this->assertSame('Wawasan & Tren', ArticleCategory::where('slug', 'insights-trends')->firstOrFail()->getTranslation('name', 'id'));
    }

    private function article(?ArticleCategory $category, array $attributes = []): Article
    {
        $number = ++$this->articleNumber;

        return Article::create(array_replace([
            'title' => ['en' => "Story {$number} EN", 'id' => "Cerita {$number} ID"],
            'slug' => ['en' => "story-{$number}-en", 'id' => "cerita-{$number}-id"],
            'content' => ['en' => "Article content {$number} EN", 'id' => "Isi artikel {$number} ID"],
            'article_category_id' => $category?->id,
            'author_id' => $this->author->id,
            'status' => ArticleStatus::Published,
            'is_featured' => true,
            'published_at' => now(),
        ], $attributes));
    }
}
