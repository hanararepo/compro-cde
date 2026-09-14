<?php

namespace App\Services\Acl;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    /**
     * Module definitions for the permission matrix.
     * Each module maps to a set of actions.
     *
     * @return array<string, array<string>>
     */
    public function getModulePermissions(): array
    {
        return [
            'articles' => ['view', 'view-others', 'create', 'edit', 'delete', 'approve'],
            'article-categories' => ['view', 'create', 'edit', 'delete'],
            'article-tags' => ['view', 'create', 'edit', 'delete'],
            'galleries' => ['view', 'view-others', 'create', 'edit', 'delete', 'approve'],
            'gallery-categories' => ['view', 'create', 'edit', 'delete'],
            'gallery-videos' => ['view', 'create', 'edit', 'delete'],
            'coal-products' => ['view', 'create', 'edit', 'delete'],
            'awards' => ['view', 'create', 'edit', 'delete'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'roles' => ['view', 'create', 'edit', 'delete'],
            'settings' => ['view', 'edit', 'appearance'],
            'activity-log' => ['view'],
            'contact-messages' => ['view', 'delete', 'reply'],
            'careers' => ['view', 'create', 'edit', 'delete'],
            'career-applications' => ['view', 'delete'],
            'sliders' => ['view', 'create', 'edit', 'delete'],
        ];
    }

    /**
     * Returns all permissions grouped by module, ready for the permission matrix UI.
     *
     * @return array<string, Collection<Permission>>
     */
    public function getPermissionsGroupedByModule(): array
    {
        $allPermissions = Permission::all();
        $grouped = [];

        foreach ($this->getModulePermissions() as $module => $actions) {
            $grouped[$module] = $allPermissions->filter(
                fn (Permission $permission) => str_starts_with($permission->name, $module.'.')
            )->values();
        }

        return $grouped;
    }

    /**
     * Create a new role and sync the provided permission names to it.
     *
     * @param  array{name: string, permissions: list<string>}  $data
     */
    public function createRole(array $data): Role
    {
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return $role;
    }

    /**
     * Update a role's name and re-sync its permissions.
     *
     * @param  array{name: string, permissions: list<string>}  $data
     */
    public function updateRole(Role $role, array $data): Role
    {
        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return $role->fresh();
    }

    /**
     * Seed all permissions defined in getModulePermissions().
     * Called by the database seeder.
     */
    public function seedPermissions(): void
    {
        foreach ($this->getModulePermissions() as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }
    }
}
