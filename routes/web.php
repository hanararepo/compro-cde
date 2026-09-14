<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ArticleCategoryController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\ArticleTagController;
use App\Http\Controllers\Admin\AwardController as AdminAwardController;
use App\Http\Controllers\Admin\CoalProductController as AdminCoalProductController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GalleryCategoryController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\GalleryVideoController;
use App\Http\Controllers\Admin\JobPostingController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\ArticleController as PublicArticleController;
use App\Http\Controllers\Public\AwardController as PublicAwardController;
use App\Http\Controllers\Public\CareerController;
use App\Http\Controllers\Public\CoalProductController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::view('/about-us/introduction', 'public.about.introduction')->name('about.introduction');
Route::view('/about-us/vision-mission', 'public.about.vision-mission')->name('about.vision-mission');
Route::view('/about-us/corporate-logo', 'public.about.corporate-logo')->name('about.corporate-logo');
Route::get('/about-us/photo-gallery', [AboutController::class, 'photoGallery'])->name('about.photo-gallery');
Route::get('/about-us/awards-certificates', [PublicAwardController::class, 'index'])->name('about.awards-certificates');
Route::get('/articles', [PublicArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [PublicArticleController::class, 'show'])->name('articles.show');
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [NewsController::class, 'uncategorized'])->name('news.uncategorized');
Route::get('/coal-products', [CoalProductController::class, 'index'])->name('coal-products.index');
Route::get('/coal-products/{coalProduct:slug}', [CoalProductController::class, 'show'])->name('coal-products.show');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/careers', [CareerController::class, 'index'])->name('careers.index');
Route::get('/careers/{slug}', [CareerController::class, 'show'])->name('careers.show');
Route::post('/careers/{slug}/apply', [CareerController::class, 'apply'])->middleware('throttle:3,1')->name('careers.apply');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/locale/{lang}', [LocaleController::class, 'switch'])->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Authentication Routes (Dynamic Login Path via .env)
|--------------------------------------------------------------------------
*/

$loginPath = trim((string) config('app.admin_login_path', 'hanara-portal'), '/');
$adminPrefix = trim((string) config('app.admin_prefix', 'admin'), '/');

Route::middleware('guest')->group(function () use ($loginPath) {
    Route::get($loginPath, [AuthController::class, 'showLoginForm'])->name('login');
    Route::post($loginPath, [AuthController::class, 'login']);
    if ($loginPath !== 'login') {
        Route::get('login', [AuthController::class, 'showLoginForm']);
        Route::post('login', [AuthController::class, 'login']);
    }
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Management Routes (Dynamic Prefix via .env, Protected by auth)
|--------------------------------------------------------------------------
*/

Route::prefix($adminPrefix)
    ->name('admin.')
    ->middleware(['auth'])
    ->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Awards & Certificates
        Route::get('awards', [AdminAwardController::class, 'index'])
            ->middleware('permission:awards.view')->name('awards.index');
        Route::middleware('permission:awards.create')->group(function () {
            Route::get('awards/create', [AdminAwardController::class, 'create'])->name('awards.create');
            Route::post('awards', [AdminAwardController::class, 'store'])->name('awards.store');
        });
        Route::middleware('permission:awards.edit')->group(function () {
            Route::get('awards/{award}/edit', [AdminAwardController::class, 'edit'])->name('awards.edit');
            Route::put('awards/{award}', [AdminAwardController::class, 'update'])->name('awards.update');
            Route::patch('awards/{award}', [AdminAwardController::class, 'update']);
        });
        Route::delete('awards/{award}', [AdminAwardController::class, 'destroy'])
            ->middleware('permission:awards.delete')->name('awards.destroy');

        // Coal Products
        Route::get('coal-products', [AdminCoalProductController::class, 'index'])
            ->middleware('permission:coal-products.view')->name('coal-products.index');
        Route::middleware('permission:coal-products.create')->group(function () {
            Route::get('coal-products/create', [AdminCoalProductController::class, 'create'])->name('coal-products.create');
            Route::post('coal-products', [AdminCoalProductController::class, 'store'])->name('coal-products.store');
        });
        Route::middleware('permission:coal-products.edit')->group(function () {
            Route::get('coal-products/{coalProduct}/edit', [AdminCoalProductController::class, 'edit'])->name('coal-products.edit');
            Route::put('coal-products/{coalProduct}', [AdminCoalProductController::class, 'update'])->name('coal-products.update');
        });
        Route::delete('coal-products/{coalProduct}', [AdminCoalProductController::class, 'destroy'])
            ->middleware('permission:coal-products.delete')->name('coal-products.destroy');

        // Hero Sliders
        Route::middleware('permission:sliders.view')->group(function () {
            Route::get('sliders', [SliderController::class, 'index'])->name('sliders.index');
        });
        Route::middleware('permission:sliders.create')->group(function () {
            Route::get('sliders/create', [SliderController::class, 'create'])->name('sliders.create');
            Route::post('sliders', [SliderController::class, 'store'])->name('sliders.store');
        });
        Route::middleware('permission:sliders.edit')->group(function () {
            Route::get('sliders/{slider}/edit', [SliderController::class, 'edit'])->name('sliders.edit');
            Route::put('sliders/{slider}', [SliderController::class, 'update'])->name('sliders.update');
            Route::patch('sliders/{slider}/toggle-active', [SliderController::class, 'toggleActive'])->name('sliders.toggle-active');
        });
        Route::delete('sliders/{slider}', [SliderController::class, 'destroy'])
            ->middleware('permission:sliders.delete')
            ->name('sliders.destroy');

        // Global Settings
        Route::middleware('permission:settings.view')->group(function () {
            Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            Route::put('settings', [SettingController::class, 'update'])
                ->middleware('permission:settings.edit')
                ->name('settings.update');
        });

        // Roles & Permissions (ACL)
        Route::middleware('permission:roles.view')->group(function () {
            Route::resource('roles', RoleController::class)->except(['show']);
        });

        // User Management
        Route::middleware('permission:users.view')->group(function () {
            Route::resource('users', UserController::class)->except(['show']);
        });

        // Article Categories
        Route::middleware('permission:article-categories.view')->group(function () {
            Route::resource('article-categories', ArticleCategoryController::class)->except(['show']);
        });

        // Articles & Approval Workflow
        Route::get('articles/pending', [AdminArticleController::class, 'pending'])
            ->middleware('permission:articles.approve')
            ->name('articles.pending');

        Route::get('articles/{article}/preview', [AdminArticleController::class, 'preview'])
            ->name('articles.preview');

        Route::patch('articles/{article}/approve', [AdminArticleController::class, 'approve'])
            ->middleware('permission:articles.approve')
            ->name('articles.approve');

        Route::patch('articles/{article}/reject', [AdminArticleController::class, 'reject'])
            ->middleware('permission:articles.approve')
            ->name('articles.reject');

        Route::patch('articles/{article}/unpublish', [AdminArticleController::class, 'unpublish'])
            ->middleware('permission:articles.approve')
            ->name('articles.unpublish');

        // Articles — index is open to any article-related permission (scope filtered in controller)
        Route::get('articles', [AdminArticleController::class, 'index'])->name('articles.index');

        Route::middleware('permission:articles.create')->group(function () {
            Route::get('articles/create', [AdminArticleController::class, 'create'])->name('articles.create');
            Route::post('articles', [AdminArticleController::class, 'store'])->name('articles.store');
        });

        Route::middleware('permission:articles.edit')->group(function () {
            Route::get('articles/{article}/edit', [AdminArticleController::class, 'edit'])->name('articles.edit');
            Route::put('articles/{article}', [AdminArticleController::class, 'update'])->name('articles.update');
            Route::patch('articles/{article}', [AdminArticleController::class, 'update']);
        });

        Route::delete('articles/{article}', [AdminArticleController::class, 'destroy'])
            ->middleware('permission:articles.delete')
            ->name('articles.destroy');

        // Article Tags CRUD
        Route::middleware('permission:article-tags.view')->group(function () {
            Route::resource('article-tags', ArticleTagController::class)->except(['show']);
        });

        // Gallery Categories
        Route::middleware('permission:gallery-categories.view')->group(function () {
            Route::resource('gallery-categories', GalleryCategoryController::class)->except(['show']);
        });

        // Gallery Management & Approval Workflow
        Route::get('galleries/pending', [AdminGalleryController::class, 'pending'])
            ->middleware('permission:galleries.approve')
            ->name('galleries.pending');

        Route::patch('galleries/{gallery}/approve', [AdminGalleryController::class, 'approve'])
            ->middleware('permission:galleries.approve')
            ->name('galleries.approve');

        Route::patch('galleries/{gallery}/reject', [AdminGalleryController::class, 'reject'])
            ->middleware('permission:galleries.approve')
            ->name('galleries.reject');

        Route::get('galleries', [AdminGalleryController::class, 'index'])->name('galleries.index');

        Route::middleware('permission:galleries.create')->group(function () {
            Route::get('galleries/create', [AdminGalleryController::class, 'create'])->name('galleries.create');
            Route::post('galleries', [AdminGalleryController::class, 'store'])->name('galleries.store');
        });

        Route::middleware('permission:galleries.edit')->group(function () {
            Route::get('galleries/{gallery}/edit', [AdminGalleryController::class, 'edit'])->name('galleries.edit');
            Route::put('galleries/{gallery}', [AdminGalleryController::class, 'update'])->name('galleries.update');
            Route::patch('galleries/{gallery}', [AdminGalleryController::class, 'update']);
        });

        Route::delete('galleries/{gallery}', [AdminGalleryController::class, 'destroy'])
            ->middleware('permission:galleries.delete')
            ->name('galleries.destroy');

        // Gallery Videos (YouTube)
        Route::middleware('permission:gallery-videos.view')->group(function () {
            Route::get('gallery-videos', [GalleryVideoController::class, 'index'])->name('gallery-videos.index');
        });
        Route::middleware('permission:gallery-videos.create')->group(function () {
            Route::get('gallery-videos/create', [GalleryVideoController::class, 'create'])->name('gallery-videos.create');
            Route::post('gallery-videos', [GalleryVideoController::class, 'store'])->name('gallery-videos.store');
        });
        Route::middleware('permission:gallery-videos.edit')->group(function () {
            Route::get('gallery-videos/{galleryVideo}/edit', [GalleryVideoController::class, 'edit'])->name('gallery-videos.edit');
            Route::put('gallery-videos/{galleryVideo}', [GalleryVideoController::class, 'update'])->name('gallery-videos.update');
            Route::patch('gallery-videos/{galleryVideo}/toggle-active', [GalleryVideoController::class, 'toggleActive'])->name('gallery-videos.toggle-active');
        });
        Route::delete('gallery-videos/{galleryVideo}', [GalleryVideoController::class, 'destroy'])
            ->middleware('permission:gallery-videos.delete')
            ->name('gallery-videos.destroy');

        // Activity Log
        Route::get('activity-log', [ActivityLogController::class, 'index'])
            ->middleware('permission:activity-log.view')
            ->name('activity-log.index');

        // Contact Messages
        Route::middleware('permission:contact-messages.view')->group(function () {
            Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
            Route::get('contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
            Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])
                ->middleware('permission:contact-messages.delete')
                ->name('contact-messages.destroy');
        });

        // Career / Job Postings
        Route::middleware('permission:careers.view')->group(function () {
            Route::get('careers', [JobPostingController::class, 'index'])->name('careers.index');
        });
        Route::middleware('permission:careers.create')->group(function () {
            Route::get('careers/create', [JobPostingController::class, 'create'])->name('careers.create');
            Route::post('careers', [JobPostingController::class, 'store'])->name('careers.store');
        });
        Route::middleware('permission:careers.edit')->group(function () {
            Route::get('careers/{career}/edit', [JobPostingController::class, 'edit'])->name('careers.edit');
            Route::put('careers/{career}', [JobPostingController::class, 'update'])->name('careers.update');
        });
        Route::delete('careers/{career}', [JobPostingController::class, 'destroy'])
            ->middleware('permission:careers.delete')
            ->name('careers.destroy');

        // Career Applications
        Route::middleware('permission:career-applications.view')->group(function () {
            Route::get('careers/{career}/applications', [JobPostingController::class, 'applications'])->name('careers.applications.index');
            Route::get('careers/{career}/applications/{application}/cv', [JobPostingController::class, 'downloadCv'])->name('careers.applications.cv');
        });
        Route::delete('careers/{career}/applications/{application}', [JobPostingController::class, 'destroyApplication'])
            ->middleware('permission:career-applications.delete')
            ->name('careers.applications.destroy');
    });

// Category URLs come last so existing public, authentication, and admin routes take precedence.
Route::get('/{category:slug}', [NewsController::class, 'category'])->name('news.category');
Route::get('/{category:slug}/{slug}', [NewsController::class, 'show'])->name('news.show');
