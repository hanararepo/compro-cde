<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use App\Services\Article\ArticleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsFoundationFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_homepage_renders_successfully(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Hanara CMS');
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_default_login_url_returns_404_when_obscured(): void
    {
        // When dynamic login path is active, standard /login returns 404
        $response = $this->get('/login');
        $response->assertStatus(404);

        // While the configured secret login path returns 200
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Sign In');
    }

    public function test_administrator_can_login_and_access_dashboard(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'admin@cms.local',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        $dashboardResponse = $this->get(route('admin.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Dashboard Overview');
    }

    public function test_administrator_bypasses_all_permission_checks(): void
    {
        $admin = User::where('email', 'admin@cms.local')->first();

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get(route('admin.roles.index'))
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get(route('admin.articles.pending'))
            ->assertStatus(200);
    }

    public function test_article_approval_workflow(): void
    {
        $author = User::where('email', 'author@cms.local')->first();
        $admin = User::where('email', 'admin@cms.local')->first();
        $category = ArticleCategory::first();

        // 1. Author creates article -> saved as draft
        $articleService = new ArticleService;
        $article = $articleService->createArticle([
            'title_en' => 'Author Draft Story',
            'title_id' => 'Cerita Draf Penulis',
            'content_en' => 'Draft content by author for editorial review.',
            'content_id' => 'Konten draf oleh penulis untuk ditinjau.',
            'article_category_id' => $category->id,
        ], $author);

        $this->assertEquals(ArticleStatus::Draft, $article->status);

        // 2. Author submits article for approval
        $article = $articleService->updateArticle($article, [
            'title_en' => 'Author Draft Story',
            'title_id' => 'Cerita Draf Penulis',
            'content_en' => 'Draft content by author for editorial review.',
            'content_id' => 'Konten draf oleh penulis untuk ditinjau.',
            '_action' => 'request_approval',
        ], $author);

        $this->assertEquals(ArticleStatus::Pending, $article->status);

        // 3. Admin can preview the pending article
        $previewResponse = $this->actingAs($admin)
            ->get(route('admin.articles.preview', $article));
        $previewResponse->assertStatus(200);
        $previewResponse->assertSee('PREVIEW MODE');

        // 3. Admin approves article -> status becomes Published
        $articleService->approveArticle($article, $admin);
        $article->refresh();

        $this->assertEquals(ArticleStatus::Published, $article->status);
        $this->assertNotNull($article->approved_at);
        $this->assertEquals($admin->id, $article->approved_by);
    }

    public function test_user_filtering_and_search(): void
    {
        $admin = User::where('email', 'admin@cms.local')->first();

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'Super', 'role' => 'Administrator', 'status' => '1']))
            ->assertStatus(200)
            ->assertSee('admin@cms.local');
    }

    public function test_article_filtering_and_search(): void
    {
        $admin = User::where('email', 'admin@cms.local')->first();

        // Test searching lowercase "mastering" matches "Mastering Tailwind CSS v4 & Alpine.js"
        $this->actingAs($admin)
            ->get(route('admin.articles.index', ['search' => 'mastering']))
            ->assertStatus(200)
            ->assertSee('Mastering Tailwind');

        // Test searching Indonesian term "membangun" matches Indonesian title
        $this->actingAs($admin)
            ->get(route('admin.articles.index', ['search' => 'membangun']))
            ->assertStatus(200)
            ->assertSee('Building a Scalable');
    }

    public function test_public_articles_show_increments_views(): void
    {
        $article = Article::where('status', ArticleStatus::Published)->first();
        $initialViews = $article->views_count;

        $slug = $article->getTranslation('slug', 'en');
        $response = $this->get($article->publicUrl('en'));

        $response->assertStatus(200);
        $this->assertEquals($initialViews + 1, $article->fresh()->views_count);
    }
}
