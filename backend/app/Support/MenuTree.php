<?php

namespace App\Support;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MenuTree
{
    // All mutations lock the menu row, serializing moves and deletes in one tree.
    public static function save(Menu $menu, ?int $id, array $data): MenuItem
    {
        return DB::transaction(function () use ($menu, $id, $data): MenuItem {
            Menu::whereKey($menu->id)->lockForUpdate()->firstOrFail();
            $items = $menu->items()->get()->keyBy('id');
            $item = $id ? $items->get($id) : new MenuItem(['menu_id' => $menu->id]);
            abort_unless($item, 404);
            $parentId = $data['parent_id'] ?? null;
            $visited = [];
            while ($parentId) {
                if ($parentId === $id || isset($visited[$parentId]) || ! $items->has($parentId)) {
                    throw ValidationException::withMessages(['parent_id' => 'Mục cha không hợp lệ hoặc tạo vòng lặp.']);
                }
                $visited[$parentId] = true;
                $parentId = $items[$parentId]->parent_id;
            }
            $item->fill($data)->save();

            return $item;
        });
    }

    public static function delete(Menu $menu, int $id): void
    {
        DB::transaction(function () use ($menu, $id): void {
            Menu::whereKey($menu->id)->lockForUpdate()->firstOrFail();
            $item = $menu->items()->findOrFail($id);
            if ($menu->items()->where('parent_id', $id)->exists()) {
                throw ValidationException::withMessages(['delete' => 'Hãy chuyển hoặc xóa các mục con trước khi xóa mục cha.']);
            }
            $item->delete();
        });
    }

    public static function rows(Collection $items, ?int $parentId = null, int $depth = 0, array $visited = []): array
    {
        $rows = [];
        foreach ($items->where('parent_id', $parentId) as $item) {
            if (isset($visited[$item->id])) {
                continue;
            }
            $visited[$item->id] = true;
            $rows[] = ['item' => $item, 'depth' => $depth];
            array_push($rows, ...self::rows($items, $item->id, $depth + 1, $visited));
        }

        return $rows;
    }
}
