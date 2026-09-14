<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Article categories with translatable name and slug.
     * Supports one level of nesting via parent_id (self-referential).
     */
    public function up(): void
    {
        Schema::create('article_categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');        // Translatable: {"en": "...", "id": "..."}
            $table->string('slug')->unique()->index();
            $table->text('description')->nullable();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('article_categories')
                ->nullOnDelete();
            $table->string('color', 7)->nullable(); // Hex color for badge
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_categories');
    }
};
