@props([
    'id',
    'selected' => [],
    'label' => 'Chọn dòng',
    'model' => 'selected',
    'keyPrefix' => 'table',
])

@php
    $rowId = (int) $id;
    $isSelected = collect($selected)->map(fn ($value) => (int) $value)->contains($rowId);
@endphp

<input
    {{ $attributes->class(['table-checkbox']) }}
    type="checkbox"
    wire:key="{{ $keyPrefix }}-{{ $model }}-row-{{ $rowId }}-{{ $isSelected ? 'selected' : 'clear' }}"
    wire:model.live="{{ $model }}"
    value="{{ $rowId }}"
    @checked($isSelected)
    aria-label="{{ $label }}"
>
