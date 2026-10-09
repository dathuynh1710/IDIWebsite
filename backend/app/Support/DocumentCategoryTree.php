<?php

namespace App\Support;

use Illuminate\Support\Collection;

class DocumentCategoryTree
{
    /** Attach display paths and flatten siblings in their configured order, without extra queries. */
    public static function build(Collection $categories, string $locale = 'vi'): Collection
    {
        $byId = $categories->keyBy('id');
        foreach ($categories as $category) {
            $names = [];
            $ancestors = [];
            $seen = [];
            $current = $category;
            while ($current && ! isset($seen[$current->id])) {
                $seen[$current->id] = true;
                array_unshift($names, $current->getTranslation('name', $locale, false)
                    ?: $current->getTranslation('name', 'vi', false) ?: '#'.$current->id);
                if ($current->id !== $category->id) {
                    array_unshift($ancestors, $current->id);
                }
                $current = $byId->get($current->parent_id);
            }
            $category->setAttribute('tree_path', implode(' → ', $names));
            $category->setAttribute('tree_parent_path', implode(' → ', array_slice($names, 0, -1)));
            $category->setAttribute('tree_ancestors', $ancestors);
        }
        $duplicates = $categories->countBy('tree_path');
        foreach ($categories as $category) {
            $category->setAttribute('tree_label', $category->tree_path
                .($duplicates[$category->tree_path] > 1 ? ' · #'.$category->id : ''));
        }

        $children = $categories->sort(fn ($a, $b) => [$b->sort_order, $a->id] <=> [$a->sort_order, $b->id])->groupBy('parent_id');
        $result = collect();
        $visited = [];
        $walk = function ($items, int $depth) use (&$walk, &$visited, $children, $result): void {
            foreach ($items as $item) {
                if (isset($visited[$item->id])) {
                    continue;
                }
                $visited[$item->id] = true;
                $item->setAttribute('tree_depth', $depth);
                $result->push($item);
                $walk($children->get($item->id, collect()), $depth + 1);
            }
        };
        $walk($categories->filter(fn ($item) => ! $byId->has($item->parent_id))
            ->sort(fn ($a, $b) => [$b->sort_order, $a->id] <=> [$a->sort_order, $b->id]), 0);
        // Keep malformed legacy cycles visible and never recurse indefinitely.
        $walk($categories, 0);

        return $result;
    }
}
