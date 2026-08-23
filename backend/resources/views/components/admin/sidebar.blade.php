@php
    $notificationCounts = [
        'contacts' => Gate::allows('contacts.manage')
            ? \App\Models\ContactMessage::where('status', 'new')->count()
            : 0,
        'recruitment' => Gate::allows('recruitment.view')
            ? \App\Models\JobApplication::where('status', 'new')->count()
            : 0,
    ];

    $menu = collect(config('admin-menu'))->map(function (array $group) use ($notificationCounts): array {
        $group['items'] = collect($group['items'])->map(function (array $item) use ($notificationCounts): array {
            if (isset($item['notification']['key'])) {
                $item['notification']['count'] = $notificationCounts[$item['notification']['key']] ?? 0;
            }

            return $item;
        })->all();

        return $group;
    })->all();
@endphp
<aside class="admin-sidebar" aria-label="Điều hướng quản trị">
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" wire:navigate aria-label="IDI Seafood CMS">
            <img src="{{ asset('images/brand/idi-logo.png') }}" alt="Logo IDI Seafood">
            <span class="brand-text"><strong>IDI Seafood</strong><small>Content Management</small></span>
        </a>
        <button type="button" class="sidebar-close" @click="closeSidebar()" aria-label="Đóng menu">
            <x-ui.icon name="x" />
        </button>
    </div>

    <nav class="sidebar-nav" x-data="{ openMenu: null }">
        @foreach($menu as $group)
            @if($group['section'])
                <p class="sidebar-section">{{ $group['section'] }}</p>
            @endif
            <ul>
                @foreach($group['items'] as $item)
                    @if(!($item['hidden'] ?? false))
                        @if(empty($item['permission']))
                            <x-admin.sidebar-menu-item :item="$item" />
                        @else
                            @can($item['permission'])
                                <x-admin.sidebar-menu-item :item="$item" />
                            @endcan
                        @endif
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
