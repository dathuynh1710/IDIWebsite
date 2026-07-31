<div>
    <x-admin.page-header title="Quản lý tuyển dụng" description="Tìm kiếm, xuất bản và quản lý vị trí tuyển dụng theo từng ngôn ngữ." :breadcrumbs="$breadcrumbs">
        <x-slot:actions><a class="button button-primary" href="{{ route('admin.recruitment.positions.create') }}" wire:navigate><x-ui.icon name="plus" size="18" /> Thêm tuyển dụng mới</a></x-slot:actions>
    </x-admin.page-header>
    <section class="card filter-card"><div class="recruitment-filter-grid">
        <div class="form-field filter-search"><label>Tìm kiếm</label><div><x-ui.icon name="search" size="17" /><input class="input" wire:model.live.debounce.350ms="search" placeholder="Tiêu đề, mã hoặc phòng ban..."></div></div>
        <x-form.input name="date_from" label="Từ ngày" type="date" wire:model.live="date_from" />
        <x-form.input name="date_to" label="Đến ngày" type="date" wire:model.live="date_to" />
        <x-form.select name="active" label="Trạng thái" :options="['' => 'Tất cả', '1' => 'Đang hiện', '0' => 'Đang ẩn']" wire:model.live="active" />
        <x-form.select name="locale" label="Ngôn ngữ" :options="['vi' => 'VI', 'en' => 'EN', 'zh' => '中文']" wire:model.live="locale" />
    </div></section>
    <section class="card category-list-card">
        <div class="category-toolbar"><div class="category-toolbar-actions">
            <button class="button button-success" wire:click="bulk('show')" @disabled(!$selected)>Hiện</button>
            <button class="button button-secondary" wire:click="bulk('hide')" @disabled(!$selected)>Ẩn</button>
            <button class="button button-secondary" wire:click="bulk('reorder')" @disabled(!$selected)>Cập nhật thứ tự</button>
            <button class="button button-danger" wire:click="bulk('delete')" wire:confirm="Xóa các vị trí đã chọn?" @disabled(!$selected)>Xóa</button>
        </div><span>{{ $positions->total() }} vị trí</span></div>
        @if($positions->isEmpty())
            <x-ui.empty-state title="Chưa có vị trí tuyển dụng" description="Tạo vị trí đầu tiên hoặc thay đổi bộ lọc." icon="briefcase" />
        @else
            <div class="table-responsive"><table class="data-table category-table"><thead><tr><th></th><th>Thứ tự</th><th>Tiêu đề ({{ strtoupper($locale) }})</th><th>Số lượng</th><th>Hạn nộp</th><th>Hồ sơ</th><th>Bản dịch</th><th>Trạng thái</th><th></th></tr></thead><tbody>
                @foreach($positions as $item)<tr wire:key="position-{{ $item->id }}" @class(['is-muted-row' => !$item->is_active])>
                    <td><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $item->id }}"></td>
                    <td><input class="input order-input" type="number" wire:model="sortOrders.{{ $item->id }}"></td>
                    <td><div class="recruitment-title-cell"><strong>{{ $item->getTranslation('title', $locale, false) ?: 'Chưa có bản dịch' }}</strong><small>{{ $item->code ?: 'Chưa có mã' }} · {{ $item->getTranslation('location', $locale, false) ?: 'Chưa nhập nơi làm việc' }}</small></div></td>
                    <td>{{ $item->quantity }}</td><td>{{ $item->expires_at?->format('d/m/Y') ?: 'Không giới hạn' }}</td>
                    <td><a href="{{ route('admin.recruitment.applications.index', ['position' => $item->id]) }}" wire:navigate><x-ui.badge tone="info">{{ $item->applications_count }}</x-ui.badge></a></td>
                    <td><div class="translation-dots">@foreach(['vi','en','zh'] as $code)<span class="{{ filled($item->getTranslation('title', $code, false)) ? 'is-complete' : '' }}">{{ strtoupper($code) }}</span>@endforeach</div></td>
                    <td><button type="button" wire:click="toggleVisibility({{ $item->id }})"><x-ui.badge :tone="$item->is_active ? 'success' : 'neutral'">{{ $item->is_active ? 'Hiện' : 'Ẩn' }}</x-ui.badge></button></td>
                    <td><div class="row-actions"><a class="icon-button" href="{{ route('admin.recruitment.positions.edit', $item) }}" wire:navigate title="Sửa"><x-ui.icon name="edit" size="18" /></a><a class="icon-button is-dark" href="{{ route('admin.recruitment.positions.preview', ['position' => $item, 'locale' => $locale]) }}" target="_blank" title="Xem trước"><x-ui.icon name="eye" size="18" /></a><button class="icon-button" wire:click="duplicate({{ $item->id }})" title="Nhân bản"><x-ui.icon name="copy" size="18" /></button><button class="icon-button is-danger" wire:click="delete({{ $item->id }})" wire:confirm="Xóa vị trí này?" title="Xóa"><x-ui.icon name="trash" size="18" /></button></div></td>
                </tr>@endforeach
            </tbody></table></div><x-ui.pagination :paginator="$positions" />
        @endif
    </section>
</div>
