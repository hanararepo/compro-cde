<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminRoleAndUserRestrictionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_role_exists_and_has_proper_permissions(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();

        $this->assertNotNull($adminRole);
        $this->assertTrue($adminRole->hasPermissionTo('articles.view'));
        $this->assertTrue($adminRole->hasPermissionTo('articles.approve'));
        $this->assertTrue($adminRole->hasPermissionTo('users.create'));
        $this->assertTrue($adminRole->hasPermissionTo('settings.view'));
        $this->assertTrue($adminRole->hasPermissionTo('activity-log.view'));

        // Must NOT have roles/ACL permissions
        $this->assertFalse($adminRole->hasPermissionTo('roles.view'));
        $this->assertFalse($adminRole->hasPermissionTo('roles.create'));
        $this->assertFalse($adminRole->hasPermissionTo('roles.edit'));
        $this->assertFalse($adminRole->hasPermissionTo('roles.delete'));
    }

    public function test_admin_can_access_all_modules_except_roles_and_acl(): void
    {
        $admin = User::where('email', 'staffadmin@cms.local')->first();

        // Accessible modules
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.articles.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.articles.pending'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.galleries.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.users.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.settings.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.activity-log.index'))->assertStatus(200);

        // Forbidden: Roles & ACL
        $this->actingAs($admin)->get(route('admin.roles.index'))->assertStatus(403);
    }

    public function test_admin_does_not_see_administrator_role_in_create_user_form(): void
    {
        $admin = User::where('email', 'staffadmin@cms.local')->first();

        $response = $this->actingAs($admin)->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertSee('Admin');
        $response->assertSee('Editor');
        $response->assertSee('Author');
        $response->assertDontSee('value="Administrator"', false);
    }

    public function test_administrator_sees_administrator_role_in_create_user_form(): void
    {
        $superAdmin = User::where('email', 'admin@cms.local')->first();

        $response = $this->actingAs($superAdmin)->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertSee('value="Administrator"', false);
    }

    public function test_non_administrator_cannot_create_user_with_administrator_role(): void
    {
        $admin = User::where('email', 'staffadmin@cms.local')->first();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Attempted Super Admin',
            'email' => 'fake_admin@cms.local',
            'password' => 'Password123!',
            'role' => 'Administrator',
            'is_active' => '1',
        ]);

        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseMissing('users', ['email' => 'fake_admin@cms.local']);
    }

    public function test_administrator_can_create_user_with_administrator_role(): void
    {
        $superAdmin = User::where('email', 'admin@cms.local')->first();

        $response = $this->actingAs($superAdmin)->post(route('admin.users.store'), [
            'name' => 'Legit Super Admin',
            'email' => 'new_super@cms.local',
            'password' => 'Password123!',
            'role' => 'Administrator',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $createdUser = User::where('email', 'new_super@cms.local')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasRole('Administrator'));
    }

    public function test_admin_can_create_user_with_other_roles(): void
    {
        $admin = User::where('email', 'staffadmin@cms.local')->first();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Editor Guy',
            'email' => 'new_editor@cms.local',
            'password' => 'Password123!',
            'role' => 'Editor',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $createdUser = User::where('email', 'new_editor@cms.local')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasRole('Editor'));
    }

    public function test_non_administrator_cannot_edit_administrator_user(): void
    {
        $admin = User::where('email', 'staffadmin@cms.local')->first();
        $superAdmin = User::where('email', 'admin@cms.local')->first();

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $superAdmin))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $superAdmin), [
                'name' => 'Tampered Admin',
                'email' => 'admin@cms.local',
                'role' => 'Editor',
            ])
            ->assertStatus(403);
    }

    public function test_non_administrator_cannot_delete_administrator_user(): void
    {
        $admin = User::where('email', 'staffadmin@cms.local')->first();
        $superAdmin = User::where('email', 'admin@cms.local')->first();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $superAdmin))
            ->assertStatus(403);

        $this->assertDatabaseHas('users', ['email' => 'admin@cms.local']);
    }

    public function test_author_without_view_others_does_not_see_author_filter_in_articles(): void
    {
        $author = User::where('email', 'author@cms.local')->first();

        $response = $this->actingAs($author)->get(route('admin.articles.index'));

        $response->assertStatus(200);
        $response->assertDontSee('filters.author', false);
    }

    public function test_user_with_view_others_sees_author_filter_in_articles(): void
    {
        $superAdmin = User::where('email', 'admin@cms.local')->first();

        $response = $this->actingAs($superAdmin)->get(route('admin.articles.index'));

        $response->assertStatus(200);
        $response->assertSee('filters.author', false);
        $response->assertSee('All Authors');
    }

    public function test_author_without_view_others_does_not_see_author_filter_in_galleries(): void
    {
        $author = User::where('email', 'author@cms.local')->first();

        $response = $this->actingAs($author)->get(route('admin.galleries.index'));

        $response->assertStatus(200);
        $response->assertDontSee('filters.author', false);
    }

    public function test_user_with_view_others_sees_author_filter_in_galleries(): void
    {
        $superAdmin = User::where('email', 'admin@cms.local')->first();

        Gallery::create([
            'title' => ['en' => 'Test Gallery Photo', 'id' => 'Foto Galeri Uji'],
            'image_path' => 'galleries/test.jpg',
            'uploaded_by' => $superAdmin->id,
            'status' => 'published',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.galleries.index'));

        $response->assertStatus(200);
        $response->assertSee('filters.author', false);
        $response->assertSee('All Authors');
        $response->assertSee($superAdmin->name);

        // Filter by author
        $filterResponse = $this->actingAs($superAdmin)->get(route('admin.galleries.index', ['author' => $superAdmin->id]));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Test Gallery Photo');
    }
}
