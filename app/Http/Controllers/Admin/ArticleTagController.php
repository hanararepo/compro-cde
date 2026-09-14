<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleTag;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleTagController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    /**
     * Display a listing of tags with live search.
     */
    public function index(Request $request): View
    {
        $tags = ArticleTag::withCount('articles')
            ->when($request->search, function ($query, $search) {
                $term = strtolower(trim($search));
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                        ->orWhere('slug', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.articles.tags.index', compact('tags'));
    }

    /**
     * Show the form for creating a new tag.
     */
    public function create(): View
    {
        return view('admin.articles.tags.create');
    }

    /**
     * Store a newly created tag.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'name_en' => ['nullable', 'string', 'max:100'],
            'name_id' => ['nullable', 'string', 'max:100'],
        ]);

        // AJAX call from article form (sends 'name' only)
        if ($request->expectsJson()) {
            $name = trim($validated['name']);
            $slug = Str::slug($name);

            $tag = ArticleTag::where('slug', $slug)->first();

            if (! $tag) {
                $tag = ArticleTag::create([
                    'name' => ['en' => $name, 'id' => $name],
                    'slug' => $slug,
                ]);
            }

            return response()->json([
                'id' => $tag->id,
                'name' => $tag->getTranslation('name', 'en'),
            ]);
        }

        // Regular form submission from CRUD
        $nameEn = trim($validated['name_en'] ?? $validated['name']);
        $nameId = trim($validated['name_id'] ?? $validated['name']);
        $slug = Str::slug($nameEn);

        ArticleTag::create([
            'name' => ['en' => $nameEn, 'id' => $nameId],
            'slug' => $slug,
        ]);

        $this->activityLog->log(auth()->user(), 'created', "Created article tag '{$nameEn}'.");

        return redirect()
            ->route('admin.article-tags.index')
            ->with('success', 'Tag created successfully.');
    }

    /**
     * Show the form for editing the specified tag.
     */
    public function edit(ArticleTag $articleTag): View
    {
        return view('admin.articles.tags.edit', compact('articleTag'));
    }

    /**
     * Update the specified tag.
     */
    public function update(Request $request, ArticleTag $articleTag): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:100'],
            'name_id' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'unique:article_tags,slug,'.$articleTag->id],
        ]);

        $articleTag->update([
            'name' => ['en' => $validated['name_en'], 'id' => $validated['name_id']],
            'slug' => Str::slug($validated['slug']),
        ]);

        $this->activityLog->log(auth()->user(), 'updated', "Updated article tag '{$validated['name_en']}'.", $articleTag);

        return redirect()
            ->route('admin.article-tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    /**
     * Remove the specified tag from storage.
     */
    public function destroy(ArticleTag $articleTag): RedirectResponse
    {
        $name = $articleTag->getTranslation('name', 'en', false) ?: 'Unnamed';
        $articleTag->delete();

        $this->activityLog->log(auth()->user(), 'deleted', "Deleted article tag '{$name}'.");

        return redirect()
            ->route('admin.article-tags.index')
            ->with('success', 'Tag deleted successfully.');
    }
}
