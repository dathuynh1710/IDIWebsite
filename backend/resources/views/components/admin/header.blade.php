<header class="admin-header">
    <div class="header-left">
        <button type="button" class="icon-button mobile-menu-button" @click="openSidebar()" aria-label="Mở menu">
            <x-ui.icon name="menu" />
        </button>
        <button type="button" class="icon-button desktop-collapse-button" @click="toggleSidebar()" aria-label="Thu gọn menu">
            <x-ui.icon name="menu" />
        </button>
        <div class="header-context">
            <span>Hệ thống quản trị nội dung</span>
            <strong>@yield('page-context', 'IDI Seafood')</strong>
        </div>
    </div>
    <div class="header-user" x-data="{ open: false }" @keydown.escape.window="open = false">
        <button type="button" class="user-trigger" @click="open = !open" @click.outside="open = false" :aria-expanded="open.toString()">
            <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <span class="user-copy">
                <strong>{{ auth()->user()->name }}</strong>
                <small>Quản trị viên</small>
            </span>
            <x-ui.icon name="chevron-down" size="16" />
        </button>
        <div class="user-dropdown" x-show="open" x-transition x-cloak>
            <a href="#"><x-ui.icon name="user" size="18" /> Hồ sơ</a>
            <a href="#"><x-ui.icon name="settings" size="18" /> Đổi mật khẩu</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"><x-ui.icon name="log-out" size="18" /> Đăng xuất</button>
            </form>
        </div>
    </div>
</header>
