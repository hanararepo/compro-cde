<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\ValidRecaptcha;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog
    ) {}

    /**
     * Show the login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Authenticate the user.
     */
    public function login(Request $request): RedirectResponse
    {
        // Build validation rules
        $rules = [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];

        // Only validate reCAPTCHA if the site key is configured
        if (config('recaptcha.site_key')) {
            $tokenField = $request->filled('g-recaptcha-response') ? 'g-recaptcha-response' : 'recaptcha_token';
            $rules[$tokenField] = ['required', 'string', new ValidRecaptcha];
        }

        $credentials = $request->validate($rules);

        $remember = $request->boolean('remember');

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => true], $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            $this->activityLog->log($user, 'logged_in', "User '{$user->name}' logged in.");

            return redirect()->intended(route('admin.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records, or your account is deactivated.'),
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $this->activityLog->log($user, 'logged_out', "User '{$user->name}' logged out.");

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
