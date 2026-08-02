@php
    $types = ['success', 'error', 'warning', 'info'];
    $flash = collect($types)->first(fn ($type) => session()->has($type));
@endphp
@if($flash)
    <div class="flash flash-{{ $flash }}" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" role="{{ $flash === 'error' ? 'alert' : 'status' }}">
        <x-ui.icon :name="$flash === 'success' ? 'check' : ($flash === 'error' ? 'alert' : 'info')" />
        <span>{{ session($flash) }}</span>
        <button type="button" @click="show = false" aria-label="Đóng thông báo"><x-ui.icon name="x" size="18" /></button>
    </div>
@endif
