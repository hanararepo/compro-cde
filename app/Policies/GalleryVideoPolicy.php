<?php

namespace App\Policies;

use App\Models\GalleryVideo;
use App\Models\User;

class GalleryVideoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('gallery-videos.view');
    }

    public function view(User $user, GalleryVideo $video): bool
    {
        return $user->can('gallery-videos.view');
    }

    public function create(User $user): bool
    {
        return $user->can('gallery-videos.create');
    }

    public function update(User $user, GalleryVideo $video): bool
    {
        return $user->can('gallery-videos.edit');
    }

    public function delete(User $user, GalleryVideo $video): bool
    {
        return $user->can('gallery-videos.delete');
    }
}
