<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Award;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AwardController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    /**
     * Display a listing of awards/certificates with filters.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Award::class);

        $awards = Award::when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->search, function ($query, $search) {
                $term = strtolower(trim($search));
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(title) LIKE ?', ["%{$term}%"])
                      ->orWhereRaw('LOWER(description) LIKE ?', ["%{$term}%"]);
                });
            })
            ->orderBy('sort_order')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.awards.index', compact('awards'));
    }

    /**
     * Show the form for creating a new award/certificate.
     */
    public function create(): View
    {
        $this->authorize('create', Award::class);

        return view('admin.awards.create');
    }

    /**
     * Store a newly created award/certificate.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Award::class);

        $validated = $request->validate([
            'title_en'    => ['required', 'string', 'max:255'],
            'title_id'    => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_id' => ['nullable', 'string'],
            'image'       => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'type'        => ['required', 'in:award,certificate'],
            'issued_date' => ['nullable', 'date'],
            'sort_order'  => ['nullable', 'integer'],
            'is_active'   => ['boolean'],
        ]);

        // Upload image
        $imagePath = $request->file('image')->store('awards', 'public');

        $award = Award::create([
            'title'       => ['en' => $validated['title_en'], 'id' => $validated['title_id'] ?? ''],
            'description' => ['en' => $validated['description_en'] ?? '', 'id' => $validated['description_id'] ?? ''],
            'image_path'  => $imagePath,
            'type'        => $validated['type'],
            'issued_date' => $validated['issued_date'] ?? null,
            'sort_order'  => $validated['sort_order'] ?? 0,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        $label = $award->getTranslation('title', 'en', false) ?: 'Untitled';
        $this->activityLog->log($request->user(), 'created', "Created {$award->type} '{$label}'.", $award);

        return redirect()
            ->route('admin.awards.index')
            ->with('success', ucfirst($award->type) . ' created successfully.');
    }

    /**
     * Show the form for editing the specified award/certificate.
     */
    public function edit(Award $award): View
    {
        $this->authorize('update', $award);

        return view('admin.awards.edit', compact('award'));
    }

    /**
     * Update the specified award/certificate.
     */
    public function update(Request $request, Award $award): RedirectResponse
    {
        $this->authorize('update', $award);

        $validated = $request->validate([
            'title_en'    => ['required', 'string', 'max:255'],
            'title_id'    => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_id' => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'type'        => ['required', 'in:award,certificate'],
            'issued_date' => ['nullable', 'date'],
            'sort_order'  => ['nullable', 'integer'],
            'is_active'   => ['boolean'],
        ]);

        $data = [
            'title'       => ['en' => $validated['title_en'], 'id' => $validated['title_id'] ?? ''],
            'description' => ['en' => $validated['description_en'] ?? '', 'id' => $validated['description_id'] ?? ''],
            'type'        => $validated['type'],
            'issued_date' => $validated['issued_date'] ?? null,
            'sort_order'  => $validated['sort_order'] ?? 0,
            'is_active'   => $request->boolean('is_active', true),
        ];

        // Replace image if a new one is uploaded
        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($award->image_path);
            $data['image_path'] = $request->file('image')->store('awards', 'public');
        }

        $award->update($data);

        $label = $award->fresh()->getTranslation('title', 'en', false) ?: 'Untitled';
        $this->activityLog->log($request->user(), 'updated', "Updated {$award->type} '{$label}'.", $award);

        return redirect()
            ->route('admin.awards.index')
            ->with('success', ucfirst($award->type) . ' updated successfully.');
    }

    /**
     * Remove the specified award/certificate.
     */
    public function destroy(Award $award): RedirectResponse
    {
        $this->authorize('delete', $award);

        $label = $award->getTranslation('title', 'en', false) ?: 'Untitled';
        $type  = $award->type;

        // Delete image from disk
        Storage::disk('public')->delete($award->image_path);

        $this->activityLog->log(auth()->user(), 'deleted', "Deleted {$type} '{$label}'.", $award);

        $award->delete();

        return redirect()
            ->route('admin.awards.index')
            ->with('success', ucfirst($type) . ' deleted successfully.');
    }
}
