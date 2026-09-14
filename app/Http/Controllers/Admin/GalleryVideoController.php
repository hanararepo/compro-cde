<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryVideo;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryVideoController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    /**
     * Display a listing of all gallery videos.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', GalleryVideo::class);

        $videos = GalleryVideo::withTrashed()
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = strtolower(trim($request->search));
                $query->where(function ($q) use ($term) {
                    $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.en'))) LIKE ?", ["%{$term}%"])
                      ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.id'))) LIKE ?", ["%{$term}%"])
                      ->orWhere('youtube_url', 'LIKE', "%{$term}%");
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
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.gallery-videos.index', compact('videos'));
    }

    /**
     * Show the form for creating a new gallery video.
     */
    public function create(): View
    {
        $this->authorize('create', GalleryVideo::class);

        return view('admin.gallery-videos.create');
    }

    /**
     * Store a newly created gallery video.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', GalleryVideo::class);

        $validated = $request->validate([
            'title_en'    => ['required', 'string', 'max:255'],
            'title_id'    => ['required', 'string', 'max:255'],
            'youtube_url' => ['required', 'string', 'max:1000'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ]);

        $youtubeId = GalleryVideo::extractYouTubeId($validated['youtube_url']);
        if (! $youtubeId) {
            return back()
                ->withInput()
                ->withErrors(['youtube_url' => 'Invalid YouTube URL or Embed link. Please provide a valid YouTube video link.']);
        }

        $video = GalleryVideo::create([
            'title'       => [
                'en' => $validated['title_en'],
                'id' => $validated['title_id'],
            ],
            'youtube_url' => $validated['youtube_url'],
            'youtube_id'  => $youtubeId,
            'sort_order'  => $validated['sort_order'] ?? 0,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        $label = $validated['title_en'];
        $this->activityLog->log($request->user(), 'created', "Added gallery video '{$label}'.", $video);

        return redirect()
            ->route('admin.gallery-videos.index')
            ->with('success', 'Video berhasil ditambahkan ke galeri.');
    }

    /**
     * Show the form for editing the specified gallery video.
     */
    public function edit(GalleryVideo $galleryVideo): View
    {
        $this->authorize('update', $galleryVideo);

        return view('admin.gallery-videos.edit', compact('galleryVideo'));
    }

    /**
     * Update the specified gallery video.
     */
    public function update(Request $request, GalleryVideo $galleryVideo): RedirectResponse
    {
        $this->authorize('update', $galleryVideo);

        $validated = $request->validate([
            'title_en'    => ['required', 'string', 'max:255'],
            'title_id'    => ['required', 'string', 'max:255'],
            'youtube_url' => ['required', 'string', 'max:1000'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ]);

        $youtubeId = GalleryVideo::extractYouTubeId($validated['youtube_url']);
        if (! $youtubeId) {
            return back()
                ->withInput()
                ->withErrors(['youtube_url' => 'Invalid YouTube URL or Embed link. Please provide a valid YouTube video link.']);
        }

        $galleryVideo->update([
            'title'       => [
                'en' => $validated['title_en'],
                'id' => $validated['title_id'],
            ],
            'youtube_url' => $validated['youtube_url'],
            'youtube_id'  => $youtubeId,
            'sort_order'  => $validated['sort_order'] ?? 0,
            'is_active'   => $request->boolean('is_active'),
        ]);

        $label = $validated['title_en'];
        $this->activityLog->log($request->user(), 'updated', "Updated gallery video '{$label}'.", $galleryVideo);

        return redirect()
            ->route('admin.gallery-videos.index')
            ->with('success', 'Video galeri berhasil diperbarui.');
    }

    /**
     * Toggle video active status.
     */
    public function toggleActive(Request $request, GalleryVideo $galleryVideo): RedirectResponse
    {
        $this->authorize('update', $galleryVideo);

        $galleryVideo->update(['is_active' => ! $galleryVideo->is_active]);

        $status = $galleryVideo->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $label  = $galleryVideo->getTranslation('title', 'en', false) ?: 'Video';
        $this->activityLog->log($request->user(), 'updated', "Gallery video '{$label}' {$status}.", $galleryVideo);

        return back()->with('success', "Video berhasil {$status}.");
    }

    /**
     * Remove the specified gallery video.
     */
    public function destroy(Request $request, GalleryVideo $galleryVideo): RedirectResponse
    {
        $this->authorize('delete', $galleryVideo);

        $label = $galleryVideo->getTranslation('title', 'en', false) ?: 'Untitled Video';
        $this->activityLog->log($request->user(), 'deleted', "Deleted gallery video '{$label}'.", $galleryVideo);

        $galleryVideo->delete();

        return redirect()
            ->route('admin.gallery-videos.index')
            ->with('success', 'Video berhasil dihapus dari galeri.');
    }
}
