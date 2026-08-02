@props(['variant' => 'primary', 'type' => 'button', 'icon' => null])
<button type="{{ $type }}" {{ $attributes->class(['button', "button-{$variant}"]) }}>
    @if($icon)<x-ui.icon :name="$icon" size="18" />@endif
    {{ $slot }}
</button>
