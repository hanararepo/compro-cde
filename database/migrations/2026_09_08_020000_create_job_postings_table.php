<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->json('title');        // {"en": "...", "id": "..."}
            $table->json('description'); // {"en": "...", "id": "..."}  — rich HTML from Quill
            $table->string('image')->nullable();
            $table->enum('type', ['full_time', 'part_time'])->default('full_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
