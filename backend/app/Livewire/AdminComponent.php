<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithToasts;
use Livewire\Component;

abstract class AdminComponent extends Component
{
    use InteractsWithToasts;

    /**
     * Toggle every selectable row on the current paginator page.
     *
     * Tables pass only their rendered IDs, so selection remains scoped to the
     * current page and existing selections on other pages are preserved.
     */
    public function toggleRenderedPageSelection(array $ids): void
    {
        if (! property_exists($this, 'selected')) {
            return;
        }

        $pageIds = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();
        $selectedIds = collect($this->selected)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique();
        $allPageSelected = $pageIds->isNotEmpty() && $pageIds->every(
            fn (int $id) => $selectedIds->contains($id)
        );

        $this->selected = ($allPageSelected
            ? $selectedIds->diff($pageIds)
            : $selectedIds->merge($pageIds))
            ->unique()
            ->values()
            ->all();
    }
}
