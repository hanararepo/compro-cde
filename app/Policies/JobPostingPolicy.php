<?php

namespace App\Policies;

use App\Models\JobPosting;
use App\Models\User;

class JobPostingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('careers.view');
    }

    public function create(User $user): bool
    {
        return $user->can('careers.create');
    }

    public function update(User $user, JobPosting $jobPosting): bool
    {
        return $user->can('careers.edit');
    }

    public function delete(User $user, JobPosting $jobPosting): bool
    {
        return $user->can('careers.delete');
    }
}
