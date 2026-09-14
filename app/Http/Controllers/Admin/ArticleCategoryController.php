<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleCategory;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleCategoryController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    /**
     * Display a listing of the article categories with eager loading and live search.
     */
    public function index(Request $request): View
    {
        $categories = ArticleCategory::with('parent')
            ->withCount('articles')
            ->when($request->search, function ($query, $search) {
                $term = strtolower(trim($search));
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                        ->orWhere('slug', 'like', "%{$term}%");
                });
            })
            ->orderBy('sort_order')
            ->paginate(10)
            ->withQueryString();

        return view('admin.articles.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new article category.
     */
    public function create(): View
    {
        $parentCategories = ArticleCategory::roots()->get();

        return view('admin.articles.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_id' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:article_categories,slug'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:article_categories,id'],
            'color' => ['nullable', 'string', 'max:7'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name_en']);

        ArticleCategory::create([
            'name' => [
                'en' => $validated['name_en'],
                'id' => $validated['name_id'],
            ],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'color' => $validated['color'] ?? '#6366f1',
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $this->activityLog->log(auth()->user(), 'created', "Created article category '{$validated['name_en']}'.");

        return redirect()
            ->route('admin.article-categories.index')
            ->with('success', 'Article category created successfully.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(ArticleCategory $articleCategory): View
    {
        $parentCategories = ArticleCategory::roots()
            ->where('id', '!=', $articleCategory->id)
            ->get();

        return view('admin.articles.categories.edit', compact('articleCategory', 'parentCategories'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, ArticleCategory $articleCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_id' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:article_categories,slug,'.$articleCategory->id],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:article_categories,id'],
            'color' => ['nullable', 'string', 'max:7'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $articleCategory->update([
            'name' => [
                'en' => $validated['name_en'],
                'id' => $validated['name_id'],
            ],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'color' => $validated['color'] ?? '#6366f1',
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $this->activityLog->log(auth()->user(), 'updated', "Updated article category '{$validated['name_en']}'.", $articleCategory);

        return redirect()
            ->route('admin.article-categories.index')
            ->with('success', 'Article category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(ArticleCategory $articleCategory): RedirectResponse
    {
        if ($articleCategory->articles()->count() > 0) {
            return back()->with('error', 'Cannot delete category because it contains articles.');
        }

        $name = $articleCategory->getTranslation('name', 'en', false) ?: 'Unnamed';
        $articleCategory->delete();

        $this->activityLog->log(auth()->user(), 'deleted', "Deleted article category '{$name}'.");

        return redirect()
            ->route('admin.article-categories.index')
            ->with('success', 'Article category deleted successfully.');
    }
}
