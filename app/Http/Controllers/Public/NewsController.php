<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleTag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        return $this->listing($request);
    }

    public function category(Request $request, ArticleCategory $category): View
    {
        return $this->listing($request, $category);
    }

    private function listing(Request $request, ?ArticleCategory $activeCategory = null): View
    {
        $articles = Article::with(['category', 'author', 'tags'])
            ->published()
            ->when($activeCategory, fn (Builder $query) => $query->where('article_category_id', $activeCategory->id))
            ->when($request->filled('tag'), fn (Builder $query) => $query->whereHas('tags', fn (Builder $tags) => $tags->where('slug', $request->string('tag')->toString())))
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $term = mb_strtolower(trim($request->string('search')->toString()));
                $query->where(function (Builder $text) use ($term) {
                    $text->whereRaw('LOWER(title) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('LOWER(summary) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('LOWER(content) LIKE ?', ["%{$term}%"]);
                });
            })
            ->latest('published_at')->orderByDesc('id')
            ->paginate(9)->appends($request->only(['search', 'tag']));

        $tags = ArticleTag::whereHas('articles', function (Builder $query) use ($activeCategory) {
            $query->published()->when($activeCategory, fn (Builder $articles) => $articles->where('article_category_id', $activeCategory->id));
        })->get();
        $activeTag = $tags->firstWhere('slug', $request->query('tag'));

        return view('public.news.index', compact('articles', 'activeCategory', 'tags', 'activeTag'));
    }

    public function show(ArticleCategory $category, string $slug): View
    {
        $article = $this->articleQuery($slug)->where('article_category_id', $category->id)->firstOrFail();

        return $this->detail($article);
    }

    public function uncategorized(string $slug): View|RedirectResponse
    {
        $article = $this->articleQuery($slug)->firstOrFail();
        if ($article->category) {
            return redirect()->to($article->publicUrl(), 301);
        }

        return $this->detail($article);
    }

    private function articleQuery(string $slug): Builder
    {
        return Article::with(['category', 'author', 'tags'])->published()
            ->where(fn (Builder $query) => $query->where('slug->en', $slug)->orWhere('slug->id', $slug));
    }

    private function detail(Article $article): View
    {
        $article->increment('views_count');
        $relatedArticles = Article::with('category')->published()
            ->where('id', '!=', $article->id)
            ->where('article_category_id', $article->article_category_id)
            ->latest('published_at')->orderByDesc('id')->take(3)->get();

        return view('public.news.show', compact('article', 'relatedArticles'));
    }
}
