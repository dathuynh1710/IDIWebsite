@extends('layouts.minimal')
@section('title', 'Đăng nhập - ' . config('admin.name'))
@section('content')
    <main class="login-shell">
        <section class="login-brand" aria-labelledby="login-brand-title"
            style="--login-brand-image: url('{{ asset('assets/images/auth/saomai-group-facility.jpg') }}')">
            <div class="login-brand-content">
                <div class="login-brand-identity">
                    <img class="login-brand-logo" src="{{ asset('images/brand/idi-logo.png') }}" alt="Logo IDI Seafood">
                    <span class="login-brand-identity-copy">
                        <span>Hệ thống quản trị website</span>
                        <strong id="login-brand-title">IDI Seafood CMS</strong>
                    </span>
                </div>
            </div>
        </section>
        <section class="login-panel">
            <form method="POST" action="{{ route('login.store') }}" class="login-form">
                @csrf
                <header>
                    <span class="login-form-logo-wrap">
                        <img class="login-form-logo" src="{{ asset('images/brand/idi-logo.png') }}" alt="Logo IDI Seafood">
                        <span class="login-form-identity-copy">
                            <span>Hệ thống quản trị website</span>
                            <strong>IDI Seafood CMS</strong>
                        </span>
                    </span>
                    <h2>Chào mừng trở lại</h2>
                    <p>Đăng nhập để tiếp tục quản trị hệ thống.</p>
                </header>
                <div class="login-field">
                    <label for="username">Tên đăng nhập</label>
                    <div class="login-input-control @error('username') is-invalid @enderror">
                        <x-ui.icon name="user" size="21" />
                        <input id="username" name="username" type="text" value="{{ old('username') }}"
                            placeholder="Nhập tên đăng nhập" autocomplete="username" autofocus required
                            aria-required="true" @error('username') aria-invalid="true" aria-describedby="username-error" @enderror>
                    </div>
                    <x-form.field-error name="username" />
                </div>
                <div class="login-field" x-data="{ showPassword: false }">
                    <label for="password">Mật khẩu</label>
                    <div class="login-input-control @error('password') is-invalid @enderror">
                        <x-ui.icon name="shield" size="21" />
                        <input id="password" name="password" type="password" :type="showPassword ? 'text' : 'password'"
                            placeholder="Nhập mật khẩu" autocomplete="current-password" required aria-required="true"
                            @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                        <button type="button" class="login-password-toggle" @click="showPassword = !showPassword"
                            :aria-pressed="showPassword" :aria-label="showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'"
                            :title="showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'">
                            <span x-show="!showPassword"><x-ui.icon name="eye" size="21" /></span>
                            <span x-show="showPassword" x-cloak><x-ui.icon name="eye-off" size="21" /></span>
                        </button>
                    </div>
                    <x-form.field-error name="password" />
                </div>
                <label class="login-remember">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    <span class="login-switch-track" aria-hidden="true"><span></span></span>
                    <span>Ghi nhớ đăng nhập</span>
                </label>
                <x-ui.button type="submit" class="login-submit">Đăng nhập</x-ui.button>
                <small>© {{ now()->year }} IDISEAFOOD VIETNAM SOLUTION.
                </small>
            </form>
        </section>
    </main>
@endsection
