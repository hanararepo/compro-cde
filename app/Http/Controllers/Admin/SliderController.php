<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SliderController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    /**
     * Display a listing of all sliders.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Slider::class);

        $sliders = Slider::withTrashed()
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = strtolower(trim($request->search));
                $query->where(function ($q) use ($term) {
                    $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.en'))) LIKE ?", ["%{$term}%"])
                      ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.id'))) LIKE ?", ["%{$term}%"]);
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'active') {
                    $query->whereNull('deleted_at')->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->whereNull('deleted_at')->where('is_active', false);
                } elseif ($request->status === 'trashed') {
                    $query->onlyTrashed();
                }
            })
            ->when(! $request->filled('status'), fn ($q) => $q->whereNull('deleted_at'))
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.sliders.index', compact('sliders'));
    }

    /**
     * Show the form for creating a new slider.
     */
    public function create(): View
    {
        $this->authorize('create', Slider::class);

        return view('admin.sliders.create');
    }

    /**
     * Store a newly created slider.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Slider::class);

        $validated = $request->validate([
            'title_en'      => ['required', 'string', 'max:255'],
            'title_id'      => ['required', 'string', 'max:255'],
            'description_en'=> ['nullable', 'string', 'max:1000'],
            'description_id'=> ['nullable', 'string', 'max:1000'],
            'image_desktop' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'image_mobile'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['boolean'],
        ]);

        $desktopPath = $request->file('image_desktop')->store('sliders/desktop', 'public');

        $mobilePath = null;
        if ($request->hasFile('image_mobile')) {
            $mobilePath = $request->file('image_mobile')->store('sliders/mobile', 'public');
        }

        $slider = Slider::create([
            'title'         => ['en' => $validated['title_en'], 'id' => $validated['title_id']],
            'description'   => [
                'en' => $validated['description_en'] ?? null,
                'id' => $validated['description_id'] ?? null,
            ],
            'image_desktop' => $desktopPath,
            'image_mobile'  => $mobilePath,
            'sort_order'    => $validated['sort_order'] ?? 0,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        $label = $validated['title_en'];
        $this->activityLog->log($request->user(), 'created', "Created slider '{$label}'.", $slider);

        return redirect()
            ->route('admin.sliders.index')
            ->with('success', 'Slider berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified slider.
     */
    public function edit(Slider $slider): View
    {
        $this->authorize('update', $slider);

        return view('admin.sliders.edit', compact('slider'));
    }

    /**
     * Update the specified slider.
     */
    public function update(Request $request, Slider $slider): RedirectResponse
    {
        $this->authorize('update', $slider);

        $validated = $request->validate([
            'title_en'      => ['required', 'string', 'max:255'],
            'title_id'      => ['required', 'string', 'max:255'],
            'description_en'=> ['nullable', 'string', 'max:1000'],
            'description_id'=> ['nullable', 'string', 'max:1000'],
            'image_desktop' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'image_mobile'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['boolean'],
        ]);

        $desktopPath = $slider->image_desktop;
        if ($request->hasFile('image_desktop')) {
            Storage::disk('public')->delete($slider->image_desktop);
            $desktopPath = $request->file('image_desktop')->store('sliders/desktop', 'public');
        }

        $mobilePath = $slider->image_mobile;
        if ($request->hasFile('image_mobile')) {
            if ($slider->image_mobile) {
                Storage::disk('public')->delete($slider->image_mobile);
            }
            $mobilePath = $request->file('image_mobile')->store('sliders/mobile', 'public');
        }

        // Handle remove mobile image checkbox
        if ($request->boolean('remove_mobile_image') && $slider->image_mobile) {
            Storage::disk('public')->delete($slider->image_mobile);
            $mobilePath = null;
        }

        $slider->update([
            'title'         => ['en' => $validated['title_en'], 'id' => $validated['title_id']],
            'description'   => [
                'en' => $validated['description_en'] ?? null,
                'id' => $validated['description_id'] ?? null,
            ],
            'image_desktop' => $desktopPath,
            'image_mobile'  => $mobilePath,
            'sort_order'    => $validated['sort_order'] ?? 0,
            'is_active'     => $request->boolean('is_active'),
        ]);

        $label = $validated['title_en'];
        $this->activityLog->log($request->user(), 'updated', "Updated slider '{$label}'.", $slider);

        return redirect()
            ->route('admin.sliders.index')
            ->with('success', 'Slider berhasil diperbarui.');
    }

    /**
     * Toggle slider active status quickly.
     */
    public function toggleActive(Request $request, Slider $slider): RedirectResponse
    {
        $this->authorize('update', $slider);

        $slider->update(['is_active' => ! $slider->is_active]);

        $status = $slider->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $label  = $slider->getTranslation('title', 'en', false) ?: 'Slider';
        $this->activityLog->log($request->user(), 'updated', "Slider '{$label}' {$status}.", $slider);

        return back()->with('success', "Slider berhasil {$status}.");
    }

    /**
     * Remove the specified slider from storage.
     */
    public function destroy(Request $request, Slider $slider): RedirectResponse
    {
        $this->authorize('delete', $slider);
        $label = $slider->getTranslation('title', 'en', false) ?: 'Untitled';

        Storage::disk('public')->delete(array_filter([
            $slider->image_desktop,
            $slider->image_mobile,
        ]));

        $this->activityLog->log($request->user(), 'deleted', "Deleted slider '{$label}'.", $slider);

        $slider->delete();

        return redirect()
            ->route('admin.sliders.index')
            ->with('success', 'Slider berhasil dihapus.');
    }
}
