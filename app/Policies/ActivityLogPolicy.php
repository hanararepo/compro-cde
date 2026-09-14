<?php

namespace App\Policies;

use App\Models\User;

class ActivityLogPolicy
{
    /**
     * Only users with the activity-log.view permission may see the log.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('activity-log.view');
    }
}
