<?php

namespace App\Policies;

use App\Models\Gallery;
use App\Models\User;

class GalleryPolicy
{
    /**
     * Administrators bypass all policy checks (handled in AppServiceProvider).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('galleries.view') || $user->can('galleries.view-others');
    }

    public function view(User $user, Gallery $gallery): bool
    {
        // Uploader can always view their own gallery item
        if ($gallery->uploaded_by === $user->id) {
            return true;
        }

        return $user->can('galleries.view-others');
    }

    public function create(User $user): bool
    {
        return $user->can('galleries.create');
    }

    public function update(User $user, Gallery $gallery): bool
    {
        // Uploader can only edit their own draft/pending/rejected galleries
        if ($gallery->uploaded_by === $user->id) {
            return $user->can('galleries.edit')
                && in_array($gallery->status->value, ['draft', 'pending', 'rejected']);
        }

        return $user->can('galleries.edit') && $user->can('galleries.view-others');
    }

    public function delete(User $user, Gallery $gallery): bool
    {
        // Uploader can delete their own gallery item
        if ($gallery->uploaded_by === $user->id) {
            return $user->can('galleries.delete');
        }

        return $user->can('galleries.delete') && $user->can('galleries.view-others');
    }

    public function approve(User $user, ?Gallery $gallery = null): bool
    {
        return $user->can('galleries.approve');
    }
}
