<?php

namespace App\Providers;

use App\Http\Middleware\EnsureHasPermission;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Award;
use App\Models\CoalProduct;
use App\Models\ContactMessage;
use App\Models\Gallery;
use App\Models\GalleryVideo;
use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Slider;
use App\Models\User;
use App\Policies\ArticlePolicy;
use App\Policies\AwardPolicy;
use App\Policies\CoalProductPolicy;
use App\Policies\ContactMessagePolicy;
use App\Policies\GalleryPolicy;
use App\Policies\GalleryVideoPolicy;
use App\Policies\JobApplicationPolicy;
use App\Policies\JobPostingPolicy;
use App\Policies\SliderPolicy;
use App\Policies\UserPolicy;
use App\Services\Setting\SettingService;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Redirect authenticated users trying to access guest routes (like login) to dashboard
        RedirectIfAuthenticated::redirectUsing(fn () => route('admin.dashboard'));
        // Enforce strict N+1 prevention in local and testing environments
        Model::preventLazyLoading(! app()->isProduction());

        // Lazily share all site settings to every view — runs once per render, loaded from cache.
        View::composer('*', function ($view) {
            if (! $view->offsetExists('settings')) {
                $view->with('settings', app(SettingService::class)->getAllSettings());
            }
        });

        View::composer('partials.public-header', function ($view) {
            $view->with('navigationCategories', ArticleCategory::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'name', 'slug']));
            $view->with('navigationCoalProducts', CoalProduct::query()
                ->latest('created_at')->latest('id')->get(['id', 'name', 'slug']));
        });

        View::composer('partials.public-company-footer', function ($view) {
            $view->with('footerCoalProducts', CoalProduct::query()
                ->latest('created_at')->latest('id')->get(['id', 'name', 'slug']));
        });

        $this->registerPolicies();
        $this->registerAdminGateBefore();
        $this->registerMiddlewareAliases();
    }

    /**
     * Register model policies.
     */
    private function registerPolicies(): void
    {
        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Award::class, AwardPolicy::class);
        Gate::policy(CoalProduct::class, CoalProductPolicy::class);
        Gate::policy(ContactMessage::class, ContactMessagePolicy::class);
        Gate::policy(Gallery::class, GalleryPolicy::class);
        Gate::policy(GalleryVideo::class, GalleryVideoPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(JobPosting::class, JobPostingPolicy::class);
        Gate::policy(JobApplication::class, JobApplicationPolicy::class);
        Gate::policy(Slider::class, SliderPolicy::class);
    }

    /**
     * Administrators bypass ALL Gate and Policy checks.
     * This is the canonical way to implement "super admin" in Laravel with Spatie.
     */
    private function registerAdminGateBefore(): void
    {
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Administrator')) {
                return true;
            }
        });
    }

    /**
     * Register route middleware aliases used by admin routes.
     */
    private function registerMiddlewareAliases(): void
    {
        $this->app['router']->aliasMiddleware(
            'permission',
            EnsureHasPermission::class
        );
    }
}
