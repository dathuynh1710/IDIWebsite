<div class="dashboard-page">
    <x-admin.page-header title="Bảng điều khiển" />

    @if($stats->isNotEmpty())
        <section class="dashboard-stat-grid" aria-label="Chỉ số cần chú ý">
            @foreach($stats as $stat)
                @if($stat['route'])<a href="{{ route($stat['route']) }}" wire:navigate class="dashboard-stat-card is-{{ $stat['tone'] }}">@else<article class="dashboard-stat-card is-{{ $stat['tone'] }}">@endif
                    <span class="dashboard-stat-icon"><x-ui.icon :name="$stat['icon']" size="22" /></span>
                    <div class="dashboard-stat-copy"><small>{{ $stat['label'] }}</small><strong>{{ number_format($stat['value']) }}</strong><span>{{ $stat['hint'] }}</span></div>
                    @if($stat['route'])<x-ui.icon class="dashboard-stat-arrow" name="chevron-right" size="18" />@endif
                @if($stat['route'])</a>@else</article>@endif
            @endforeach
        </section>
    @endif

    <div class="dashboard-main-grid">
        <section class="card dashboard-panel dashboard-recent-panel">
            <header class="dashboard-panel-header">
                <div><p class="dashboard-eyebrow">Nội dung</p><h2>Cập nhật gần đây</h2></div>
                <span>{{ $recentItems->count() }} mục mới nhất</span>
            </header>
            @if($recentItems->isEmpty())
                <x-ui.empty-state title="Chưa có nội dung cập nhật" description="Nội dung bạn có quyền quản lý sẽ xuất hiện tại đây." />
            @else
                <div class="dashboard-recent-list">
                    @foreach($recentItems as $item)
                        <a href="{{ $item['url'] }}" wire:navigate>
                            <span class="dashboard-item-icon"><x-ui.icon :name="$item['icon']" size="19" /></span>
                            <span class="dashboard-item-copy"><strong>{{ $item['title'] }}</strong><small>{{ $item['module'] }} <i aria-hidden="true"></i> {{ $item['active'] ? 'Đang hiển thị' : 'Đang ẩn' }}</small></span>
                            <time datetime="{{ $item['updated_at']->toIso8601String() }}">{{ $item['updated_at']->diffForHumans() }}</time>
                            <x-ui.icon class="dashboard-item-arrow" name="chevron-right" size="17" />
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <aside class="dashboard-side-stack">
            <section class="card dashboard-panel">
                <header class="dashboard-panel-header"><div><p class="dashboard-eyebrow">Lối tắt</p><h2>Thao tác nhanh</h2></div></header>
                @if($quickActions->isEmpty())
                    <p class="dashboard-panel-empty">Chưa có thao tác phù hợp với quyền hiện tại.</p>
                @else
                    <div class="dashboard-quick-actions">
                        @foreach($quickActions as $action)
                            <a href="{{ route($action['route']) }}" wire:navigate>
                                <span><x-ui.icon :name="$action['icon']" size="19" /></span>
                                <span><strong>{{ $action['label'] }}</strong><small>{{ $action['description'] }}</small></span>
                                <x-ui.icon name="chevron-right" size="17" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        </aside>
    </div>

    @if($moduleProgress->isNotEmpty() || $recentActivities->isNotEmpty())
        <div class="dashboard-bottom-grid {{ $recentActivities->isEmpty() ? 'is-single' : '' }}">
            @if($moduleProgress->isNotEmpty())
                <section class="card dashboard-panel">
                    <header class="dashboard-panel-header"><div><p class="dashboard-eyebrow">Xuất bản</p><h2>Tình trạng nội dung</h2></div></header>
                    <div class="dashboard-progress-list">
                        @foreach($moduleProgress as $module)
                            <a href="{{ route($module['route']) }}" wire:navigate>
                                <span class="dashboard-progress-icon"><x-ui.icon :name="$module['icon']" size="18" /></span>
                                <span class="dashboard-progress-copy"><span><strong>{{ $module['label'] }}</strong><small>{{ number_format($module['active']) }}/{{ number_format($module['total']) }} đang hiển thị</small></span><i><b style="width: {{ $module['percent'] }}%"></b></i></span>
                                <strong>{{ $module['percent'] }}%</strong>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($recentActivities->isNotEmpty())
                <section class="card dashboard-panel">
                    <header class="dashboard-panel-header">
                        <div><p class="dashboard-eyebrow">Hệ thống</p><h2>Hoạt động gần đây</h2></div>
                        <a href="{{ route('admin.activity-logs.index') }}" wire:navigate>Xem tất cả <x-ui.icon name="chevron-right" size="15" /></a>
                    </header>
                    <div class="dashboard-activity-list">
                        @foreach($recentActivities as $activity)
                            <div>
                                <span>{{ mb_strtoupper(mb_substr($activity->causer?->name ?? '?', 0, 1)) }}</span>
                                <p><strong>{{ $activity->causer?->name ?? 'Tài khoản đã xóa' }}</strong> {{ $activity->description }}<small>{{ $activity->created_at->diffForHumans() }}</small></p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    @endif
</div>
