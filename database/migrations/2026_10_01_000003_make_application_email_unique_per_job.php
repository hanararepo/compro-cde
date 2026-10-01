<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasDuplicates = DB::table('job_applications')
            ->selectRaw('job_posting_id, LOWER(TRIM(email)) AS normalized_email')
            ->groupBy('job_posting_id', DB::raw('LOWER(TRIM(email))'))
            ->havingRaw('COUNT(*) > 1')->exists();

        // Preserve historical applications: never silently delete duplicates during deployment.
        if ($hasDuplicates) {
            throw new RuntimeException('Duplicate application emails exist for the same job. Resolve these records before running this migration.');
        }

        DB::table('job_applications')->update(['email' => DB::raw('LOWER(TRIM(email))')]);

        Schema::table('job_applications', function (Blueprint $table) {
            $table->unique(['job_posting_id', 'email'], 'job_applications_job_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropUnique('job_applications_job_email_unique');
        });
    }
};
