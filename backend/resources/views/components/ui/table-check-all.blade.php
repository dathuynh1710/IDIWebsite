@props([
    'ids' => [],
    'selected' => [],
    'label' => 'Chọn tất cả dòng trên trang này',
])

@php
    $pageIds = collect($ids)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
    $selectedIds = collect($selected)->map(fn ($id) => (int) $id)->filter()->unique();
    $selectedOnPage = collect($pageIds)->filter(fn ($id) => $selectedIds->contains($id))->count();
    $allPageSelected = $pageIds !== [] && $selectedOnPage === count($pageIds);
    $somePageSelected = $selectedOnPage > 0 && ! $allPageSelected;
    $selectionState = $allPageSelected ? 'all' : ($somePageSelected ? 'some' : 'none');
@endphp

<input
    {{ $attributes->class(['table-checkbox']) }}
    type="checkbox"
    wire:key="page-selection-{{ md5(implode(',', $pageIds)) }}-{{ $selectionState }}"
    wire:click="toggleRenderedPageSelection(@js($pageIds))"
    @checked($allPageSelected)
    @disabled($pageIds === [])
    x-data
    x-init="$el.checked = @js($allPageSelected); $el.indeterminate = @js($somePageSelected)"
    x-effect="$el.checked = @js($allPageSelected); $el.indeterminate = @js($somePageSelected)"
    aria-label="{{ $label }}"
    title="{{ $label }}"
>
