<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('admin.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body x-data="adminLayout()" :class="{ 'sidebar-open': sidebarOpen, 'sidebar-collapsed': sidebarCollapsed }">
    <a class="skip-link" href="#main-content">Chuyển đến nội dung chính</a>
    <div class="admin-shell">
        <x-admin.sidebar />
        <div class="admin-main">
            <x-admin.header />
            <main id="main-content" class="admin-content" tabindex="-1">
                <x-admin.flash-message />
                @yield('content')
            </main>
            <x-admin.footer />
        </div>
    </div>
    <x-admin.mobile-overlay />
    @stack('scripts')
</body>
</html>
