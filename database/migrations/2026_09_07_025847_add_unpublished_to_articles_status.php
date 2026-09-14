<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add 'unpublished' to the articles.status enum.
     * Unpublished = previously published but now hidden from the public frontend.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending', 'published', 'unpublished', 'rejected'])
                ->default('draft')->change();
        });
    }

    /**
     * Reverse the migration — remove 'unpublished' from the enum.
     * Articles with status 'unpublished' will be moved back to 'draft' first.
     */
    public function down(): void
    {
        // Safeguard: move unpublished articles to draft before removing the value
        DB::statement("UPDATE articles SET status = 'draft' WHERE status = 'unpublished'");
        Schema::table('articles', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending', 'published', 'rejected'])
                ->default('draft')->change();
        });
    }
};
