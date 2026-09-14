<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request and set application locale based on session.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $adminPrefix = trim((string) config('app.admin_prefix', 'admin'), '/');

        // Admin dashboard is standardized in English
        if ($request->is($adminPrefix.'*')) {
            app()->setLocale('en');

            return $next($request);
        }

        $locale = session('locale', config('app.locale', 'en'));

        if (in_array($locale, ['en', 'id'], true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
