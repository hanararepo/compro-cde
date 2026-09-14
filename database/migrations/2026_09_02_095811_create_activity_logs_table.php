<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 50);                   // created, updated, deleted, approved, rejected, logged_in, logged_out
            $table->string('subject_type', 100)->nullable(); // e.g. App\Models\Article
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label')->nullable();   // snapshot: title/name at time of action
            $table->string('description');                 // human-readable sentence
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
