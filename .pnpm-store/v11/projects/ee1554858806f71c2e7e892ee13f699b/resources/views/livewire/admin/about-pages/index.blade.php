<div>
    <x-admin.page-header title="Quản lý giới thiệu" description="Quản lý nội dung giới thiệu đa ngôn ngữ của website" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @can('pages.create')
                <a class="button button-primary" href="{{ route('admin.about-pages.create') }}" wire:navigate><x-ui.icon name="plus" size="18" /> Thêm giới thiệu</a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <nav class="locale-filter card" aria-label="Ngôn ngữ nội dung">
        <span>Ngôn ngữ đang xem:</span>
        @foreach(['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'] as $code => $label)
            <button type="button" wire:click="$set('locale', '{{ $code }}')" class="{{ $locale === $code ? 'is-active' : '' }}"><span>{{ strtoupper($code) }}</span>{{ $label }}</button>
        @endforeach
    </nav>

    <section class="filter-card card">
        <div class="filter-grid about-filter-grid">
            <div class="filter-search"><label for="about-search">Tìm kiếm</label><div><x-ui.icon name="search" size="18" /><input id="about-search" class="input" wire:model.live.debounce.300ms="search" placeholder="Tiêu đề, mã hoặc đường dẫn"></div></div>
            <div><label for="about-template">Loại giới thiệu</label><select id="about-template" class="select" wire:model.live="template"><option value="">Tất cả loại</option>@foreach($templates as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
            <div><label for="about-status">Trạng thái</label><select id="about-status" class="select" wire:model.live="status"><option value="">Tất cả</option><option value="active">Đang hiển thị</option><option value="hidden">Đang ẩn</option><option value="trashed">Thùng rác</option></select></div>
            <div class="filter-actions"><button class="button button-ghost" type="button" wire:click="resetFilters">Đặt lại</button></div>
        </div>
    </section>

    <section class="card category-list-card">
        @if($pages->isEmpty())
            <x-ui.empty-state title="Không tìm thấy nội dung giới thiệu" description="Thử thay đổi bộ lọc hoặc thêm nội dung mới." />
        @else
            <div class="category-toolbar">
                <div class="category-toolbar-actions">
                    <button class="button button-success" type="button" wire:click="bulk('show')" @disabled($selected === [])>Hiện</button>
                    <button class="button button-secondary" type="button" wire:click="bulk('hide')" @disabled($selected === [])>Ẩn</button>
                    <button class="button button-secondary" type="button" wire:click="bulk('reorder')" @disabled($selected === [])>Cập nhật thứ tự</button>
                    <button class="button button-danger" type="button" wire:click="bulk('delete')" wire:confirm="Chuyển các nội dung đã chọn vào thùng rác?" @disabled($selected === [])>Xóa</button>
                </div>
                <span class="category-selection-count">Đã chọn {{ count($selected) }} / {{ $pages->total() }} nội dung</span>
            </div>
            <div class="table-responsive">
                <table class="data-table category-table">
                    <thead><tr><th class="selection-column"></th><th class="order-column">Thứ tự</th><th>Tiêu đề ({{ strtoupper($locale) }})</th><th>Đường dẫn</th><th>Loại giới thiệu</th><th>Bản dịch</th><th>Hiển thị</th><th class="table-actions-heading">Thao tác</th></tr></thead>
                    <tbody>
                        @foreach($pages as $item)
                            @php
                                $translations = $item->getTranslations('title');
                                $statuses = $item->getTranslations('translation_status');
                                $isTrashed = $item->trashed();
                            @endphp
                            <tr wire:key="about-page-{{ $item->id }}" @class(['is-muted-row' => !$item->is_active || $isTrashed])>
                                <td><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $item->id }}" aria-label="Chọn {{ $item->getTranslation('title', $locale, false) }}"></td>
                                <td><input class="input order-input" type="number" min="0" wire:model="sortOrders.{{ $item->id }}" aria-label="Thứ tự {{ $item->id }}"></td>
                                <td class="category-name-cell"><strong>{{ $item->getTranslation('title', $locale, false) ?: 'Chưa có bản dịch' }}</strong><small>{{ $item->code ?: '#'.$item->id }} @if($item->parent) · Thuộc {{ $item->parent->getTranslation('title', $locale, false) }} @endif</small></td>
                                <td><code>{{ $item->getTranslation('slug', $locale, false) ?: '—' }}</code></td>
                                <td>{{ $templates[$item->template] ?? 'Giới thiệu' }}</td>
                                <td><div class="translation-dots">@foreach(['vi','en','zh'] as $code)<span class="{{ filled($translations[$code] ?? null) ? 'is-complete' : '' }}" title="{{ strtoupper($code) }}: {{ $statuses[$code] ?? 'chưa có' }}">{{ strtoupper($code) }}</span>@endforeach</div></td>
                                <td><x-ui.badge :tone="$item->is_active && !$isTrashed ? 'success' : 'neutral'">{{ $isTrashed ? 'Thùng rác' : ($item->is_active ? 'Hiện' : 'Ẩn') }}</x-ui.badge></td>
                                <td><div class="row-actions">
                                    @if($isTrashed)
                                        @can('pages.delete')<button class="icon-button is-success" type="button" wire:click="restore({{ $item->id }})" title="Khôi phục" aria-label="Khôi phục"><x-ui.icon name="restore" size="18" /></button>@endcan
                                    @else
                                        @can('pages.update')<a class="icon-button" href="{{ route('admin.about-pages.edit', $item) }}" wire:navigate title="Sửa" aria-label="Sửa"><x-ui.icon name="edit" size="18" /></a>@endcan
                                        @can('pages.view')<a class="icon-button is-dark" href="{{ route('admin.about-pages.preview', ['page' => $item, 'locale' => $locale]) }}" target="_blank" title="Xem trước" aria-label="Xem trước"><x-ui.icon name="eye" size="18" /></a>@endcan
                                        @can('pages.update')<button class="icon-button" type="button" wire:click="toggleVisibility({{ $item->id }})" title="{{ $item->is_active ? 'Ẩn' : 'Hiện' }}" aria-label="{{ $item->is_active ? 'Ẩn' : 'Hiện' }}"><x-ui.icon :name="$item->is_active ? 'eye-off' : 'eye'" size="18" /></button>@endcan
                                        @can('pages.create')<button class="icon-button" type="button" wire:click="duplicate({{ $item->id }})" title="Nhân bản" aria-label="Nhân bản"><x-ui.icon name="copy" size="18" /></button>@endcan
                                        @can('pages.delete')<button class="icon-button is-danger" type="button" wire:click="delete({{ $item->id }})" wire:confirm="Chuyển nội dung này vào thùng rác?" title="Xóa" aria-label="Xóa"><x-ui.icon name="trash" size="18" /></button>@endcan
                                    @endif
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-ui.pagination :paginator="$pages" />
        @endif
    </section>
</div>
