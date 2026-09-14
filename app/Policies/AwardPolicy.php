<?php

namespace App\Policies;

use App\Models\Award;
use App\Models\User;

class AwardPolicy
{
    /**
     * Administrators bypass all policy checks (handled in AppServiceProvider).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('awards.view') || $user->can('awards.view-others');
    }

    public function view(User $user, Award $award): bool
    {
        return $user->can('awards.view') || $user->can('awards.view-others');
    }

    public function create(User $user): bool
    {
        return $user->can('awards.create');
    }

    public function update(User $user, Award $award): bool
    {
        return $user->can('awards.edit');
    }

    public function delete(User $user, Award $award): bool
    {
        return $user->can('awards.delete');
    }
}
