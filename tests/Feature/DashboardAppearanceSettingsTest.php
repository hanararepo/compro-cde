<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAppearanceSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_super_admin_and_admin_can_view_appearance_tab_in_settings(): void
    {
        $admin = User::where('email', 'admin@cms.local')->first();

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Theme');
        $response->assertSee('Warna Aksen Dashboard');
        $response->assertSee('Tipe & Warna Background Sidebar', false);
    }

    public function test_authorized_user_can_update_accent_color_and_sidebar_theme(): void
    {
        $admin = User::where('email', 'admin@cms.local')->first();

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'site_name' => 'Hanara Updated',
            'admin_accent_color' => 'blue',
            'admin_sidebar_theme' => 'dark',
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertSame('blue', Setting::get('admin_accent_color'));
        $this->assertSame('dark', Setting::get('admin_sidebar_theme'));
    }

    public function test_global_theme_setting_is_reflected_in_admin_layout_for_all_users(): void
    {
        Setting::set('admin_accent_color', 'violet');
        Setting::set('admin_sidebar_theme', 'white');

        // Another staff admin logs in
        $staffAdmin = User::where('email', 'staffadmin@cms.local')->first();

        $response = $this->actingAs($staffAdmin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        // The dynamic style tag should inject the violet hex value (#7c3aed for shade 600)
        $response->assertSee('--color-brand-600: #7c3aed;', false);
    }

    public function test_user_without_appearance_permission_cannot_update_theme(): void
    {
        $author = User::where('email', 'author@cms.local')->first();

        $response = $this->actingAs($author)->put(route('admin.settings.update'), [
            'admin_accent_color' => 'rose',
        ]);

        // settings.edit / settings.view middleware or controller ACL aborts
        $this->assertContains($response->status(), [403]);
    }

    public function test_validation_rejects_invalid_accent_or_sidebar_theme(): void
    {
        $admin = User::where('email', 'admin@cms.local')->first();

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'admin_accent_color' => 'invalid-rainbow-color',
            'admin_sidebar_theme' => 'neon-purple',
        ]);

        $response->assertSessionHasErrors(['admin_accent_color', 'admin_sidebar_theme']);
    }
}
