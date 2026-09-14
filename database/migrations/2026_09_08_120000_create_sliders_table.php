<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->json('title');              // {'en': '...', 'id': '...'}
            $table->json('description')->nullable(); // {'en': '...', 'id': '...'}
            $table->string('image_desktop');    // path for desktop hero image
            $table->string('image_mobile')->nullable(); // path for mobile hero image
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
