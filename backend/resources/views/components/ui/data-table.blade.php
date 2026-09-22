@props([
    'label' => 'Bảng dữ liệu',
    'maxHeight' => 'min(65vh, 720px)',
    'tableClass' => '',
])

<div {{ $attributes->class(['data-table-region']) }}>
    @isset($actions)
        <div class="data-table-actions">
            {{ $actions }}
        </div>
    @endisset

    <div
        class="data-table-scroll"
        role="region"
        aria-label="{{ $label }}"
        tabindex="0"
        style="--data-table-max-height: {{ $maxHeight }}"
    >
        @if(isset($columns) || isset($rows))
            <table @class(['data-table', $tableClass])>
                @isset($columns)
                    <thead><tr>{{ $columns }}</tr></thead>
                @endisset
                <tbody>{{ $rows ?? $slot }}</tbody>
            </table>
        @else
            {{ $slot }}
        @endif
    </div>

    @isset($footer)
        <div class="data-table-footer">
            {{ $footer }}
        </div>
    @endisset
</div>
