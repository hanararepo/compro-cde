<?php

namespace App\Policies;

use App\Models\Slider;
use App\Models\User;

class SliderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sliders.view');
    }

    public function view(User $user, Slider $slider): bool
    {
        return $user->can('sliders.view');
    }

    public function create(User $user): bool
    {
        return $user->can('sliders.create');
    }

    public function update(User $user, Slider $slider): bool
    {
        return $user->can('sliders.edit');
    }

    public function delete(User $user, Slider $slider): bool
    {
        return $user->can('sliders.delete');
    }
}
