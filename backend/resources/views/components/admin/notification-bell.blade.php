@props(['count' => 0, 'href'])
@php
    $count = max(0, (int) $count);
    $displayCount = $count > 99 ? '99+' : (string) $count;
@endphp
<a href="{{ $href }}"
    wire:navigate
    class="sidebar-notification-bell"
    title="Thông báo"
    aria-label="{{ $count > 0 ? "Thông báo: {$count} mục mới" : 'Thông báo' }}">
    <x-ui.icon name="bell" :size="18" />
    @if($count > 0)
        <span class="sidebar-notification-badge" aria-hidden="true">{{ $displayCount }}</span>
    @endif
</a>
