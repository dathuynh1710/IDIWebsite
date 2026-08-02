@props(['item'])
@php
    $hasChildren = !empty($item['children']);
    $activePattern = $item['active'] ?? ($item['route'] ?? '');
    $isActive = $activePattern && request()->routeIs($activePattern);
    $href = isset($item['route']) ? route($item['route']) : ($item['url'] ?? '#');
@endphp
<li @if($hasChildren) x-data="{ open: {{ $isActive ? 'true' : 'false' }} }" @endif>
    @if($hasChildren)
        <button type="button" class="sidebar-link {{ $isActive ? 'is-active' : '' }}" @click="open = !open" :aria-expanded="open.toString()">
            <x-ui.icon :name="$item['icon'] ?? 'info'" />
            <span class="sidebar-label">{{ $item['label'] }}</span>
            <x-ui.icon name="chevron-down" class="sidebar-chevron" x-bind:class="{ 'is-open': open }" />
        </button>
        <ul class="sidebar-submenu" x-show="open" x-transition x-cloak>
            @foreach($item['children'] as $child)
                <li>
                    <a href="{{ route($child['route']) }}" wire:navigate class="{{ request()->routeIs($child['route']) ? 'is-active' : '' }}">
                        <span></span>{{ $child['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <a href="{{ $href }}" wire:navigate class="sidebar-link {{ $isActive ? 'is-active' : '' }}">
            <x-ui.icon :name="$item['icon'] ?? 'info'" />
            <span class="sidebar-label">{{ $item['label'] }}</span>
        </a>
    @endif
</li>
