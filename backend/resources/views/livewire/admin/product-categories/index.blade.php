<div>
    <x-admin.page-header class="card page-heading-card" title="Danh mục sản phẩm" description="Sắp xếp và kiểm soát nhóm sản phẩm hiển thị trên website" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @can('products.create')
                <a class="button button-primary" href="{{ route('admin.product-categories.create') }}" wire:navigate>
                    <x-ui.icon name="plus" size="18" /> Thêm danh mục
                </a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <section class="filter-card card">
        <div class="category-filter-grid">
            <div class="filter-search">
                <label for="category-search">Tìm kiếm</label>
                <div><x-ui.icon name="search" size="18" /><input id="category-search" class="input" wire:model.live.debounce.300ms="search" placeholder="Tên, mã hoặc đường dẫn"></div>
            </div>
            <div>
                <label for="category-status">Trạng thái</label>
                <select id="category-status" class="select" wire:model.live="status">
                    <option value="">Tất cả danh mục</option>
                    <option value="active">Đang hiển thị</option>
                    <option value="hidden">Đang ẩn</option>
                    <option value="trashed">Thùng rác</option>
                </select>
            </div>
            <div class="filter-actions">
                <button class="button button-ghost" type="button" wire:click="resetFilters">Đặt lại</button>
            </div>
        </div>
    </section>

    <section class="card category-list-card" wire:loading.class="is-loading">
        @if($categories->isEmpty())
            <x-ui.empty-state title="{{ $status === 'trashed' ? 'Thùng rác đang trống' : 'Chưa có danh mục phù hợp' }}" description="Thử thay đổi bộ lọc hoặc thêm danh mục sản phẩm mới." />
        @elseif($status !== 'trashed')
            <div>
                <div class="category-toolbar">
                    <div class="category-toolbar-actions">
                        @can('products.update')
                            <button class="button button-success" type="button" wire:click="bulk('hide')"><x-ui.icon name="eye-off" size="17" /> Ẩn</button>
                            <button class="button button-success" type="button" wire:click="bulk('show')"><x-ui.icon name="eye" size="17" /> Hiện</button>
                            <button class="button button-secondary" type="button" wire:click="bulk('reorder')"><x-ui.icon name="save" size="17" /> Cập nhật thứ tự</button>
                        @endcan
                        @can('products.delete')
                            <button class="button button-danger" type="button" wire:click="bulk('delete')" wire:confirm="Chuyển các danh mục đã chọn vào thùng rác?"><x-ui.icon name="trash" size="17" /> Xóa</button>
                        @endcan
                    </div>
                    <span class="category-selection-count">{{ count($selected) ? 'Đã chọn '.count($selected).' danh mục' : 'Chưa chọn danh mục' }}</span>
                </div>
                @error('selected')<div class="validation-summary" role="alert">{{ $message }}</div>@enderror
                <div class="table-responsive">
                    <table class="data-table category-table">
                        <thead><tr><th class="selection-column"></th><th class="order-column">Thứ tự</th><th>Tên danh mục</th><th>Sản phẩm</th><th class="table-actions-heading">Thao tác</th></tr></thead>
                        <tbody>
                            @foreach($categories as $category)
                                @php($name = $category->getTranslation('name', 'vi', false) ?: 'Chưa có tên')
                                <tr wire:key="category-{{ $category->id }}" class="{{ $category->is_active ? '' : 'is-muted-row' }}">
                                    <td><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $category->id }}" aria-label="Chọn {{ $name }}"></td>
                                    <td><input class="input order-input" type="number" min="0" max="999999" wire:model="sortOrders.{{ $category->id }}"></td>
                                    <td><div class="category-name-cell"><strong>{{ $name }}</strong><small><span class="category-date-item" title="Ngày tạo"><x-ui.icon name="calendar" size="14" />{{ $category->created_at?->format('H:i - d/m/Y') ?: '—' }}</span><span aria-hidden="true">-</span><span class="category-date-item" title="Ngày cập nhật"><x-ui.icon name="history" size="14" />{{ $category->updated_at?->format('H:i - d/m/Y') ?: '—' }}</span></small></div></td>
                                    <td><span class="category-product-count">{{ $category->products_count }}</span></td>
                                    <td><div class="row-actions">
                                        @can('products.update')
                                            <a class="icon-button is-success" href="{{ route('admin.product-categories.edit', $category) }}" wire:navigate title="Sửa"><x-ui.icon name="edit" size="18" /></a>
                                            <button class="icon-button" type="button" wire:click="toggleVisibility({{ $category->id }})"><x-ui.icon :name="$category->is_active ? 'eye-off' : 'eye'" size="18" /></button>
                                        @endcan
                                        @can('products.delete')
                                            <button class="icon-button is-danger" type="button" wire:click="delete({{ $category->id }})" wire:confirm="Xóa danh mục “{{ $name }}”?"><x-ui.icon name="trash" size="18" /></button>
                                        @endcan
                                    </div></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table category-table">
                    <thead><tr><th>Tên danh mục</th><th>Đã xóa</th><th>Thao tác</th></tr></thead>
                    <tbody>@foreach($categories as $category)<tr wire:key="trashed-category-{{ $category->id }}"><td><div class="category-name-cell"><strong>{{ $category->getTranslation('name', 'vi', false) }}</strong><small><span class="category-date-item" title="Ngày tạo"><x-ui.icon name="calendar" size="14" />{{ $category->created_at?->format('H:i - d/m/Y') ?: '—' }}</span><span aria-hidden="true">-</span><span class="category-date-item" title="Ngày cập nhật"><x-ui.icon name="history" size="14" />{{ $category->updated_at?->format('H:i - d/m/Y') ?: '—' }}</span></small></div></td><td>{{ $category->deleted_at?->format('d/m/Y H:i') }}</td><td><button class="button button-secondary" wire:click="restore({{ $category->id }})"><x-ui.icon name="restore" size="17" /> Khôi phục</button></td></tr>@endforeach</tbody>
                </table>
            </div>
        @endif
        <x-ui.pagination :paginator="$categories" :per-page-options="[10, 20, 50, 100]" />
    </section>

</div>
