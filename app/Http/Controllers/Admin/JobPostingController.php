<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Services\ActivityLog\ActivityLogService;
use App\Services\Careers\DeleteJobApplications;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobPostingController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    // ─────────────────────────────────────────
    // Job Postings CRUD
    // ─────────────────────────────────────────

    public function index(Request $request): View
    {
        $this->authorize('viewAny', JobPosting::class);

        $jobs = JobPosting::withCount('applications')
            ->when($request->search, function ($query, $search) {
                $term = strtolower(trim($search));
                $query->whereRaw('LOWER(title) LIKE ?', ["%{$term}%"]);
            })
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when(in_array($request->input('recruitment_status'), ['0', '1'], true), fn ($query) => $query->where('is_closed', $request->input('recruitment_status') === '1'))
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('is_active', $request->status === '1');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.careers.index', compact('jobs'));
    }

    public function create(): View
    {
        $this->authorize('create', JobPosting::class);

        return view('admin.careers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', JobPosting::class);

        $validated = $request->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_id' => ['required', 'string', 'max:255'],
            'description_en' => ['required', 'string'],
            'description_id' => ['required', 'string'],
            'type' => ['required', 'string', 'in:full_time,part_time'],
            'is_active' => ['boolean'],
            'is_closed' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('careers', 'public');
        }

        $slugEn = JobPosting::generateUniqueSlug($validated['title_en'], 'en');
        $slugId = JobPosting::generateUniqueSlug($validated['title_id'] ?: $validated['title_en'], 'id');

        $job = JobPosting::create([
            'title' => ['en' => $validated['title_en'], 'id' => $validated['title_id']],
            'slug' => ['en' => $slugEn, 'id' => $slugId],
            'description' => ['en' => $validated['description_en'], 'id' => $validated['description_id']],
            'type' => $validated['type'],
            'is_active' => $request->boolean('is_active', true),
            'is_closed' => $request->boolean('is_closed'),
            'image' => $imagePath,
        ]);

        $label = $validated['title_en'];
        $this->activityLog->log($request->user(), 'created', "Created job posting '{$label}'.", $job);

        return redirect()
            ->route('admin.careers.index')
            ->with('success', 'Job posting created successfully.');
    }

    public function edit(JobPosting $career): View
    {
        $this->authorize('update', $career);

        return view('admin.careers.edit', compact('career'));
    }

    public function update(Request $request, JobPosting $career): RedirectResponse
    {
        $this->authorize('update', $career);

        $validated = $request->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_id' => ['required', 'string', 'max:255'],
            'description_en' => ['required', 'string'],
            'description_id' => ['required', 'string'],
            'type' => ['required', 'string', 'in:full_time,part_time'],
            'is_active' => ['boolean'],
            'is_closed' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $imagePath = $career->image;
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($career->image) {
                Storage::disk('public')->delete($career->image);
            }
            $imagePath = $request->file('image')->store('careers', 'public');
        }

        $slugEn = JobPosting::generateUniqueSlug($validated['title_en'], 'en', $career->id);
        $slugId = JobPosting::generateUniqueSlug($validated['title_id'] ?: $validated['title_en'], 'id', $career->id);

        $career->update([
            'title' => ['en' => $validated['title_en'], 'id' => $validated['title_id']],
            'slug' => ['en' => $slugEn, 'id' => $slugId],
            'description' => ['en' => $validated['description_en'], 'id' => $validated['description_id']],
            'type' => $validated['type'],
            'is_active' => $request->boolean('is_active', true),
            'is_closed' => $request->boolean('is_closed', $career->is_closed),
            'image' => $imagePath,
        ]);

        $label = $validated['title_en'];
        $this->activityLog->log($request->user(), 'updated', "Updated job posting '{$label}'.", $career);

        return redirect()
            ->route('admin.careers.index')
            ->with('success', 'Job posting updated successfully.');
    }

    public function destroy(Request $request, JobPosting $career): RedirectResponse
    {
        $this->authorize('delete', $career);

        $label = $career->getTranslation('title', 'en', false) ?: 'Untitled';

        // Delete image from public storage
        if ($career->image) {
            Storage::disk('public')->delete($career->image);
        }

        $this->activityLog->log($request->user(), 'deleted', "Deleted job posting '{$label}'.", $career);

        $career->delete();

        return redirect()
            ->route('admin.careers.index')
            ->with('success', 'Job posting deleted successfully.');
    }

    // ─────────────────────────────────────────
    // Applications Management
    // ─────────────────────────────────────────

    public function applications(Request $request, JobPosting $career): View
    {
        $this->authorize('viewAny', JobApplication::class);

        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['sent', 'failed', 'pending', 'legacy'])],
        ]);
        $statusCounts = $career->applications()->selectRaw('email_status, COUNT(*) AS total')
            ->groupBy('email_status')->pluck('total', 'email_status');
        $stats = [
            'total' => $statusCounts->sum(),
            'sent' => $statusCounts->get('sent', 0),
            'failed' => $statusCounts->get('failed', 0),
            'pending' => $statusCounts->get('pending', 0) + $statusCounts->get('legacy', 0),
        ];

        $applications = $career->applications()
            ->when($request->search, function ($query, $search) {
                $term = strtolower(trim($search));
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('LOWER(email) LIKE ?', ["%{$term}%"]);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('email_status', $request->string('status')->toString()))
            ->latest()->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.careers.applications.index', compact('career', 'applications', 'stats'));
    }

    public function destroyApplication(Request $request, JobPosting $career, JobApplication $application, DeleteJobApplications $deletion): RedirectResponse
    {
        $this->authorize('delete', $application);
        abort_unless($application->job_posting_id === $career->id, 404);

        return $this->deletionResponse($deletion->delete([$application->id], $career->id, $request->user()));
    }

    public function bulkDestroyApplications(Request $request, JobPosting $career, DeleteJobApplications $deletion): RedirectResponse
    {
        $this->authorize('career-applications.delete');
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'integer', 'distinct', Rule::exists('job_applications', 'id')->where('job_posting_id', $career->id)],
        ]);
        $applications = $career->applications()->whereIn('id', $validated['ids'])->get();
        foreach ($applications as $application) {
            $this->authorize('delete', $application);
        }

        return $this->deletionResponse($deletion->delete($validated['ids'], $career->id, $request->user()));
    }

    private function deletionResponse(array $result): RedirectResponse
    {
        if ($result['failed']) {
            return back()->with('error', "Deleted: {$result['deleted']}. Could not delete: {$result['failed']}. The remaining applications are retained. Please try again.");
        }

        return back()->with('success', $result['deleted'].' '.Str::plural('application', $result['deleted']).' deleted successfully.');
    }

    /**
     * Securely stream-download the applicant's CV.
     * The file lives in the private disk (outside public/) and is never accessible via URL.
     */
    public function downloadCv(JobPosting $career, JobApplication $application): StreamedResponse
    {
        $this->authorize('viewAny', JobApplication::class);
        abort_unless($application->job_posting_id === $career->id, 404);

        abort_unless(
            $application->cv_path && $application->email_status !== 'sent' && Storage::disk('local')->exists($application->cv_path),
            404,
            'CV file not found.'
        );

        $ext = pathinfo($application->cv_original_name, PATHINFO_EXTENSION);
        $safe = Str::slug(pathinfo($application->cv_original_name, PATHINFO_FILENAME));
        $downloadName = "{$safe}.{$ext}";

        return Storage::disk('local')->download(
            $application->cv_path,
            $downloadName
        );
    }
}
