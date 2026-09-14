<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleTag;
use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleContentSeeder extends Seeder
{
    /**
     * Seed sample categories, multilingual articles, and gallery items.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@cms.local')->first();
        $author = User::where('email', 'author@cms.local')->first() ?? $admin;

        // 1. Article Categories
        $catTech = ArticleCategory::firstOrCreate(
            ['slug' => 'technology'],
            [
                'name' => ['en' => 'Technology & Architecture', 'id' => 'Teknologi & Arsitektur'],
                'description' => 'Insights into software architecture, clean code, and modern frameworks.',
                'color' => '#6366f1',
                'sort_order' => 1,
            ]
        );

        $catDesign = ArticleCategory::firstOrCreate(
            ['slug' => 'design-systems'],
            [
                'name' => ['en' => 'Design & UI/UX', 'id' => 'Desain & UI/UX'],
                'description' => 'Modern user interface design and component aesthetics.',
                'color' => '#ec4899',
                'sort_order' => 2,
            ]
        );

        // 2. Article Tags
        $tagLaravel = ArticleTag::firstOrCreate(
            ['slug' => 'laravel'],
            ['name' => ['en' => 'Laravel 13', 'id' => 'Laravel 13']]
        );
        $tagCleanCode = ArticleTag::firstOrCreate(
            ['slug' => 'clean-code'],
            ['name' => ['en' => 'Clean Code', 'id' => 'Kode Bersih']]
        );

        // 3. Published Articles
        $article1 = Article::firstOrCreate(
            ['slug->en' => 'building-scalable-cms-with-laravel-13'],
            [
                'title' => [
                    'en' => 'Building a Scalable & Modular CMS with Laravel 13',
                    'id' => 'Membangun CMS yang Scalable & Modular dengan Laravel 13',
                ],
                'slug' => [
                    'en' => 'building-scalable-cms-with-laravel-13',
                    'id' => 'membangun-cms-scalable-modular-laravel-13',
                ],
                'summary' => [
                    'en' => 'Learn how to architect an enterprise-grade CMS with clean code, service layers, and robust ACL.',
                    'id' => 'Pelajari cara merancang CMS tingkat enterprise dengan clean code, service layer, dan sistem ACL yang kokoh.',
                ],
                'content' => [
                    'en' => "Laravel 13 introduces exceptional speed and elegance for modern web applications. In this architecture, we decouple heavy business logic into dedicated Service classes (such as ArticleService, RoleService, and SettingService) while keeping our Controllers lean and focused purely on HTTP request/response orchestration.\n\nOur Access Control List (ACL) utilizes Spatie's permission package with an automatic Super Administrator bypass implemented at the Gate level. Furthermore, multi-language internationalization is achieved effortlessly via JSON attributes on Eloquent models.",
                    'id' => "Laravel 13 menghadirkan kecepatan dan keanggunan luar biasa untuk aplikasi web modern. Dalam arsitektur ini, logika bisnis dipisahkan ke dalam Service classes khusus (seperti ArticleService, RoleService, dan SettingService) sementara Controller tetap ramping dan hanya berfokus pada orkestrasi request/response HTTP.\n\nSistem ACL memanfaatkan package Spatie Permission dengan fitur bypass Super Administrator otomatis di tingkat Gate. Selain itu, fitur multi-bahasa didukung secara native menggunakan kolom JSON pada model Eloquent.",
                ],
                'status' => ArticleStatus::Published,
                'article_category_id' => $catTech->id,
                'author_id' => $admin->id,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'published_at' => now(),
                'views_count' => 142,
                'is_featured' => true,
            ]
        );
        $article1->tags()->sync([$tagLaravel->id, $tagCleanCode->id]);

        // 4. Pending Article (To demonstrate Approval Queue)
        $article2 = Article::firstOrCreate(
            ['slug->en' => 'mastering-tailwind-css-v4-component-design'],
            [
                'title' => [
                    'en' => 'Mastering Tailwind CSS v4 & Component Architecture',
                    'id' => 'Menguasai Tailwind CSS v4 & Arsitektur Komponen',
                ],
                'slug' => [
                    'en' => 'mastering-tailwind-css-v4-component-design',
                    'id' => 'menguasai-tailwind-css-v4-arsitektur-komponen',
                ],
                'summary' => [
                    'en' => 'An editorial submission reviewing modern utility-first CSS practices.',
                    'id' => 'Artikel kiriman yang mengulas praktik terbaik utility-first CSS modern.',
                ],
                'content' => [
                    'en' => 'Tailwind CSS v4 revolutionizes frontend workflow with engine optimizations and simplified configuration. Reusable Blade components encapsulate form inputs, data tables, and modal dialogs smoothly.',
                    'id' => 'Tailwind CSS v4 merevolusi alur kerja frontend dengan optimasi engine dan konfigurasi yang lebih sederhana. Komponen Blade reusable membungkus input form, tabel data, dan modal dialog dengan sangat rapi.',
                ],
                'status' => ArticleStatus::Pending,
                'article_category_id' => $catDesign->id,
                'author_id' => $author->id,
                'is_featured' => false,
            ]
        );

        // 5. Gallery Category
        $galCat = GalleryCategory::firstOrCreate(
            ['slug' => 'company-activities'],
            [
                'name' => ['en' => 'Activities & Events', 'id' => 'Kegiatan & Acara'],
                'description' => 'Moments captured from workshops and community conferences.',
                'sort_order' => 1,
            ]
        );
    }
}
