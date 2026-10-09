<?php

namespace Tests\Feature;

use App\Livewire\Admin\Menus\Form;
use App\Livewire\Admin\Menus\Index;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    use RefreshDatabase;

    private function editor(): User
    {
        Permission::findOrCreate('settings.manage', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('settings.manage');

        return $user;
    }

    private function menu(string $location = 'main'): Menu
    {
        return Menu::create(['location' => $location, 'name' => $location]);
    }

    private function item(Menu $menu, ?MenuItem $parent = null): MenuItem
    {
        return $menu->items()->create(['parent_id' => $parent?->id, 'label' => ['vi' => 'Mục'], 'url' => '/about']);
    }

    public function test_admin_screens_and_actions_require_settings_permission(): void
    {
        $menu = $this->menu();
        $this->get('/admin/menus')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/menus')->assertForbidden();
        $this->get("/admin/menus/{$menu->id}/items/create")->assertForbidden();
        Livewire::test(Index::class)->assertForbidden();
        $this->actingAs($this->editor())->get('/admin/menus')->assertOk()->assertSee('Quản lý menu')
            ->assertSee('href="'.route('admin.menus.index').'"', false);
        $this->get("/admin/menus/{$menu->id}/items/create")->assertOk()->assertSee('English')->assertSee('中文');
    }

    public function test_can_create_position_and_multilevel_items_then_move_reorder_and_reload(): void
    {
        $user = $this->editor();
        Livewire::actingAs($user)->test(Index::class)->set('name', 'Menu chính')->set('location', 'main')->call('createMenu')->assertHasNoErrors();
        $menu = Menu::firstOrFail();
        $root = $this->item($menu);
        $other = $this->item($menu);
        $child = $this->item($menu, $root);
        Livewire::test(Form::class, ['menu' => $menu])
            ->set('label', ['vi' => 'Mới', 'en' => 'New', 'zh' => '新'])
            ->set('parent_id', $child->id)->set('url', '/about/story')->set('sort_order', 20)
            ->call('save')->assertHasNoErrors();
        $leaf = MenuItem::latest('id')->firstOrFail();
        $this->assertSame($child->id, $leaf->parent_id);
        Livewire::test(Form::class, ['menu' => $menu, 'item' => $leaf])
            ->set('parent_id', $other->id)->set('sort_order', 1)->set('label.en', 'Updated')
            ->call('save')->assertHasNoErrors();
        Livewire::test(Form::class, ['menu' => $menu, 'item' => $leaf->fresh()])
            ->assertSet('parent_id', $other->id)->assertSet('sort_order', 1)->assertSet('label.en', 'Updated');
        Livewire::test(Index::class, ['menuId' => $menu->id])->call('toggleVisibility', $leaf->id)->assertHasNoErrors();
        $this->assertFalse($leaf->fresh()->is_active);
        Livewire::test(Index::class, ['menuId' => $menu->id])->call('toggleVisibility', $leaf->id)->call('toggleMenu')->assertHasNoErrors();
        $this->assertTrue($leaf->fresh()->is_active);
        $this->assertFalse($menu->fresh()->is_active);
    }

    public function test_rejects_self_descendant_and_cross_menu_parents(): void
    {
        $menu = $this->menu();
        $root = $this->item($menu);
        $child = $this->item($menu, $root);
        $leaf = $this->item($menu, $child);
        $foreign = $this->item($this->menu('footer'));
        foreach ([$root->id, $leaf->id, $foreign->id, 99999] as $parentId) {
            Livewire::actingAs($this->editor())->test(Form::class, ['menu' => $menu, 'item' => $root])
                ->set('parent_id', $parentId)->call('save')->assertHasErrors('parent_id');
        }
        $this->assertNull($root->fresh()->parent_id);
        Livewire::test(Form::class, ['menu' => $menu, 'item' => $foreign])->assertNotFound();
        $this->expectException(ModelNotFoundException::class);
        Livewire::test(Index::class, ['menuId' => $menu->id])->call('toggleVisibility', $foreign->id);
    }

    public function test_delete_is_confirmed_and_parent_children_are_never_silently_removed(): void
    {
        $menu = $this->menu();
        $root = $this->item($menu);
        $child = $this->item($menu, $root);
        $component = Livewire::actingAs($this->editor())->test(Index::class, ['menuId' => $menu->id]);
        $component->call('requestDelete', $root->id)->assertSee('Xóa mục menu #'.$root->id);
        $this->assertModelExists($root);
        $component->call('confirmDelete')->assertHasErrors('delete');
        $this->assertModelExists($child);
        $component->call('cancelDelete')->call('requestDelete', $child->id)->call('confirmDelete')->assertHasNoErrors();
        $this->assertModelMissing($child);
        $component->call('requestDelete', $root->id)->call('confirmDelete')->assertHasNoErrors();
        $this->assertModelMissing($root);
    }

    public function test_link_validation_and_required_labels(): void
    {
        $menu = $this->menu();
        foreach (['javascript:alert(1)', 'data:text/html,bad', '//outside.test', '/\\outside.test', 'https://'] as $url) {
            Livewire::actingAs($this->editor())->test(Form::class, ['menu' => $menu])
                ->set('label.vi', 'Link')->set('url', $url)->call('save')->assertHasErrors('url');
        }
        Livewire::test(Form::class, ['menu' => $menu])->set('url', '/news')->call('save')->assertHasErrors('label.vi');
        Livewire::test(Form::class, ['menu' => $menu])->set('label.vi', 'Link')->set('link_type', 'external')->set('url', 'javascript:alert(1)')->call('save')->assertHasErrors('url');
        Livewire::test(Form::class, ['menu' => $menu])->set('label.vi', 'Link')->set('link_type', 'external')->set('url', 'https://example.com/path')->call('save')->assertHasNoErrors();
        Livewire::test(Form::class, ['menu' => $menu])->set('label.vi', 'Link')->set('link_type', 'page')->set('page_id', 999)->call('save')->assertHasErrors('page_id');
        $this->assertSame(1, $menu->items()->count());
    }
}
