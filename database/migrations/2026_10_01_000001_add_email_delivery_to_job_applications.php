<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('cv_path')->nullable()->change();
            // Existing applications must not be emailed automatically.
            $table->string('email_status')->default('legacy')->index();
            $table->unsignedInteger('email_attempts')->default(0);
            $table->string('email_recipient')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('email_next_attempt_at')->nullable()->index();
            $table->text('email_last_error')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropIndex(['email_status']);
            $table->dropIndex(['email_next_attempt_at']);
            $table->dropColumn(['email_status', 'email_attempts', 'email_recipient', 'email_sent_at', 'email_next_attempt_at', 'email_last_error']);
        });
        // Keep cv_path nullable: successfully delivered CVs have been deleted.
    }
};
