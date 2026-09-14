<?php

namespace App\Policies;

use App\Models\JobApplication;
use App\Models\User;

class JobApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('career-applications.view');
    }

    public function delete(User $user, JobApplication $application): bool
    {
        return $user->can('career-applications.delete');
    }
}
