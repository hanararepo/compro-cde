<?php

namespace App\Http\Controllers;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\CoalProduct;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Generate the XML sitemap with caching and minimal DB column selection.
     */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap_xml', now()->addDay(), function () {
            $urls = [];

            // Static pages
            $urls[] = ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'];
            $urls[] = ['loc' => route('news.index'), 'priority' => '0.8', 'changefreq' => 'daily'];
            $urls[] = ['loc' => route('about.photo-gallery'), 'priority' => '0.7', 'changefreq' => 'weekly'];
            $urls[] = ['loc' => route('coal-products.index'), 'priority' => '0.7', 'changefreq' => 'weekly'];

            foreach (CoalProduct::select(['slug', 'updated_at'])->get() as $product) {
                $urls[] = [
                    'loc' => route('coal-products.show', ['coalProduct' => $product->slug]),
                    'lastmod' => $product->updated_at->toAtomString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ];
            }

            // Category pages — only select id and slug
            foreach (ArticleCategory::select(['id', 'slug'])->get() as $category) {
                $urls[] = [
                    'loc' => route('news.category', ['category' => $category->slug]),
                    'changefreq' => 'weekly',
                    'priority' => '0.6',
                ];
            }

            // Published articles — only select columns needed for sitemap (no heavy content/summary)
            $articles = Article::with('category:id,slug')->where('status', ArticleStatus::Published)
                ->select(['id', 'slug', 'article_category_id', 'updated_at', 'published_at'])
                ->orderByDesc('published_at')
                ->get();

            foreach ($articles as $article) {
                $slugEn = $article->getTranslation('slug', 'en', false);
                $slugId = $article->getTranslation('slug', 'id', false);
                $urlEn = $article->publicUrl('en');
                $urlId = $slugId ? $article->publicUrl('id') : null;
                $lastmod = ($article->updated_at ?? $article->published_at)->toAtomString();

                $urls[] = [
                    'loc' => $urlEn,
                    'lastmod' => $lastmod,
                    'changefreq' => 'monthly',
                    'priority' => '0.8',
                ];

                if ($slugId && $slugId !== $slugEn) {
                    $urls[] = [
                        'loc' => $urlId,
                        'lastmod' => $lastmod,
                        'changefreq' => 'monthly',
                        'priority' => '0.8',
                    ];
                }
            }

            return view('sitemap', compact('urls'))->render();
        });

        return response($xml, 200)
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Serve a dynamic robots.txt so the Sitemap URL always reflects the correct APP_URL.
     */
    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            '',
            '# Block auth pages from indexing',
            'Disallow: /login',
            'Disallow: /logout',
            '',
            '# Block Laravel internals',
            'Disallow: /_ignition/',
            'Disallow: /telescope/',
            '',
            '# Sitemap',
            'Sitemap: '.route('sitemap'),
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
