<div>
    <x-admin.page-header title="Quản lý danh mục cổ đông" description="Tổ chức nhóm tài liệu và đường dẫn theo từng ngôn ngữ." :breadcrumbs="$breadcrumbs">
        <x-slot:actions><a class="button button-primary" href="{{ route('admin.investors.categories.create') }}" wire:navigate><x-ui.icon name="plus" size="18" /> Thêm danh mục</a></x-slot:actions>
    </x-admin.page-header>
    <section class="card filter-card"><div class="category-filter-grid">
        <x-form.input name="search" label="Tìm kiếm" wire:model.live.debounce.350ms="search" placeholder="Tên danh mục..." />
        <x-form.select name="active" label="Trạng thái" :options="['' => 'Tất cả', '1' => 'Đang hiện', '0' => 'Đang ẩn']" wire:model.live="active" />
        <x-form.select name="locale" label="Ngôn ngữ" :options="['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文']" wire:model.live="locale" />
    </div></section>
    <section class="card category-list-card">
        <div class="category-toolbar"><div class="category-toolbar-actions">
            <button class="button button-success" wire:click="bulk('show')" @disabled(!$selected)>Hiện</button>
            <button class="button button-secondary" wire:click="bulk('hide')" @disabled(!$selected)>Ẩn</button>
            <button class="button button-secondary" wire:click="bulk('reorder')" @disabled(!$selected)>Cập nhật thứ tự</button>
            <button class="button button-danger" type="button" wire:click="requestBulkDelete" @disabled(!$selected)>Xóa</button>
        </div><span>{{ $categoryCount }} danh mục · {{ $categories->total() }} nhánh gốc</span></div>
        @if($categories->isEmpty())<x-ui.empty-state title="Chưa có danh mục" description="Hãy tạo danh mục đầu tiên cho quan hệ cổ đông." icon="folder" />
        @else<div class="table-responsive"><table class="data-table category-table"><thead><tr><th></th><th>Thứ tự</th><th>Tiêu đề ({{ strtoupper($locale) }})</th><th>Danh mục cha</th><th>Số tài liệu</th><th>Bản dịch</th><th>Trạng thái</th><th></th></tr></thead><tbody>
            @foreach($categories as $item)<tr wire:key="investor-category-{{ $item->id }}" @class(['is-muted-row' => !$item->is_active])>
                <td><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $item->id }}"></td>
                <td><input class="input order-input" type="number" wire:model="sortOrders.{{ $item->id }}"></td>
                <td class="category-name-cell investor-category-name-cell" style="--tree-depth: {{ $item->tree_depth }}">
                    <div @class(['investor-category-tree-label', 'is-child' => $item->tree_depth])>
                        <span class="investor-category-tree-connector" aria-hidden="true"></span>
                        <x-ui.icon :name="$item->tree_depth ? 'file' : 'folder'" size="16" />
                        <strong>{{ $item->getTranslation('name', $locale, false) ?: 'Chưa có bản dịch' }}</strong>
                        @if(!$item->tree_depth)<span class="investor-category-root-badge">Danh mục gốc</span>@endif
                    </div>
                    <small><x-ui.icon name="link" size="13" /> /{{ $locale }}/investors/{{ $item->getTranslation('slug', $locale, false) }}</small>
                    <small>
                        <span class="category-date-item" title="Ngày tạo"><x-ui.icon name="calendar" size="14" /><time datetime="{{ $item->created_at?->toIso8601String() }}">{{ $item->created_at?->format('H:i - d/m/Y') ?: '—' }}</time></span>
                        <span aria-hidden="true">-</span>
                        <span class="category-date-item" title="Ngày cập nhật"><x-ui.icon name="history" size="14" /><time datetime="{{ $item->updated_at?->toIso8601String() }}">{{ $item->updated_at?->format('H:i - d/m/Y') ?: '—' }}</time></span>
                    </small>
                </td>
                <td>
                    @if($item->parent)
                        <span class="investor-category-parent"><x-ui.icon name="chevron-right" size="15" /> {{ $item->parent->getTranslation('name', $locale, false) }}</span>
                    @else
                        <span class="investor-category-parent is-root"><x-ui.icon name="folder" size="15" /> — Gốc —</span>
                    @endif
                </td>
                <td><span class="category-product-count">{{ $item->documents_count }}</span></td>
                <td><div class="translation-dots">@foreach(['vi','en','zh'] as $code)<span class="{{ filled($item->getTranslation('name', $code, false)) ? 'is-complete' : '' }}">{{ strtoupper($code) }}</span>@endforeach</div></td>
                <td><x-ui.badge :tone="$item->is_active ? 'success' : 'neutral'">{{ $item->is_active ? 'Hiện' : 'Ẩn' }}</x-ui.badge></td>
                <td><div class="row-actions"><a class="icon-button" href="{{ route('admin.investors.categories.edit', $item) }}" wire:navigate title="Sửa"><x-ui.icon name="edit" size="18" /></a><button class="icon-button is-dark" wire:click="toggleVisibility({{ $item->id }})" title="Ẩn/hiện"><x-ui.icon :name="$item->is_active ? 'eye-off' : 'eye'" size="18" /></button><button class="icon-button is-danger" type="button" wire:click="requestDelete({{ $item->id }})" title="Xóa" aria-label="Xóa danh mục {{ $item->getTranslation('name', $locale, false) }}"><x-ui.icon name="trash" size="18" /></button></div></td>
            </tr>@endforeach
        </tbody></table></div><x-ui.pagination :paginator="$categories" :per-page-options="$perPageOptions" :show-summary="false" />@endif
    </section>
    @if($pendingDeleteId || $pendingBulkDelete)
        <x-ui.delete-confirmation-modal wire-key="investor-category-delete-confirmation" title="Xóa danh mục cổ đông?" confirm-label="Có, xóa danh mục" warning="Chỉ có thể xóa danh mục không còn danh mục con hoặc tài liệu.">
            @if($pendingBulkDelete)
                Bạn sắp xóa <strong>{{ count($selected) }} danh mục đã chọn</strong>.
            @else
                Bạn sắp xóa danh mục <strong>“{{ $pendingDeleteName }}”</strong>.
            @endif
        </x-ui.delete-confirmation-modal>
    @endif
</div>
