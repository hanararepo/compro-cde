<?php

namespace App\Policies;

use App\Models\CoalProduct;
use App\Models\User;

class CoalProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('coal-products.view');
    }

    public function create(User $user): bool
    {
        return $user->can('coal-products.create');
    }

    public function update(User $user, CoalProduct $coalProduct): bool
    {
        return $user->can('coal-products.edit');
    }

    public function delete(User $user, CoalProduct $coalProduct): bool
    {
        return $user->can('coal-products.delete');
    }
}
