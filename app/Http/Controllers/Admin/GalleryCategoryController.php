<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryCategory;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GalleryCategoryController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    /**
     * Display a listing of gallery categories with search and eager counted galleries.
     */
    public function index(Request $request): View
    {
        $categories = GalleryCategory::withCount('galleries')
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

        return view('admin.galleries.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new gallery category.
     */
    public function create(): View
    {
        return view('admin.galleries.categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_id' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:gallery_categories,slug'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name_en']);

        GalleryCategory::create([
            'name' => [
                'en' => $validated['name_en'],
                'id' => $validated['name_id'],
            ],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $this->activityLog->log(auth()->user(), 'created', "Created gallery category '{$validated['name_en']}'.");

        return redirect()
            ->route('admin.gallery-categories.index')
            ->with('success', 'Gallery category created successfully.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(GalleryCategory $galleryCategory): View
    {
        return view('admin.galleries.categories.edit', compact('galleryCategory'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, GalleryCategory $galleryCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_id' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:gallery_categories,slug,'.$galleryCategory->id],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $galleryCategory->update([
            'name' => [
                'en' => $validated['name_en'],
                'id' => $validated['name_id'],
            ],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $this->activityLog->log(auth()->user(), 'updated', "Updated gallery category '{$validated['name_en']}'.", $galleryCategory);

        return redirect()
            ->route('admin.gallery-categories.index')
            ->with('success', 'Gallery category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(GalleryCategory $galleryCategory): RedirectResponse
    {
        if ($galleryCategory->galleries()->count() > 0) {
            return back()->with('error', 'Cannot delete category because it contains gallery items.');
        }

        $name = $galleryCategory->getTranslation('name', 'en', false) ?: 'Unnamed';
        $galleryCategory->delete();

        $this->activityLog->log(auth()->user(), 'deleted', "Deleted gallery category '{$name}'.");

        return redirect()
            ->route('admin.gallery-categories.index')
            ->with('success', 'Gallery category deleted successfully.');
    }
}
