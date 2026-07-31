<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('admin.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body x-data="adminLayout()" :class="{ 'sidebar-open': sidebarOpen, 'sidebar-collapsed': sidebarCollapsed }">
    <a class="skip-link" href="#main-content">Chuyển đến nội dung chính</a>
    <div class="admin-shell">
        @persist('admin-sidebar')
            <x-admin.sidebar />
        @endpersist
        <div class="admin-main">
            @persist('admin-header')
                <x-admin.header />
            @endpersist
            <main id="main-content" class="admin-content" tabindex="-1">
                {{ $slot }}
            </main>
            @persist('admin-footer')
                <x-admin.footer />
            @endpersist
        </div>
    </div>
    @persist('admin-mobile-overlay')
        <x-admin.mobile-overlay />
    @endpersist

    <div class="admin-loading-overlay" x-data="{ show: false }"
        x-on:livewire:navigating.window="show = true"
        x-on:livewire:navigated.window="show = false"
        x-show="show"
        x-transition.opacity
        x-cloak
        aria-live="polite"
        aria-label="Đang xử lý">
        <x-ui.loading-spinner />
    </div>
    <div class="admin-toast-region"
        x-data="{ show: false, message: '', type: 'success', timer: null }"
        x-on:admin-toast.window="
            message = $event.detail.message;
            type = $event.detail.type || 'success';
            show = true;
            clearTimeout(timer);
            timer = setTimeout(() => show = false, 3500)
        "
        aria-live="polite">
        <div class="flash-message" :class="'flash-' + type" x-show="show" x-transition x-cloak>
            <span x-text="message"></span>
            <button type="button" class="icon-button" x-on:click="show = false" aria-label="Đóng"><x-ui.icon name="x" size="16" /></button>
        </div>
    </div>
    @livewireScriptConfig
</body>
</html>
