@extends('layouts.minimal')
@section('title', 'Đăng nhập - '.config('admin.name'))
@section('content')
<main class="login-shell">
    <section class="login-brand">
        <div><span class="brand-mark">IDI</span><h1>IDI Seafood CMS</h1><p>Quản trị nội dung website an toàn, nhất quán và đa ngôn ngữ.</p></div>
    </section>
    <section class="login-panel">
        <form method="POST" action="{{ route('login.store') }}" class="login-form">
            @csrf
            <header><span class="brand-mark">IDI</span><h2>Chào mừng trở lại</h2><p>Đăng nhập để tiếp tục quản trị hệ thống.</p></header>
            <x-form.input name="username" label="Tên đăng nhập" type="text" autocomplete="username" autofocus required />
            <x-form.input name="password" label="Mật khẩu" type="password" autocomplete="current-password" required />
            <label class="remember"><input type="checkbox" name="remember" value="1"> Ghi nhớ đăng nhập</label>
            <x-ui.button type="submit">Đăng nhập</x-ui.button>
            <small>© {{ now()->year }} IDI Seafood. Hệ thống dành cho nhân sự được ủy quyền.</small>
        </form>
    </section>
</main>
@endsection
