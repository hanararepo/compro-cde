<?php

namespace Database\Seeders;

use App\Services\Acl\RoleService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roleService = new RoleService;

        // 1. Seed all atomic module permissions
        $roleService->seedPermissions();

        // 2. Create core roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $editorRole = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        $authorRole = Role::firstOrCreate(['name' => 'Author', 'guard_name' => 'web']);

        // 3. Assign permissions to Admin (Full access except Roles & ACL)
        $adminRole->syncPermissions([
            'coal-products.view',
            'coal-products.create',
            'coal-products.edit',
            'coal-products.delete',
            'articles.view',
            'articles.view-others',
            'articles.create',
            'articles.edit',
            'articles.delete',
            'articles.approve',
            'article-categories.view',
            'article-categories.create',
            'article-categories.edit',
            'article-categories.delete',
            'article-tags.view',
            'article-tags.create',
            'article-tags.edit',
            'article-tags.delete',
            'galleries.view',
            'galleries.view-others',
            'galleries.create',
            'galleries.edit',
            'galleries.delete',
            'galleries.approve',
            'gallery-categories.view',
            'gallery-categories.create',
            'gallery-categories.edit',
            'gallery-categories.delete',
            'gallery-videos.view',
            'gallery-videos.create',
            'gallery-videos.edit',
            'gallery-videos.delete',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'settings.view',
            'settings.edit',
            'settings.appearance',
            'activity-log.view',
            'contact-messages.view',
            'contact-messages.delete',
            'contact-messages.reply',
            'careers.view',
            'careers.create',
            'careers.edit',
            'careers.delete',
            'career-applications.view',
            'career-applications.delete',
            'sliders.view',
            'sliders.create',
            'sliders.edit',
            'sliders.delete',
            'awards.view',
            'awards.create',
            'awards.edit',
            'awards.delete',
        ]);

        // 4. Assign permissions to Editor
        $editorRole->syncPermissions([
            'coal-products.view',
            'coal-products.create',
            'coal-products.edit',
            'articles.view',
            'articles.view-others',
            'articles.create',
            'articles.edit',
            'articles.delete',
            'articles.approve',
            'article-categories.view',
            'article-categories.create',
            'article-categories.edit',
            'article-tags.view',
            'article-tags.create',
            'article-tags.edit',
            'article-tags.delete',
            'galleries.view',
            'galleries.view-others',
            'galleries.create',
            'galleries.edit',
            'galleries.delete',
            'galleries.approve',
            'gallery-categories.view',
            'gallery-categories.create',
            'gallery-categories.edit',
            'gallery-videos.view',
            'gallery-videos.create',
            'gallery-videos.edit',
            'activity-log.view',
            'contact-messages.view',
            'contact-messages.reply',
            'careers.view',
            'career-applications.view',
            'sliders.view',
            'sliders.create',
            'sliders.edit',
            'awards.view',
            'awards.create',
            'awards.edit',
        ]);

        // 5. Assign permissions to Author
        $authorRole->syncPermissions([
            'articles.view',
            'articles.create',
            'articles.edit',
            'articles.delete',
            'galleries.view',
            'galleries.create',
            'galleries.edit',
            'galleries.delete',
        ]);
    }
}
