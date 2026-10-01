<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('email_status')->default('legacy');
            $table->unsignedInteger('email_attempts')->default(0);
            $table->string('email_recipient')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->text('email_last_error')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn(['email_status', 'email_attempts', 'email_recipient', 'email_sent_at', 'email_last_error']);
        });
    }
};
