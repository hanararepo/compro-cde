<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use App\Models\Gallery;
use App\Models\GalleryVideo;
use App\Models\Setting;
use App\Models\Slider;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the public homepage with active hero slides, videos, articles, and gallery items.
     */
    public function index(): View
    {
        $sliders = Slider::active()->orderBy('id')->get();

        $galleryVideos = GalleryVideo::active()
            ->orderBy('sort_order')
            ->latest()
            ->orderByDesc('id')
            ->get();

        $articleCategories = ArticleCategory::query()
            ->whereIn('slug', ['csr-environment', 'insights-trends'])
            ->get()
            ->keyBy('slug');

        $csrCategory = $articleCategories->get('csr-environment');
        $insightsCategory = $articleCategories->get('insights-trends');

        $featuredArticles = fn (HasMany $query) => $query
            ->with('category:id,slug')
            ->published()
            ->where('is_featured', true)
            ->latest('published_at')
            ->orderByDesc('id');

        $csrCategory?->load(['articles' => fn (HasMany $query) => $featuredArticles($query)
            ->with('author')
            ->limit(3)]);
        $insightsCategory?->load(['articles' => $featuredArticles]);

        $galleries = Gallery::with('category')
            ->published()
            ->latest()
            ->orderByDesc('id')
            ->take(8)
            ->get();

        $settings = Setting::getAllSettings();

        return view('public.home', compact('sliders', 'galleryVideos', 'csrCategory', 'insightsCategory', 'galleries', 'settings'));
    }
}
