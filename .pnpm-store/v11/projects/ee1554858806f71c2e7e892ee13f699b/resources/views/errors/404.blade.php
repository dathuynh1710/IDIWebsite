@extends('layouts.minimal')
@section('title', 'Không tìm thấy trang')
@section('content')
<main class="error-page"><x-ui.icon name="search" size="48" /><h1>404</h1><h2>Không tìm thấy trang</h2><p>Đường dẫn có thể đã thay đổi hoặc không còn tồn tại.</p><a class="button button-primary" href="{{ auth()->check() ? route('admin.dashboard') : route('login') }}">Về trang chính</a></main>
@endsection
