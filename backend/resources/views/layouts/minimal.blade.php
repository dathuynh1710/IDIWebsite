<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('admin.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="minimal-body">@yield('content')</body>
</html>
