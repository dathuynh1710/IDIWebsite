@props(['item'])
@php
    $hasChildren = !empty($item['children']);
    $activePattern = $item['active'] ?? ($item['route'] ?? '');
    $isActive = $activePattern && request()->routeIs($activePattern);
    $href = isset($item['route']) ? route($item['route']) : ($item['url'] ?? '#');
    $menuKey = $item['key'] ?? $activePattern ?: \Illuminate\Support\Str::slug($item['label']);
@endphp
<li @if($hasChildren && $isActive) x-init="openMenu = @js($menuKey)" @endif>
    @if($hasChildren)
        <button type="button" class="sidebar-link {{ $isActive ? 'is-active' : '' }}"
            @click="openMenu = openMenu === @js($menuKey) ? null : @js($menuKey)"
            x-bind:class="{ 'is-expanded': openMenu === @js($menuKey) }"
            :aria-expanded="(openMenu === @js($menuKey)).toString()">
            <x-ui.icon :name="$item['icon'] ?? 'info'" />
            <span class="sidebar-label">{{ $item['label'] }}</span>
            <x-ui.icon name="chevron-down" class="sidebar-chevron" />
        </button>
        <ul class="sidebar-submenu" x-show="openMenu === @js($menuKey)" x-collapse.duration.220ms x-cloak>
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
