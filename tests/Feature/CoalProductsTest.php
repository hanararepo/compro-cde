<?php

namespace Tests\Feature;

use App\Models\CoalProduct;
use App\Models\Setting;
use App\Models\User;
use App\Services\Acl\RoleService;
use Database\Seeders\CoalProductPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CoalProductsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Setting::flushCache();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        (new RoleService)->seedPermissions();
    }

    private function rows(): array
    {
        return [
            ['parameter' => 'Total Moisture', 'typical' => '30%', 'rejection' => '-'],
            ['parameter' => 'Ash Content', 'typical' => '5%', 'rejection' => '> 10%'],
            ['parameter' => 'Total Sulphur', 'typical' => '0.25%', 'rejection' => '> 0.5%'],
        ];
    }

    private function editor(array $actions = ['view', 'create', 'edit', 'delete']): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo(array_map(fn ($action) => 'coal-products.'.$action, $actions));

        return $user;
    }

    private function product(string $name = 'CDE 4600 GAR'): CoalProduct
    {
        return CoalProduct::create(['name' => $name, 'specifications' => $this->rows()]);
    }

    public function test_crud_preserves_paired_values_and_removes_deleted_rows(): void
    {
        $this->actingAs($this->editor());
        $this->get(route('admin.coal-products.create'))->assertOk()->assertSee('Add Parameter');
        $this->post(route('admin.coal-products.store'), ['name' => 'CDE 4600 GAR', 'specifications' => $this->rows()])
            ->assertRedirect(route('admin.coal-products.index'));
        $product = CoalProduct::sole();
        $this->assertSame('cde-4600-gar', $product->slug);
        $this->assertSame($this->rows(), $product->specifications);
        $this->assertDatabaseHas('activity_logs', ['event' => 'created', 'subject_type' => CoalProduct::class, 'subject_id' => $product->id]);
        $this->get(route('admin.coal-products.edit', $product))->assertOk()->assertSee('Total Moisture');

        // A removed middle row must not leave stale or mismatched values behind.
        $newRows = [0 => $this->rows()[2], 4 => ['parameter' => 'Ash Content', 'typical' => '0', 'rejection' => '-']];
        $this->put(route('admin.coal-products.update', $product), ['name' => 'CDE Updated', 'specifications' => $newRows, 'slug' => 'untrusted-slug'])
            ->assertRedirect(route('admin.coal-products.index'));
        $this->assertSame(array_values($newRows), $product->fresh()->specifications);
        $this->assertSame('cde-4600-gar', $product->fresh()->slug);
        $this->get(route('coal-products.show', ['coalProduct' => $product->slug]))->assertOk()->assertSee('CDE Updated')->assertDontSee('Total Moisture');

        $this->delete(route('admin.coal-products.destroy', $product))->assertRedirect(route('admin.coal-products.index'));
        $this->assertModelMissing($product);
        $this->get(route('coal-products.show', ['coalProduct' => $product->slug]))->assertNotFound();
        $this->assertDatabaseHas('activity_logs', ['event' => 'deleted', 'subject_type' => CoalProduct::class, 'subject_id' => $product->id]);
    }

    public function test_validation_rejects_incomplete_rows_without_losing_previous_input_or_saved_data(): void
    {
        $this->actingAs($this->editor());
        $product = $this->product();
        $url = route('admin.coal-products.edit', $product);
        $rows = $this->rows();
        $rows[1]['typical'] = '';
        $this->from($url)->put(route('admin.coal-products.update', $product), ['name' => 'Keep this name', 'specifications' => $rows])
            ->assertRedirect($url)->assertSessionHasErrors(['specifications.1.typical'])->assertSessionHasInput('name', 'Keep this name');
        $this->get($url)->assertOk()->assertSee('Keep this name')->assertSee('Ash Content');
        $this->assertSame($this->rows(), $product->fresh()->specifications);

        foreach ([[], 'invalid', [['parameter' => ['bad'], 'typical' => '30%', 'rejection' => '-']], [['parameter' => 'TM', 'typical' => '30%']]] as $invalid) {
            $this->post(route('admin.coal-products.store'), ['name' => 'Invalid', 'specifications' => $invalid])->assertSessionHasErrors();
        }
        $this->assertSame(1, CoalProduct::count());
        $this->post(route('admin.coal-products.store'), ['name' => ' ', 'specifications' => $this->rows()])->assertSessionHasErrors('name');
    }

    public function test_each_admin_action_requires_its_own_permission_and_hidden_buttons_stay_hidden(): void
    {
        $product = $this->product();
        $payload = ['name' => 'Not allowed', 'specifications' => $this->rows()];
        $this->get(route('admin.coal-products.index'))->assertRedirect(route('login'));
        $this->post(route('admin.coal-products.store'), $payload)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create());
        $this->get(route('admin.coal-products.index'))->assertForbidden();
        $this->get(route('admin.coal-products.create'))->assertForbidden();
        $this->get(route('admin.coal-products.edit', $product))->assertForbidden();
        $this->post(route('admin.coal-products.store'), $payload)->assertForbidden();
        $this->put(route('admin.coal-products.update', $product), $payload)->assertForbidden();
        $this->delete(route('admin.coal-products.destroy', $product))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertOk()->assertDontSee(route('admin.coal-products.index'), false);

        $this->actingAs($this->editor(['view']));
        $this->get(route('admin.coal-products.index'))->assertOk()->assertSee('CDE 4600 GAR')
            ->assertDontSee(route('admin.coal-products.create'), false)->assertDontSee(route('admin.coal-products.edit', $product), false);
        $this->put(route('admin.coal-products.update', $product), $payload)->assertForbidden();
        $this->delete(route('admin.coal-products.destroy', $product))->assertForbidden();

        $this->actingAs($this->editor(['create']));
        $this->post(route('admin.coal-products.store'), ['name' => 'Create only', 'specifications' => $this->rows()])->assertRedirect(route('admin.dashboard'));
        $this->put(route('admin.coal-products.update', $product), $payload)->assertForbidden();
        $this->actingAs($this->editor(['edit']));
        $this->put(route('admin.coal-products.update', $product), ['name' => 'Edit allowed', 'specifications' => $this->rows()])->assertRedirect(route('admin.dashboard'));
        $this->post(route('admin.coal-products.store'), $payload)->assertForbidden();
        $this->delete(route('admin.coal-products.destroy', $product))->assertForbidden();
        $this->actingAs($this->editor(['delete']));
        $this->delete(route('admin.coal-products.destroy', $product))->assertRedirect(route('admin.dashboard'));
    }

    public function test_acl_seeder_is_additive_and_permissions_appear_in_the_role_matrix(): void
    {
        $admin = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->givePermissionTo('settings.view');
        $editor = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $this->seed(CoalProductPermissionsSeeder::class);
        $this->seed(CoalProductPermissionsSeeder::class);
        $this->assertTrue($admin->fresh()->hasPermissionTo('settings.view'));
        $this->assertTrue($admin->fresh()->hasPermissionTo('coal-products.delete'));
        $this->assertTrue($editor->fresh()->hasPermissionTo('coal-products.edit'));
        $this->assertFalse($editor->fresh()->hasPermissionTo('coal-products.delete'));
        $this->assertSame(4, Permission::where('name', 'like', 'coal-products.%')->count());
        $this->assertCount(4, (new RoleService)->getPermissionsGroupedByModule()['coal-products']);

        $super = User::factory()->create();
        $super->assignRole(Role::create(['name' => 'Administrator', 'guard_name' => 'web']));
        $this->actingAs($super)->get(route('admin.roles.edit', $admin))->assertOk()->assertSee('coal-products.create');
        $this->post(route('admin.coal-products.store'), ['name' => 'Superadmin Product', 'specifications' => $this->rows()])->assertRedirect(route('admin.coal-products.index'));
    }

    public function test_public_pages_navbar_and_sitemap_reflect_products_without_mixing_specifications(): void
    {
        $this->get(route('coal-products.index'))->assertOk()->assertSee('Coal product specifications will be available soon.');
        $first = $this->product();
        $second = CoalProduct::create(['name' => 'Premium Coal', 'specifications' => [['parameter' => 'Premium Parameter', 'typical' => '50%', 'rejection' => '-']]]);
        foreach (['en', 'id'] as $locale) {
            $url = route('coal-products.show', ['coalProduct' => $first->slug]);
            $this->withSession(['locale' => $locale])->get(route('home'))->assertOk()->assertSee('data-nav-item="coal-products"', false)
                ->assertSee($url, false)->assertSee(route('coal-products.show', ['coalProduct' => $second->slug]), false);
            $html = $this->get($url)->assertOk()->assertSee('Total Moisture')->assertSee('30%')->assertDontSee('Premium Parameter')->getContent();
            $this->assertStringContainsString('aria-current="page">CDE 4600 GAR', $html);
            $this->assertStringContainsString($locale === 'id' ? 'Spesifikasi Produk' : 'Product Specifications', $html);
        }
        Cache::forget('sitemap_xml');
        $this->get(route('sitemap'))->assertOk()->assertSee(route('coal-products.show', ['coalProduct' => $first->slug]), false);
        $first->delete();
        $this->get(route('home'))->assertOk()->assertDontSee('CDE 4600 GAR');
        $this->get(route('sitemap'))->assertOk()->assertDontSee('/coal-products/cde-4600-gar');
        $this->get('/coal-products/not-a-product')->assertNotFound();
    }

    public function test_names_and_values_are_escaped_and_duplicate_names_get_distinct_stable_urls(): void
    {
        $first = $this->product('Duplicate');
        $second = $this->product('Duplicate');
        $this->assertSame('duplicate', $first->slug);
        $this->assertSame('duplicate-2', $second->slug);
        $first->update(['name' => '<script>alert("name")</script>', 'specifications' => [['parameter' => '<script>alert("row")</script>', 'typical' => '<img src=x onerror=alert(1)>', 'rejection' => '-']]]);
        $this->get(route('coal-products.show', ['coalProduct' => $first->slug]))->assertOk()
            ->assertSee(e('<script>alert("row")</script>'), false)->assertDontSee('<script>alert("row")</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
        $this->assertSame('duplicate', $first->fresh()->slug);
        $this->actingAs($this->editor())->get(route('admin.coal-products.edit', $first))->assertOk()
            ->assertDontSee('<script>alert("row")</script>', false);
    }

    public function test_many_parameters_and_paginated_search_remain_complete(): void
    {
        $this->actingAs($this->editor());
        $rows = array_map(fn ($index) => ['parameter' => 'Parameter '.$index, 'typical' => $index.'%', 'rejection' => '-'], range(1, 50));
        $this->post(route('admin.coal-products.store'), ['name' => 'Searchable Product 00', 'specifications' => $rows])->assertRedirect();
        $this->assertSame($rows, CoalProduct::sole()->specifications);
        for ($index = 1; $index <= 13; $index++) {
            $this->product('Searchable Product '.str_pad((string) $index, 2, '0', STR_PAD_LEFT));
        }
        $this->product('Unrelated');
        $this->get(route('admin.coal-products.index', ['search' => 'Searchable', 'page' => 2]))->assertOk()
            ->assertViewHas('products', fn ($products) => $products->total() === 14 && $products->count() === 2)
            ->assertSee('Searchable Product 13')->assertDontSee('Unrelated');
    }
}
