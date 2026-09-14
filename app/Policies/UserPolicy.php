<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any user accounts.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    /**
     * Determine whether the user can view a specific user account.
     */
    public function view(User $user, User $targetUser): bool
    {
        return $user->can('users.view');
    }

    /**
     * Determine whether the user can create user accounts.
     */
    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    /**
     * Determine whether the user can update a user account.
     * Non-administrators can never modify an Administrator account.
     */
    public function update(User $user, User $targetUser): bool
    {
        if (! $user->can('users.edit')) {
            return false;
        }

        if ($targetUser->hasRole('Administrator') && ! $user->hasRole('Administrator')) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete a user account.
     * Cannot delete self, and non-administrators can never delete an Administrator account.
     */
    public function delete(User $user, User $targetUser): bool
    {
        if (! $user->can('users.delete')) {
            return false;
        }

        if ($targetUser->id === $user->id) {
            return false;
        }

        if ($targetUser->hasRole('Administrator') && ! $user->hasRole('Administrator')) {
            return false;
        }

        return true;
    }
}
