<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    // Keep existing bookmarks working while all public links use the News URLs.
    public function index(Request $request): RedirectResponse
    {
        $filters = $request->only(['search', 'tag', 'page']);
        if ($request->filled('category')) {
            $category = ArticleCategory::where('slug', $request->string('category')->toString())->firstOrFail();

            return redirect()->route('news.category', ['category' => $category->slug] + $filters, 301);
        }

        return redirect()->route('news.index', $filters, 301);
    }

    public function show(string $slug): RedirectResponse
    {
        $article = Article::with('category')->published()
            ->where(fn ($query) => $query->where('slug->en', $slug)->orWhere('slug->id', $slug))
            ->firstOrFail();

        return redirect()->to($article->publicUrl(), 301);
    }
}
