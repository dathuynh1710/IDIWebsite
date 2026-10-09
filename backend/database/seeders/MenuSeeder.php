<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = json_decode(file_get_contents(__DIR__.'/data/menus.json'), true, flags: JSON_THROW_ON_ERROR);
        foreach ($defaults as $location => $nodes) {
            DB::transaction(function () use ($location, $nodes): void {
                // An existing position, including an intentionally empty menu, belongs to the admin.
                $menu = Menu::firstOrCreate(['location' => $location], ['name' => $location === 'main' ? 'Menu chính' : 'Footer']);
                if (! $menu->wasRecentlyCreated) {
                    return;
                }
                $this->insertNodes($menu, $nodes);
            });
        }
    }

    private function insertNodes(Menu $menu, array $nodes, ?int $parentId = null): void
    {
        $legacy = ['/about' => 'ABOUT_MESSAGE', '/about/story' => 'ABOUT_HISTORY', '/about/values' => 'ABOUT_VALUES'];
        foreach ($nodes as $order => $node) {
            $page = isset($legacy[$node['url']]) ? Page::about()->where('code', $legacy[$node['url']])->first() : null;
            $item = MenuItem::create([
                'menu_id' => $menu->id, 'parent_id' => $parentId, 'label' => $node['label'],
                'link_type' => $page ? 'page' : 'internal', 'page_id' => $page?->id,
                'url' => $page ? null : $node['url'], 'sort_order' => $order,
            ]);
            if (($node['key'] ?? null) === 'about') {
                $pages = Page::about()->orderBy('sort_order')->orderBy('id')->get();
                if ($pages->isNotEmpty()) {
                    foreach ($pages as $index => $about) {
                        MenuItem::create([
                            'menu_id' => $menu->id, 'parent_id' => $item->id, 'label' => $about->getTranslations('title'),
                            'link_type' => 'page', 'page_id' => $about->id, 'sort_order' => $index,
                            'is_active' => $about->is_active,
                        ]);
                    }

                    continue;
                }
            }
            if (($node['key'] ?? null) === 'products') {
                $mapping = [];
                $categories = ProductCategory::orderBy('sort_order')->orderBy('id')->get();
                foreach ($categories as $category) {
                    $mapping[$category->id] = MenuItem::create([
                        'menu_id' => $menu->id, 'parent_id' => $item->id, 'label' => $category->getTranslations('name'),
                        'link_type' => 'product_category', 'product_category_id' => $category->id,
                        'sort_order' => $category->sort_order ?? 0, 'is_active' => $category->is_active,
                    ]);
                }
                foreach ($categories as $category) {
                    if (isset($mapping[$category->parent_id])) {
                        $mapping[$category->id]->update(['parent_id' => $mapping[$category->parent_id]->id]);
                    }
                }
            }
            $this->insertNodes($menu, $node['children'] ?? [], $item->id);
        }
    }
}
