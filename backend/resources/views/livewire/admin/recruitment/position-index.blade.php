<div class="recruitment-management-page">
    <x-admin.page-header title="Quản lý tuyển dụng" description="Tìm kiếm và quản lý các vị trí tuyển dụng." :breadcrumbs="$breadcrumbs" class="recruitment-management-heading">
        <x-slot:actions><a class="button button-primary" href="{{ route('admin.recruitment.positions.create') }}" wire:navigate><x-ui.icon name="plus" size="18" /> Thêm tuyển dụng mới</a></x-slot:actions>
    </x-admin.page-header>

    <section class="card recruitment-management-filter-card">
        <form wire:submit="applySearch" class="recruitment-management-filter-grid">
            <div class="form-field recruitment-date-filter">
                <label>Ngày đăng</label>
                <div><input class="input" type="date" wire:model.live="date_from" aria-label="Từ ngày"><span>đến</span><input class="input" type="date" wire:model.live="date_to" aria-label="Đến ngày"></div>
            </div>
            <div class="form-field"><label>Tìm kiếm theo</label><select class="select" wire:model.live="searchBy"><option value="title">Tiêu đề</option><option value="code">Mã tuyển dụng</option><option value="location">Nơi làm việc</option><option value="department">Phòng ban</option></select></div>
            <div class="form-field recruitment-keyword-filter"><label for="recruitment-keyword">Từ khóa</label><div><input id="recruitment-keyword" class="input" wire:model="searchInput" placeholder="Nhập nội dung cần tìm..."><button class="button button-primary" type="submit"><x-ui.icon name="search" size="16" /> Tìm kiếm</button></div></div>
            <div class="form-field"><label>Ngôn ngữ</label><select class="select" wire:model.live="locale"><option value="vi">VI</option><option value="en">EN</option><option value="zh">中文</option></select></div>
        </form>
        <p class="recruitment-management-total">Tổng cộng: <strong>{{ $positions->total() }}</strong></p>
    </section>

    <section class="card recruitment-compact-list-card">
        <div class="recruitment-compact-toolbar">
            <div class="category-toolbar-actions">
                <button class="button button-secondary" wire:click="bulk('hide')" @disabled(!$selected)>Ẩn</button>
                <button class="button button-success" wire:click="bulk('show')" @disabled(!$selected)>Hiện</button>
                <button class="button button-secondary" wire:click="bulk('reorder')" @disabled(!$selected)>Cập nhật thứ tự</button>
                <button class="button button-danger" wire:click="bulk('delete')" wire:confirm="Xóa các vị trí đã chọn?" @disabled(!$selected)>Xóa</button>
            </div>
            @if($selected)<span>Đã chọn <strong>{{ count($selected) }}</strong> vị trí</span>@endif
        </div>

        @if($positions->isEmpty())
            <x-ui.empty-state title="Chưa có vị trí tuyển dụng" description="Tạo vị trí đầu tiên hoặc thay đổi bộ lọc." icon="briefcase" />
        @else
            @php
                $pageIds = $positions->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
                $selectedIds = array_map('intval', $selected);
                $allPageSelected = $pageIds !== [] && count(array_intersect($pageIds, $selectedIds)) === count($pageIds);
            @endphp
            <div class="table-responsive recruitment-compact-table-wrap">
                <table class="data-table recruitment-compact-table">
                    <thead><tr>
                        <th class="selection-column"><input class="table-checkbox" type="checkbox" wire:click="togglePageSelection(@js($pageIds))" @checked($allPageSelected) aria-label="Chọn tất cả vị trí trên trang"></th>
                        <th class="recruitment-order-heading">Thứ tự</th>
                        <th>Tiêu đề</th>
                        <th class="recruitment-number-heading">Số lượng</th>
                        <th>Hạn nộp hồ sơ</th>
                        <th>Nơi làm việc</th>
                        <th class="table-actions-heading">Thao tác</th>
                    </tr></thead>
                    <tbody>
                        @foreach($positions as $item)
                            <tr wire:key="position-{{ $item->id }}" @class(['is-muted-row' => !$item->is_active])>
                                <td class="selection-column"><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $item->id }}" aria-label="Chọn {{ $item->getTranslation('title', $locale, false) }}"></td>
                                <td><input class="input order-input" type="number" wire:model="sortOrders.{{ $item->id }}" min="0" aria-label="Thứ tự {{ $item->getTranslation('title', $locale, false) }}"></td>
                                <td><div class="recruitment-compact-title"><small>{{ $item->code ?: 'Chưa có mã' }}</small><strong>{{ $item->getTranslation('title', $locale, false) ?: 'Chưa có bản dịch' }}</strong>@if($item->department)<span>{{ $item->department }}</span>@endif</div></td>
                                <td class="recruitment-number-cell">{{ $item->quantity }}</td>
                                <td><span class="recruitment-deadline">{{ $item->expires_at?->format('d/m/Y') ?: 'Không giới hạn' }}</span></td>
                                <td><span class="recruitment-location">{{ $item->getTranslation('location', $locale, false) ?: 'Chưa cập nhật' }}</span></td>
                                <td><div class="row-actions recruitment-compact-actions">
                                    <a class="icon-button is-success" href="{{ route('admin.recruitment.positions.edit', $item) }}" wire:navigate title="Sửa"><x-ui.icon name="edit" size="17" /></a>
                                    <a class="icon-button is-dark" href="{{ route('admin.recruitment.positions.preview', ['position' => $item, 'locale' => $locale]) }}" target="_blank" title="Xem trước"><x-ui.icon name="eye" size="17" /></a>
                                    <button @class(['icon-button', 'is-success' => !$item->is_active, 'is-dark' => $item->is_active]) wire:click="toggleVisibility({{ $item->id }})" title="{{ $item->is_active ? 'Ẩn tuyển dụng' : 'Hiện tuyển dụng' }}"><x-ui.icon name="{{ $item->is_active ? 'eye-off' : 'eye' }}" size="17" /></button>
                                    <button class="icon-button" wire:click="duplicate({{ $item->id }})" title="Nhân bản"><x-ui.icon name="copy" size="17" /></button>
                                    <button class="icon-button is-danger" wire:click="delete({{ $item->id }})" wire:confirm="Xóa vị trí này?" title="Xóa"><x-ui.icon name="trash" size="17" /></button>
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-ui.pagination :paginator="$positions" :per-page-options="$perPageOptions" />
        @endif
    </section>
</div>
