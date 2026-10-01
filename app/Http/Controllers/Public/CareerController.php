<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Rules\ValidRecaptcha;
use App\Services\Careers\DeliverJobApplication;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
    public function apply(Request $request, string $slug, DeliverJobApplication $delivery): RedirectResponse
    {
        $career = JobPosting::where('is_active', true)
            ->where(function ($query) use ($slug) {
                $query->where('slug->en', $slug)
                    ->orWhere('slug->id', $slug)
                    ->orWhere('id', $slug);
            })
            ->firstOrFail();

        if ($career->is_closed) {
            throw ValidationException::withMessages(['application' => __('This position is closed and no longer accepts applications.')]);
        }

        if (is_string($request->input('email'))) {
            $request->merge(['email' => mb_strtolower(trim($request->input('email')))]);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('job_applications', 'email')->where('job_posting_id', $career->id)],
            'phone' => ['required', 'string', 'max:30'],
            // Validate MIME type from the actual file content (not only extension)
            'cv' => [
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

        $validated = $request->validate($rules, [
            'email.unique' => __('You have already applied for this position with this email. You may still apply for other positions.'),
        ]);

        // ── Secure CV Upload ──────────────────────────────────────
        // 1. Randomise filename — UUID so the path is not guessable
        // 2. Store in the LOCAL (private) disk, outside public/
        // 3. The path is NEVER exposed; download only via admin auth route
        $file = $request->file('cv');
        $ext = $file->getClientOriginalExtension();
        $storedName = Str::uuid().'.'.strtolower($ext);
        $storedPath = $file->storeAs('cv-temporary', $storedName, 'local');
        if (! $storedPath) {
            return back()->withInput($request->except(['cv', 'recaptcha_token']))
                ->withErrors(['cv' => __('The CV could not be uploaded. Please try again.')]);
        }

        try {
            $application = DB::transaction(function () use ($career, $validated, $file, $storedPath, $request) {
                // Re-check under a row lock in case an admin closed the job during upload.
                $currentCareer = JobPosting::lockForUpdate()->findOrFail($career->id);
                if (! $currentCareer->is_active || $currentCareer->is_closed) {
                    throw ValidationException::withMessages(['application' => __('This position is closed and no longer accepts applications.')]);
                }

                // Another request may have submitted this email after the initial validation.
                if ($currentCareer->applications()->where('email', $validated['email'])->exists()) {
                    throw ValidationException::withMessages(['email' => __('You have already applied for this position with this email. You may still apply for other positions.')]);
                }

                return JobApplication::create([
                    'job_posting_id' => $career->id,
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'cv_path' => $storedPath,
                    'cv_original_name' => $file->getClientOriginalName(),
                    'ip_address' => $request->ip(),
                    'email_status' => 'pending',
                ]);
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($storedPath);
            if ($exception instanceof UniqueConstraintViolationException
                && JobApplication::where('job_posting_id', $career->id)->where('email', $validated['email'])->exists()) {
                throw ValidationException::withMessages(['email' => __('You have already applied for this position with this email. You may still apply for other positions.')]);
            }
            throw $exception;
        }

        try {
            $sent = $delivery->send($application->id);
        } catch (\Throwable $exception) {
            report($exception);
            $sent = false;
        }

        return redirect()
            ->route('careers.show', $career->getSlug())
            ->with('success', $sent
                ? __('Your application has been submitted successfully. We will review it and get back to you soon!')
                : __('Your application has been submitted successfully. We will review it and get back to you soon!'));
    }
}
