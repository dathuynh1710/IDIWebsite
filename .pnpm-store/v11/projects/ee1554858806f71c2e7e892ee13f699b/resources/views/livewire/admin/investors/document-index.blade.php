<div>
    <x-admin.page-header title="Quản lý QHCĐ" description="Tìm kiếm, xuất bản và quản lý thư viện tài liệu cổ đông theo từng ngôn ngữ." :breadcrumbs="$breadcrumbs">
        <x-slot:actions><a class="button button-primary" href="{{ route('admin.investors.documents.create') }}" wire:navigate><x-ui.icon name="plus" size="18" /> Thêm QHCĐ mới</a></x-slot:actions>
    </x-admin.page-header>
    <section class="card filter-card"><div class="news-filter-grid">
        <div class="form-field filter-search"><label>Tìm kiếm</label><div><x-ui.icon name="search" size="17" /><input class="input" wire:model.live.debounce.350ms="search" placeholder="Tiêu đề hoặc số văn bản..."></div></div>
        <div class="form-field"><label>Chuyên mục</label><select class="select" wire:model.live="category"><option value="">Tất cả</option>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->getTranslation('name', $locale, false) ?: $cat->getTranslation('name', 'vi', false) }}</option>@endforeach</select></div>
        <x-form.select name="year" label="Năm" :options="['' => 'Tất cả'] + $years" wire:model.live="year" />
        <x-form.input name="date_from" label="Từ ngày" type="date" wire:model.live="date_from" />
        <x-form.input name="date_to" label="Đến ngày" type="date" wire:model.live="date_to" />
        <x-form.select name="active" label="Trạng thái" :options="['' => 'Tất cả', '1' => 'Đang hiện', '0' => 'Đang ẩn']" wire:model.live="active" />
        <x-form.select name="locale" label="Ngôn ngữ" :options="['vi' => 'VI', 'en' => 'EN', 'zh' => '中文']" wire:model.live="locale" />
    </div></section>
    <section class="card category-list-card">
        <div class="category-toolbar"><div class="category-toolbar-actions"><button class="button button-success" wire:click="bulk('show')" @disabled(!$selected)>Hiện</button><button class="button button-secondary" wire:click="bulk('hide')" @disabled(!$selected)>Ẩn</button><button class="button button-secondary" wire:click="bulk('reorder')" @disabled(!$selected)>Cập nhật thứ tự</button><button class="button button-danger" wire:click="bulk('delete')" wire:confirm="Xóa các tài liệu đã chọn?" @disabled(!$selected)>Xóa</button></div><span>{{ $documents->total() }} tài liệu</span></div>
        @if($documents->isEmpty())<x-ui.empty-state title="Chưa có tài liệu" description="Tạo tài liệu đầu tiên hoặc thay đổi bộ lọc." icon="file" />
        @else<div class="table-responsive"><table class="data-table category-table"><thead><tr><th></th><th>Thứ tự</th><th>Tiêu đề ({{ strtoupper($locale) }})</th><th>Danh mục</th><th>Kỳ báo cáo</th><th>Tệp & bản dịch</th><th>Trạng thái</th><th></th></tr></thead><tbody>
            @foreach($documents as $item)
                @php($localeFile = $item->files->firstWhere('locale', $locale))
                <tr wire:key="investor-document-{{ $item->id }}" @class(['is-muted-row' => !$item->is_active])>
                    <td><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $item->id }}"></td><td><input class="input order-input" type="number" wire:model="sortOrders.{{ $item->id }}"></td>
                    <td><strong>{{ $item->getTranslation('title', $locale, false) ?: 'Chưa có bản dịch' }}</strong><small style="display:block">{{ $item->document_number ?: 'Không có số văn bản' }} · {{ $item->published_on?->format('d/m/Y') ?: 'Chưa đặt ngày' }}</small></td>
                    <td>{{ $item->category?->getTranslation('name', $locale, false) ?: '—' }}</td>
                    <td>{{ $item->quarter ? 'Quý '.$item->quarter.' / ' : '' }}{{ $item->year ?: '—' }}</td>
                    <td><div class="translation-dots">@foreach(['vi','en','zh'] as $code)<span class="{{ filled($item->getTranslation('title', $code, false)) && $item->files->contains('locale', $code) ? 'is-complete' : '' }}">{{ strtoupper($code) }}</span>@endforeach</div>@if($localeFile)<a href="{{ $localeFile->media->url }}" target="_blank"><small><x-ui.icon name="download" size="12" /> {{ $localeFile->media->extension ? strtoupper($localeFile->media->extension) : 'FILE' }}</small></a>@endif</td>
                    <td><div class="news-status-stack"><x-ui.badge :tone="$item->is_active ? 'success' : 'neutral'">{{ $item->is_active ? 'Hiện' : 'Ẩn' }}</x-ui.badge>@if($item->is_featured)<x-ui.badge tone="warning">Nổi bật</x-ui.badge>@endif</div></td>
                    <td><div class="row-actions"><a class="icon-button" href="{{ route('admin.investors.documents.edit', $item) }}" wire:navigate title="Sửa"><x-ui.icon name="edit" size="18" /></a>@if($localeFile)<a class="icon-button is-dark" href="{{ $localeFile->media->url }}" target="_blank" title="Xem tệp"><x-ui.icon name="eye" size="18" /></a>@endif<button class="icon-button is-dark" wire:click="toggleVisibility({{ $item->id }})" title="Ẩn/hiện"><x-ui.icon :name="$item->is_active ? 'eye-off' : 'eye'" size="18" /></button><button class="icon-button is-danger" wire:click="delete({{ $item->id }})" wire:confirm="Xóa tài liệu này?" title="Xóa"><x-ui.icon name="trash" size="18" /></button></div></td>
                </tr>
            @endforeach
        </tbody></table></div><x-ui.pagination :paginator="$documents" />@endif
    </section>
</div>
