<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Awards & Certificates table.
     * Type column distinguishes award vs certificate.
     * Title and description are translatable JSON (EN/ID via spatie/laravel-translatable).
     */
    public function up(): void
    {
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->json('title');                   // Translatable: EN/ID
            $table->json('description')->nullable(); // Translatable: Quill HTML content
            $table->string('image_path');
            $table->string('type')->default('award'); // 'award' | 'certificate'
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('awards');
    }
};
