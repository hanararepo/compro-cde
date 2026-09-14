<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Main articles table with JSON translatable columns.
     *
     * Status flow: draft → pending → published | rejected
     *
     * Columns:
     *  - title/content/summary/slug: JSON (translatable via spatie/laravel-translatable)
     *  - status: enum representing approval workflow state
     *  - thumbnail: relative path stored in public storage
     *  - author_id: the user who wrote the article
     *  - approved_by: the admin/editor who approved it
     *  - approved_at: timestamp of approval
     *  - published_at: nullable, allows scheduled publishing
     *  - rejection_reason: optional note when rejecting
     *  - views_count: simple counter for popularity metrics
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->json('title');          // Translatable
            $table->json('slug');           // Translatable unique slugs per locale
            $table->json('content');        // Translatable rich content
            $table->json('summary')->nullable(); // Translatable excerpt

            $table->string('thumbnail')->nullable();

            $table->enum('status', ['draft', 'pending', 'published', 'rejected'])
                ->default('draft')
                ->index();

            $table->text('rejection_reason')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->foreignId('article_category_id')
                ->nullable()
                ->constrained('article_categories')
                ->nullOnDelete();

            $table->foreignId('author_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->unsignedBigInteger('views_count')->default(0);
            $table->boolean('is_featured')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
