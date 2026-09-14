<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasPermission
{
    /**
     * Verify that the authenticated user holds the given Spatie permission.
     * Administrators are bypassed entirely via the Gate::before() hook
     * registered in AppServiceProvider.
     *
     * Usage in routes:
     *   Route::middleware('permission:articles.create')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()?->can($permission)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
