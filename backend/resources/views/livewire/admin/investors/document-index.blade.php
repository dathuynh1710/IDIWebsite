<div>
    <x-admin.page-header title="Quản lý QHCĐ" description="Tìm kiếm và quản lý tài liệu quan hệ cổ đông." :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <a class="button button-primary" href="{{ route('admin.investors.documents.create') }}" wire:navigate><x-ui.icon name="plus" size="18" /> Thêm QHCĐ mới</a>
        </x-slot:actions>
    </x-admin.page-header>

    <section class="card filter-card">
        <div class="news-filter-grid">
            <div class="form-field">
                <label>Chuyên mục</label>
                <select class="select" wire:model.live="category">
                    <option value="">Tất cả</option>
                    @foreach($categories as $categoryItem)
                        <option value="{{ $categoryItem->id }}">{{ $categoryItem->getTranslation('name', 'vi', false) }}</option>
                    @endforeach
                </select>
            </div>
            <x-form.input name="date_from" label="Ngày đăng từ" type="date" wire:model.live="date_from" />
            <x-form.input name="date_to" label="Đến ngày" type="date" wire:model.live="date_to" />
            <div class="form-field filter-search">
                <label>Tìm kiếm</label>
                <div><x-ui.icon name="search" size="17" /><input class="input" wire:model.live.debounce.350ms="search" placeholder="Tiêu đề..."></div>
            </div>
        </div>
    </section>

    <section class="card category-list-card">
        <div class="category-toolbar">
            <div class="category-toolbar-actions">
                <button class="button button-success" wire:click="bulk('show')" @disabled(!$selected)>Hiện</button>
                <button class="button button-secondary" wire:click="bulk('hide')" @disabled(!$selected)>Ẩn</button>
                <button class="button button-secondary" wire:click="bulk('reorder')" @disabled(!$selected)>Cập nhật thứ tự</button>
                <button class="button button-danger" wire:click="bulk('delete')" wire:confirm="Xóa các tài liệu đã chọn?" @disabled(!$selected)>Xóa</button>
            </div>
            <span>{{ $documents->total() }} tài liệu</span>
        </div>

        @if($documents->isEmpty())
            <x-ui.empty-state title="Chưa có tài liệu" description="Tạo tài liệu đầu tiên hoặc thay đổi bộ lọc." icon="file" />
        @else
            <div class="table-responsive">
                <table class="data-table category-table">
                    <thead>
                        <tr><th></th><th>Thứ tự</th><th>Tiêu đề</th><th class="investor-actions-column">Thao tác</th></tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $item)
                            @php
                                $title = $item->getTranslation('title', $locale, false) ?: $item->getTranslation('title', 'vi', false);
                                $categoryName = $item->category?->getTranslation('name', $locale, false) ?: $item->category?->getTranslation('name', 'vi', false);
                                $file = $item->files->first();
                            @endphp
                            <tr wire:key="investor-document-{{ $item->id }}" @class(['is-muted-row' => !$item->is_active])>
                                <td><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $item->id }}"></td>
                                <td><input class="input order-input" type="number" wire:model="sortOrders.{{ $item->id }}"></td>
                                <td>
                                    <strong>{{ $title ?: 'Chưa có tiêu đề' }}</strong>
                                    @if($categoryName)<small style="display:block"><x-ui.icon name="folder" size="12" /> {{ $categoryName }}</small>@endif
                                    @if($item->slug)<small style="display:block"><x-ui.icon name="link" size="12" /> {{ $item->slug }}</small>@endif
                                    <small style="display:block"><x-ui.icon name="calendar" size="12" /> {{ $item->published_on?->format('d/m/Y') ?: 'Chưa đặt ngày đăng' }}</small>
                                </td>
                                <td class="investor-actions-column">
                                    <div class="row-actions">
                                        <a class="icon-button" href="{{ route('admin.investors.documents.edit', $item) }}" wire:navigate title="Sửa"><x-ui.icon name="edit" size="18" /></a>
                                        @if($file)<a class="icon-button is-dark" href="{{ route('investors.documents.download', $file) }}" title="Tải tệp"><x-ui.icon name="download" size="18" /></a>@endif
                                        <button class="icon-button is-dark" wire:click="toggleVisibility({{ $item->id }})" title="Ẩn/hiện"><x-ui.icon :name="$item->is_active ? 'eye-off' : 'eye'" size="18" /></button>
                                        <button class="icon-button is-danger" wire:click="delete({{ $item->id }})" wire:confirm="Xóa tài liệu này?" title="Xóa"><x-ui.icon name="trash" size="18" /></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-ui.pagination :paginator="$documents" />
        @endif
    </section>
</div>
