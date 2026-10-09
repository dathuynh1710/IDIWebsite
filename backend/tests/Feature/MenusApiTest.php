<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MenusApiTest extends TestCase
{
    use RefreshDatabase;

    private function menu(string $location = 'main'): Menu
    {
        return Menu::create(['location' => $location, 'name' => $location]);
    }

    private function item(Menu $menu, array $data = []): MenuItem
    {
        return $menu->items()->create(array_merge(['label' => ['vi' => 'Việt', 'en' => 'English', 'zh' => '中文'], 'url' => '/news'], $data));
    }

    public function test_tree_is_ordered_scoped_and_hidden_branches_are_omitted(): void
    {
        $menu = $this->menu();
        $later = $this->item($menu, ['sort_order' => 9]);
        $root = $this->item($menu, ['sort_order' => 1]);
        $child = $this->item($menu, ['parent_id' => $root->id]);
        $leaf = $this->item($menu, ['parent_id' => $child->id]);
        $hidden = $this->item($menu, ['is_active' => false]);
        $this->item($menu, ['parent_id' => $hidden->id]);
        $this->item($this->menu('footer'));
        $this->getJson('/api/menus/main?locale=en')->assertOk()->assertJsonCount(2, 'items')
            ->assertJsonPath('items.0.id', $root->id)->assertJsonPath('items.1.id', $later->id)
            ->assertJsonPath('items.0.children.0.children.0.id', $leaf->id)->assertJsonPath('items.0.label', 'English');
        $menu->update(['is_active' => false]);
        $this->getJson('/api/menus/main')->assertOk()->assertJsonCount(0, 'items');
        $this->getJson('/api/menus/footer')->assertJsonCount(1, 'items');
        $this->getJson('/api/menus/missing')->assertNotFound();
    }

    public function test_page_ids_resolve_current_localized_paths_and_missing_locale_is_not_fabricated(): void
    {
        $menu = $this->menu();
        $page = Page::create(['template' => 'about', 'title' => ['vi' => 'Việt', 'en' => 'English', 'zh' => '中文'], 'slug' => ['vi' => 'vi-page', 'en' => 'en-page', 'zh' => 'zh-page'], 'is_active' => true]);
        $this->item($menu, ['label' => ['vi' => 'Fallback'], 'link_type' => 'page', 'page_id' => $page->id, 'url' => null]);
        foreach (['vi' => '/vi/gioi-thieu/vi-page', 'en' => '/en/about/en-page', 'zh-CN' => '/zh/guanyu/zh-page'] as $locale => $path) {
            $this->getJson('/api/menus/main?locale='.$locale)->assertOk()->assertJsonPath('items.0.href', $path)
                ->assertJsonPath('items.0.pageId', $page->id)->assertJsonPath('items.0.label', 'Fallback');
        }
        $page->setTranslation('slug', 'en', 'changed')->save();
        $this->getJson('/api/menus/main?locale=en')->assertJsonPath('items.0.href', '/en/about/changed');
        $page->forgetTranslation('slug', 'zh')->save();
        $this->getJson('/api/menus/main?locale=zh')->assertJsonPath('items.0.href', null);
        $page->update(['is_active' => false]);
        $this->getJson('/api/menus/main')->assertJsonCount(0, 'items');
    }

    public function test_unsafe_stored_urls_are_not_public_links_and_query_count_is_bounded(): void
    {
        $menu = $this->menu();
        $this->item($menu, ['link_type' => 'external', 'url' => 'javascript:alert(1)']);
        for ($i = 0; $i < 20; $i++) {
            $page = Page::create(['template' => 'about', 'title' => ['vi' => 'Page'], 'slug' => ['vi' => 'page-'.$i], 'is_active' => true]);
            $this->item($menu, ['link_type' => 'page', 'page_id' => $page->id]);
        }
        DB::enableQueryLog();
        $this->getJson('/api/menus/main')->assertOk()->assertJsonPath('items.0.href', null);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        $this->assertLessThanOrEqual(6, count($queries));
    }

    public function test_seed_creates_missing_positions_only_and_never_overwrites_admin_or_empty_menus(): void
    {
        $this->seed(MenuSeeder::class);
        $count = MenuItem::count();
        $item = MenuItem::firstOrFail();
        $item->update(['label' => ['vi' => 'Admin edit'], 'is_active' => false]);
        $this->seed(MenuSeeder::class);
        $this->assertSame($count, MenuItem::count());
        $this->assertSame('Admin edit', $item->fresh()->getTranslation('label', 'vi', false));
        $this->assertFalse($item->fresh()->is_active);
        $footer = Menu::where('location', 'footer')->firstOrFail();
        foreach ($footer->items()->orderByDesc('id')->get() as $node) {
            $node->delete();
        }
        $this->seed(MenuSeeder::class);
        $this->assertSame(0, $footer->items()->count());
    }
}
