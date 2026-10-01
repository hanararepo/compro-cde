<?php

namespace App\Services\Careers;

use App\Models\JobApplication;
use App\Models\User;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class DeleteJobApplications
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function delete(array $ids, int $careerId, User $user): array
    {
        sort($ids);
        $locks = [];
        $deleted = 0;
        $failed = 0;

        try {
            // Reserve the entire selection before deleting anything. Sending uses these same locks.
            foreach ($ids as $id) {
                $lock = Cache::lock('career-application:'.$id, 300);
                if (! $lock->get()) {
                    throw ValidationException::withMessages(['ids' => 'Salah satu lamaran sedang diproses. Tunggu sebentar, lalu coba lagi.']);
                }
                $locks[] = $lock;
            }

            $applications = JobApplication::where('job_posting_id', $careerId)->whereIn('id', $ids)->get();
            foreach ($applications as $application) {
                try {
                    $disk = Storage::disk('local');
                    $path = $application->cv_path;
                    if ($path && $disk->exists($path) && ! $disk->delete($path)) {
                        throw new RuntimeException('Unable to delete application CV.');
                    }
                    if ($path && $disk->exists($path)) {
                        throw new RuntimeException('Application CV still exists.');
                    }

                    DB::transaction(function () use ($application, $user) {
                        $this->activityLog->log($user, 'deleted', "Deleted application #{$application->id} from '{$application->name}'.", $application);
                        $application->delete();
                    });
                    $deleted++;
                } catch (Throwable $exception) {
                    report($exception);
                    $failed++;
                }
            }
        } finally {
            foreach (array_reverse($locks) as $lock) {
                $lock->release();
            }
        }

        return compact('deleted', 'failed');
    }
}
