<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Article tags and the pivot table linking them to articles.
     */
    public function up(): void
    {
        Schema::create('article_tags', function (Blueprint $table) {
            $table->id();
            $table->json('name');           // Translatable
            $table->string('slug')->unique()->index();
            $table->timestamps();
        });

        Schema::create('article_article_tag', function (Blueprint $table) {
            $table->foreignId('article_id')
                ->constrained('articles')
                ->cascadeOnDelete();
            $table->foreignId('article_tag_id')
                ->constrained('article_tags')
                ->cascadeOnDelete();
            $table->primary(['article_id', 'article_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_article_tag');
        Schema::dropIfExists('article_tags');
    }
};
