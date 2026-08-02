<aside class="admin-sidebar" aria-label="Điều hướng quản trị">
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" wire:navigate aria-label="IDI Seafood CMS">
            <img src="{{ asset('images/idi-logo.svg') }}" alt="IDI Seafood">
            <span class="brand-text"><strong>IDI Seafood</strong><small>Content Management</small></span>
        </a>
        <button type="button" class="sidebar-close" @click="closeSidebar()" aria-label="Đóng menu">
            <x-ui.icon name="x" />
        </button>
    </div>

    <nav class="sidebar-nav">
        @foreach(config('admin-menu') as $group)
            @if($group['section'])
                <p class="sidebar-section">{{ $group['section'] }}</p>
            @endif
            <ul>
                @foreach($group['items'] as $item)
                    @if(empty($item['permission']))
                        <x-admin.sidebar-menu-item :item="$item" />
                    @else
                        @can($item['permission'])
                            <x-admin.sidebar-menu-item :item="$item" />
                        @endcan
                    @endif
                @endforeach
            </ul>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <span class="sidebar-status"></span>
        <span class="brand-text">Hệ thống hoạt động ổn định</span>
    </div>
</aside>
