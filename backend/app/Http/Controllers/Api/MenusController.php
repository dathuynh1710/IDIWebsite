<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Support\AboutPageRoutes;
use App\Support\Locale;
use App\Support\MenuUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenusController extends Controller
{
    public function show(Request $request, string $location): JsonResponse
    {
        $menu = Menu::where('location', $location)->firstOrFail();
        $locale = Locale::fromRequest($request);
        $items = $menu->is_active
            ? $menu->items()->where('is_active', true)->with(['page' => fn ($query) => $query->about(), 'productCategory'])->orderBy('sort_order')->orderBy('id')->get()
            : collect();
        $enabled = DB::table('modules')->where('code', 'about')->value('is_active');
        $aboutEnabled = $enabled === null || (bool) $enabled;
        $groups = $items->groupBy(fn (MenuItem $item) => $item->parent_id ?? 0);
        $build = function (int $parentId, array $visited = []) use (&$build, $groups, $locale, $aboutEnabled): array {
            $nodes = [];
            foreach ($groups->get($parentId, []) as $item) {
                if (isset($visited[$item->id])) {
                    continue;
                }
                $visited[$item->id] = true;
                $href = null;
                $paths = [];
                if ($item->link_type === 'page') {
                    if (! $aboutEnabled || ! $item->page?->is_active) {
                        continue;
                    }
                    $paths = AboutPageRoutes::paths($item->page);
                    $href = $paths[$locale] ?? null;
                } elseif ($item->link_type === 'product_category') {
                    $category = $item->productCategory;
                    if (! $category?->is_active) {
                        continue;
                    }
                    $slug = $category->getTranslation('slug', $locale, false) ?: $category->getTranslation('slug', 'vi', false);
                    $href = $slug ? '/products?category='.rawurlencode($slug) : null;
                } elseif (MenuUrl::valid($item->url, $item->link_type)) {
                    $href = $item->url;
                }
                $nodes[] = [
                    'id' => $item->id,
                    'label' => $item->getTranslation('label', $locale, false) ?: $item->getTranslation('label', 'vi', false),
                    'href' => $href,
                    'external' => $item->link_type === 'external',
                    'pageId' => $item->page_id,
                    'localizedPaths' => (object) $paths,
                    'children' => $build($item->id, $visited),
                ];
            }

            return $nodes;
        };

        return response()->json(['location' => $location, 'locale' => $locale, 'items' => $build(0)]);
    }
}
