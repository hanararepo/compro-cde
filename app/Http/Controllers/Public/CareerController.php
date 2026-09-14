<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Rules\ValidRecaptcha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CareerController extends Controller
{
    /**
     * Display the public list of active job postings.
     */
    public function index(): View
    {
        $jobs = JobPosting::active()
            ->withCount('applications')
            ->latest()
            ->paginate(9);

        return view('public.careers.index', compact('jobs'));
    }

    /**
     * Show a single job posting with the application form.
     */
    public function show(string $slug): View
    {
        $career = JobPosting::where('is_active', true)
            ->where(function ($query) use ($slug) {
                $query->where('slug->en', $slug)
                    ->orWhere('slug->id', $slug)
                    ->orWhere('id', $slug);
            })
            ->firstOrFail();

        return view('public.careers.show', compact('career'));
    }

    /**
     * Handle a public job application with secure CV upload.
     */
    public function apply(Request $request, string $slug): RedirectResponse
    {
        $career = JobPosting::where('is_active', true)
            ->where(function ($query) use ($slug) {
                $query->where('slug->en', $slug)
                    ->orWhere('slug->id', $slug)
                    ->orWhere('id', $slug);
            })
            ->firstOrFail();

        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255'],
            'phone'    => ['required', 'string', 'max:30'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            // Validate MIME type from the actual file content (not only extension)
            'cv'       => [
                'required',
                'file',
                'mimes:pdf,doc,docx',
                'max:5120', // 5 MB
            ],
        ];

        // Add reCAPTCHA v3 validation only when a site key is configured.
        if (config('recaptcha.site_key')) {
            $rules['recaptcha_token'] = ['required', 'string', new ValidRecaptcha];
        }

        $validated = $request->validate($rules);

        // ── Secure CV Upload ──────────────────────────────────────
        // 1. Randomise filename — UUID so the path is not guessable
        // 2. Store in the LOCAL (private) disk, outside public/
        // 3. The path is NEVER exposed; download only via admin auth route
        $file        = $request->file('cv');
        $ext         = $file->getClientOriginalExtension();
        $storedName  = Str::uuid() . '.' . strtolower($ext);
        $storedPath  = $file->storeAs('cv-uploads', $storedName, 'local');

        JobApplication::create([
            'job_posting_id'  => $career->id,
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'phone'           => $validated['phone'],
            'linkedin'        => $validated['linkedin'] ?? null,
            'cv_path'         => $storedPath,
            'cv_original_name'=> $file->getClientOriginalName(),
            'ip_address'      => $request->ip(),
        ]);

        return redirect()
            ->route('careers.show', $career->getSlug())
            ->with('success', __('Your application has been submitted successfully. We will review it and get back to you soon!'));
    }
}
