<?php

namespace App\Services\ActivityLog;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Record an activity log entry.
     *
     * @param  User|null  $user  The user performing the action (null for system events)
     * @param  string  $event  e.g. created, updated, deleted, approved, rejected, logged_in, logged_out
     * @param  string  $description  Human-readable sentence, e.g. "Created article 'My Post'"
     * @param  Model|null  $subject  The Eloquent model being acted upon
     */
    public function log(
        ?User $user,
        string $event,
        string $description,
        ?Model $subject = null
    ): void {
        ActivityLog::create([
            'user_id' => $user?->id,
            'event' => $event,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'subject_label' => $this->resolveSubjectLabel($subject),
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Extract a human-readable label from the subject model.
     * Tries common translatable title/name fields first.
     */
    private function resolveSubjectLabel(?Model $subject): ?string
    {
        if (! $subject) {
            return null;
        }

        foreach (['title', 'name'] as $field) {
            if (method_exists($subject, 'getTranslation')) {
                try {
                    $value = $subject->getTranslation($field, 'en', false);
                    if ($value) {
                        return (string) $value;
                    }
                } catch (\Throwable) {
                    // field doesn't exist on this model — try next
                }
            }

            if (isset($subject->$field) && $subject->$field) {
                return (string) $subject->$field;
            }
        }

        return class_basename($subject).' #'.$subject->getKey();
    }
}
