<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CoalProductPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (['view', 'create', 'edit', 'delete'] as $action) {
            Permission::findOrCreate('coal-products.'.$action, 'web');
        }

        // Add this module without resetting existing role assignments.
        foreach (['Admin' => ['view', 'create', 'edit', 'delete'], 'Editor' => ['view', 'create', 'edit']] as $name => $actions) {
            $role = Role::where('name', $name)->where('guard_name', 'web')->first();
            $role?->givePermissionTo(array_map(fn ($action) => 'coal-products.'.$action, $actions));
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
