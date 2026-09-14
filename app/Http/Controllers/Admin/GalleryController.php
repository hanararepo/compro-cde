<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GalleryStatus;
use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Models\User;
use App\Services\ActivityLog\ActivityLogService;
use App\Services\Gallery\GalleryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function __construct(
        private readonly GalleryService $galleryService,
        private readonly ActivityLogService $activityLog
    ) {}

    /**
     * Display a listing of gallery images with eager loading, search, and category/status filters.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $canViewOthers = $user->can('galleries.view-others') || $user->hasRole('Administrator');

        $galleries = Gallery::with(['category', 'uploader'])
            ->when(! $canViewOthers, function ($query) use ($user) {
                // Users without view-others only see their own uploads
                $query->where('uploaded_by', $user->id);
            })
            ->when($request->category, function ($query, $category) {
                $query->where('gallery_category_id', $category);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($canViewOthers && $request->author, function ($query) use ($request) {
                $query->where('uploaded_by', $request->author);
            })
            ->when($request->filled('is_active'), function ($query) use ($request) {
                $query->where('is_active', $request->boolean('is_active'));
            })
            ->when($request->search, function ($query, $search) {
                $term = strtolower(trim($search));
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(title) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('LOWER(description) LIKE ?', ["%{$term}%"]);
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = GalleryCategory::all();
        $authors = $canViewOthers ? User::whereHas('galleries')->orderBy('name')->get() : collect();

        return view('admin.galleries.index', compact('galleries', 'categories', 'authors'));
    }

    /**
     * Display the gallery approval queue for reviewers (Admins / Editors).
     */
    public function pending(Request $request): View
    {
        $this->authorize('approve', Gallery::class);

        $galleries = Gallery::with(['category', 'uploader'])
            ->where('status', GalleryStatus::Pending)
            ->when($request->search, function ($query, $search) {
                $term = strtolower(trim($search));
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(title) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('LOWER(description) LIKE ?', ["%{$term}%"]);
                });
            })
            ->when($request->category, function ($query, $category) {
                $query->where('gallery_category_id', $category);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = GalleryCategory::all();

        return view('admin.galleries.pending', compact('galleries', 'categories'));
    }

    /**
     * Show the form for creating a new gallery item.
     */
    public function create(): View
    {
        $this->authorize('create', Gallery::class);

        $categories = GalleryCategory::all();

        return view('admin.galleries.create', compact('categories'));
    }

    /**
     * Store a newly created gallery image in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Gallery::class);

        $isRequestingApproval = $request->input('_action') === 'request_approval';

        $validated = $request->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_id' => [$isRequestingApproval ? 'required' : 'nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_id' => ['nullable', 'string'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'gallery_category_id' => ['nullable', 'exists:gallery_categories,id'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'status' => ['nullable', 'string'],
            '_action' => ['nullable', 'string', 'in:request_approval,set_draft'],
        ]);

        $gallery = $this->galleryService->createGallery($validated, $request->user());

        $label = $gallery->getTranslation('title', 'en', false) ?: 'Untitled';
        $this->activityLog->log($request->user(), 'created', "Uploaded gallery item '{$label}'.", $gallery);

        $message = $gallery->status === GalleryStatus::Pending
            ? 'Gallery item submitted for approval successfully.'
            : 'Gallery item uploaded successfully.';

        return redirect()
            ->route('admin.galleries.index')
            ->with('success', $message);
    }

    /**
     * Show the form for editing the specified gallery item.
     */
    public function edit(Gallery $gallery): View
    {
        $this->authorize('update', $gallery);

        $gallery->loadMissing(['category', 'uploader']);

        $categories = GalleryCategory::all();

        return view('admin.galleries.edit', compact('gallery', 'categories'));
    }

    /**
     * Update the specified gallery item in storage.
     */
    public function update(Request $request, Gallery $gallery): RedirectResponse
    {
        $this->authorize('update', $gallery);

        $isRequestingApproval = $request->input('_action') === 'request_approval';

        $validated = $request->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_id' => [$isRequestingApproval ? 'required' : 'nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_id' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'gallery_category_id' => ['nullable', 'exists:gallery_categories,id'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'status' => ['nullable', 'string'],
            '_action' => ['nullable', 'string', 'in:request_approval,set_draft,save'],
        ]);

        $this->galleryService->updateGallery($gallery, $validated, $request->user());

        $label = $gallery->fresh()->getTranslation('title', 'en', false) ?: 'Untitled';
        $this->activityLog->log($request->user(), 'updated', "Updated gallery item '{$label}'.", $gallery);

        return redirect()
            ->route('admin.galleries.index')
            ->with('success', 'Gallery item updated successfully.');
    }

    /**
     * Approve a pending gallery item (Admin / Editor).
     */
    public function approve(Request $request, Gallery $gallery): RedirectResponse
    {
        $this->authorize('approve', $gallery);

        $this->galleryService->approveGallery($gallery, $request->user());

        $label = $gallery->getTranslation('title', 'en', false) ?: 'Untitled';
        $this->activityLog->log($request->user(), 'approved', "Approved gallery item '{$label}'.", $gallery);

        return back()->with('success', 'Gallery item approved and published successfully.');
    }

    /**
     * Reject a pending gallery item (Admin / Editor).
     */
    public function reject(Request $request, Gallery $gallery): RedirectResponse
    {
        $this->authorize('approve', $gallery);

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->galleryService->rejectGallery($gallery, $request->user(), $validated['rejection_reason'] ?? '');

        $label = $gallery->getTranslation('title', 'en', false) ?: 'Untitled';
        $this->activityLog->log($request->user(), 'rejected', "Rejected gallery item '{$label}'.", $gallery);

        return back()->with('success', 'Gallery item rejected.');
    }

    /**
     * Remove the specified gallery item from storage.
     */
    public function destroy(Gallery $gallery): RedirectResponse
    {
        $this->authorize('delete', $gallery);

        $label = $gallery->getTranslation('title', 'en', false) ?: 'Untitled';
        $this->activityLog->log(auth()->user(), 'deleted', "Deleted gallery item '{$label}'.", $gallery);

        $this->galleryService->deleteGallery($gallery);

        return redirect()
            ->route('admin.galleries.index')
            ->with('success', 'Gallery item deleted successfully.');
    }
}
