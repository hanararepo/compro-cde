<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleTag;
use App\Models\User;
use App\Services\ActivityLog\ActivityLogService;
use App\Services\Article\ArticleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleService $articleService,
        private readonly ActivityLogService $activityLog
    ) {}

    /**
     * Display a listing of articles with full eager loading (N+1 prevention) and live filters.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $canViewOthers = $user->can('articles.view-others') || $user->hasRole('Administrator');

        $articles = Article::with(['category', 'author', 'approvedBy', 'tags'])
            ->when(! $canViewOthers, function ($query) use ($user) {
                // Users without view-others only see their own articles
                $query->where('author_id', $user->id);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->category, function ($query, $category) {
                $query->where('article_category_id', $category);
            })
            ->when($canViewOthers && $request->author, function ($query) use ($request) {
                $query->where('author_id', $request->author);
            })
            ->when($request->search, function ($query, $search) {
                $term = strtolower(trim($search));
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(title) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('LOWER(summary) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('LOWER(content) LIKE ?', ["%{$term}%"]);
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = ArticleCategory::all();
        $authors = $canViewOthers ? User::whereHas('articles')->orderBy('name')->get() : collect();

        return view('admin.articles.index', compact('articles', 'categories', 'authors'));
    }

    /**
     * Display the approval queue for editors and administrators with eager loading and live search.
     */
    public function pending(Request $request): View
    {
        $this->authorize('approve', Article::class);

        $articles = Article::with(['category', 'author', 'tags'])
            ->where('status', ArticleStatus::Pending)
            ->when($request->search, function ($query, $search) {
                $term = strtolower(trim($search));
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(title) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('LOWER(summary) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('LOWER(content) LIKE ?', ["%{$term}%"]);
                });
            })
            ->when($request->category, function ($query, $category) {
                $query->where('article_category_id', $category);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = ArticleCategory::all();

        return view('admin.articles.pending', compact('articles', 'categories'));
    }

    /**
     * Display full public preview of an article for reviewer approval or author check.
     */
    public function preview(Request $request, Article $article): View
    {
        $this->authorize('view', $article);

        $article->loadMissing(['category', 'author', 'approvedBy', 'tags']);

        $relatedArticles = Article::with('category')
            ->where('id', '!=', $article->id)
            ->when($article->article_category_id, function ($query) use ($article) {
                $query->where('article_category_id', $article->article_category_id);
            })
            ->take(3)
            ->get();

        return view('admin.articles.preview', compact('article', 'relatedArticles'));
    }

    /**
     * Show the form for creating a new article.
     */
    public function create(): View
    {
        $this->authorize('create', Article::class);

        $categories = ArticleCategory::all();
        $tags = ArticleTag::all();

        return view('admin.articles.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created article in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Article::class);

        $canApprove = $request->user()->can('articles.approve');
        $isRequestingApproval = $request->input('_action') === 'request_approval';
        // For approvers, ID fields are required when publishing/submitting for approval
        $idFieldsRequired = $canApprove
            ? in_array($request->input('status'), ['pending', 'published'])
            : $isRequestingApproval;

        $validated = $request->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_id' => [$idFieldsRequired ? 'required' : 'nullable', 'string', 'max:255'],
            'content_en' => ['required', 'string'],
            'content_id' => [$idFieldsRequired ? 'required' : 'nullable', 'string'],
            'summary_en' => [$idFieldsRequired ? 'required' : 'nullable', 'string'],
            'summary_id' => [$idFieldsRequired ? 'required' : 'nullable', 'string'],
            'slug_en' => ['nullable', 'string', 'max:255'],
            'slug_id' => ['nullable', 'string', 'max:255'],
            'article_category_id' => [$idFieldsRequired ? 'required' : 'nullable', 'exists:article_categories,id'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'status' => $canApprove
                ? ['required', 'string', 'in:draft,pending,published,unpublished,rejected']
                : ['nullable', 'string'],
            'is_featured' => ['boolean'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:article_tags,id'],
            '_action' => ['nullable', 'string', 'in:request_approval,set_draft'],
        ]);

        $article = $this->articleService->createArticle($validated, $request->user());

        if (! empty($validated['tags'])) {
            $article->tags()->sync($validated['tags']);
        }

        $label = $article->getTranslation('title', 'en', false) ?: 'Untitled';
        $this->activityLog->log($request->user(), 'created', "Created article '{$label}'.", $article);

        $message = $article->status === ArticleStatus::Pending
            ? 'Article submitted for approval successfully.'
            : 'Article created successfully.';

        return redirect()
            ->route('admin.articles.index')
            ->with('success', $message);
    }

    /**
     * Show the form for editing the specified article.
     */
    public function edit(Article $article): View
    {
        $this->authorize('update', $article);

        $article->loadMissing(['tags', 'category', 'author']);

        $categories = ArticleCategory::all();
        $tags = ArticleTag::all();
        $selectedTags = $article->tags->pluck('id')->toArray();

        return view('admin.articles.edit', compact('article', 'categories', 'tags', 'selectedTags'));
    }

    /**
     * Update the specified article in storage.
     */
    public function update(Request $request, Article $article): RedirectResponse
    {
        $this->authorize('update', $article);

        $canApprove = $request->user()->can('articles.approve');
        $isRequestingApproval = $request->input('_action') === 'request_approval';
        // For approvers, ID fields are required when publishing/submitting for approval
        $idFieldsRequired = $canApprove
            ? in_array($request->input('status'), ['pending', 'published'])
            : $isRequestingApproval;

        $validated = $request->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_id' => [$idFieldsRequired ? 'required' : 'nullable', 'string', 'max:255'],
            'content_en' => ['required', 'string'],
            'content_id' => [$idFieldsRequired ? 'required' : 'nullable', 'string'],
            'summary_en' => [$idFieldsRequired ? 'required' : 'nullable', 'string'],
            'summary_id' => [$idFieldsRequired ? 'required' : 'nullable', 'string'],
            'slug_en' => ['nullable', 'string', 'max:255'],
            'slug_id' => ['nullable', 'string', 'max:255'],
            'article_category_id' => [$idFieldsRequired ? 'required' : 'nullable', 'exists:article_categories,id'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'status' => $canApprove
                ? ['required', 'string', 'in:draft,pending,published,unpublished,rejected']
                : ['nullable', 'string'],
            'is_featured' => ['boolean'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:article_tags,id'],
            '_action' => ['nullable', 'string', 'in:request_approval,set_draft,save,unpublish'],
        ]);

        $this->articleService->updateArticle($article, $validated, $request->user());

        $label = $article->fresh()->getTranslation('title', 'en', false) ?: 'Untitled';
        $this->activityLog->log($request->user(), 'updated', "Updated article '{$label}'.", $article);

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article updated successfully.');
    }

    /**
     * Approve a pending article (Admin / Editor).
     */
    public function approve(Request $request, Article $article): RedirectResponse
    {
        $this->authorize('approve', $article);

        $this->articleService->approveArticle($article, $request->user());

        $label = $article->getTranslation('title', 'en', false) ?: 'Untitled';
        $this->activityLog->log($request->user(), 'approved', "Approved article '{$label}'.", $article);

        return back()->with('success', 'Article approved and published successfully.');
    }

    /**
     * Unpublish a published article (Admin / Editor) — removes from public frontend.
     */
    public function unpublish(Request $request, Article $article): RedirectResponse
    {
        $this->authorize('approve', $article);

        $this->articleService->unpublishArticle($article);

        $label = $article->getTranslation('title', 'en', false) ?: 'Untitled';
        $this->activityLog->log($request->user(), 'unpublished', "Unpublished article '{$label}'.", $article);

        return back()->with('success', 'Article unpublished successfully.');
    }

    /**
     * Reject a pending article (Admin / Editor).
     */
    public function reject(Request $request, Article $article): RedirectResponse
    {
        $this->authorize('approve', $article);

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->articleService->rejectArticle($article, $request->user(), $validated['rejection_reason'] ?? '');

        $label = $article->getTranslation('title', 'en', false) ?: 'Untitled';
        $this->activityLog->log($request->user(), 'rejected', "Rejected article '{$label}'.", $article);

        return back()->with('success', 'Article rejected.');
    }

    /**
     * Remove the specified article from storage.
     */
    public function destroy(Article $article): RedirectResponse
    {
        $this->authorize('delete', $article);

        $label = $article->getTranslation('title', 'en', false) ?: 'Untitled';
        $this->activityLog->log(auth()->user(), 'deleted', "Deleted article '{$label}'.", $article);

        $article->delete();

        return redirect()
            ->route('admin.articles.index')
            ->with('success', 'Article deleted successfully.');
    }
}
