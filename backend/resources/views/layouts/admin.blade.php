<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('admin.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon/favicon-96x96.png') }}" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <meta name="apple-mobile-web-app-title" content="IDISeafood">
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body x-data="adminLayout()" :class="{ 'sidebar-open': sidebarOpen, 'sidebar-collapsed': sidebarCollapsed }">
    <a class="skip-link" href="#main-content">Chuyển đến nội dung chính</a>
    <div class="admin-shell">
        <x-admin.sidebar />
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
    <x-admin.toast />
    @livewireScriptConfig
</body>
</html>
