<div>
    <x-admin.page-header class="card page-heading-card" title="Danh sách sản phẩm" description="Quản lý danh sách sản phẩm đa ngôn ngữ" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @can('products.create')
                <a class="button button-primary" href="{{ route('admin.products.create') }}" wire:navigate><x-ui.icon name="plus" size="18" /> Thêm sản phẩm</a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <section class="filter-card card">
        <div class="filter-grid">
            <div class="filter-search"><label for="search">Tìm kiếm</label><div><x-ui.icon name="search" size="18" /><input id="search" class="input" wire:model.live.debounce.300ms="search" placeholder="SKU hoặc tên sản phẩm"></div></div>
            <div><label for="category">Danh mục</label><select id="category" class="select" wire:model.live="category"><option value="">Tất cả danh mục</option>@foreach($categories as $item)<option value="{{ $item->id }}">{{ $item->getTranslation('name', 'vi', false) }}</option>@endforeach</select></div>
            <div><label for="active">Trạng thái</label><select id="active" class="select" wire:model.live="active"><option value="">Tất cả</option><option value="1">Đang hiển thị</option><option value="0">Đang ẩn</option></select></div>
            <div><label for="featured">Nổi bật</label><select id="featured" class="select" wire:model.live="featured"><option value="">Tất cả</option><option value="1">Sản phẩm nổi bật</option><option value="0">Không nổi bật</option></select></div>
            <div><label for="date_from">Cập nhật từ</label><input id="date_from" class="input" type="date" wire:model.live="date_from"></div>
            <div class="filter-actions"><button class="button button-ghost" type="button" wire:click="resetFilters">Đặt lại</button></div>
        </div>
    </section>

    <section class="card product-list-card">
        @if($products->isEmpty())
            <x-ui.empty-state title="Không tìm thấy sản phẩm" description="Thử thay đổi bộ lọc hoặc tạo sản phẩm mới." />
        @else
            <div class="category-toolbar product-toolbar">
                <div class="category-toolbar-actions">
                    @can('products.update')
                        <button class="button button-success" type="button" wire:click="bulk('hide')"><x-ui.icon name="eye-off" size="17" /> Ẩn</button>
                        <button class="button button-success" type="button" wire:click="bulk('show')"><x-ui.icon name="eye" size="17" /> Hiện</button>
                        <button class="button button-secondary" type="button" wire:click="saveSortOrders"><x-ui.icon name="save" size="17" /> Cập nhật thứ tự</button>
                    @endcan
                    @can('products.delete')
                        <button class="button button-danger" type="button" wire:click="requestBulkDelete"><x-ui.icon name="trash" size="17" /> Xóa</button>
                    @endcan
                </div>
                <span class="category-selection-count">{{ count($selected) ? 'Đã chọn '.count($selected).' sản phẩm' : 'Chưa chọn sản phẩm' }}</span>
            </div>
            @error('selected')<div class="validation-summary" role="alert">{{ $message }}</div>@enderror
            <div class="table-responsive desktop-product-table">
                <table class="data-table compact-product-table">
                    <thead><tr><th class="selection-column"></th><th class="order-column">Thứ tự</th><th>Sản phẩm</th><th class="featured-column">Nổi bật</th><th class="table-actions-heading">Thao tác</th></tr></thead>
                    <tbody>
                        @foreach($products as $product)
                            @php($name = $product->getTranslation('title', 'vi', false) ?: 'Chưa có tên')
                            <tr wire:key="product-{{ $product->id }}" class="{{ $product->is_active ? '' : 'is-muted-row' }}">
                                <td><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $product->id }}" aria-label="Chọn {{ $name }}"></td>
                                <td><input class="input order-input" type="number" min="0" max="999999" wire:model="sortOrders.{{ $product->id }}" aria-label="Thứ tự của {{ $name }}"></td>
                                <td>
                                    <div class="product-cell">
                                        <div class="product-thumb" x-data="{ imageFailed: false }">
                                            @if($product->featuredMedia)
                                                <img src="{{ $product->featuredMedia->url }}" alt="" x-show="!imageFailed" x-on:error="imageFailed = true">
                                                <span x-show="imageFailed" x-cloak><x-ui.icon name="image" /></span>
                                            @else
                                                <x-ui.icon name="image" />
                                            @endif
                                        </div>
                                        <div class="product-cell-info">
                                            <code>{{ $product->sku }}</code>
                                            <strong>{{ $name }}</strong>
                                            <small>
                                                {{ $product->category?->getTranslation('name', 'vi', false) ?: 'Chưa phân loại' }}
                                            </small>
                                            <small class="product-date-line">
                                                <span class="category-date-item" title="Ngày tạo"><x-ui.icon name="calendar" size="14" />{{ $product->created_at?->format('H:i - d/m/Y') ?: '—' }}</span>
                                                <span aria-hidden="true">-</span>
                                                <span class="category-date-item" title="Ngày cập nhật"><x-ui.icon name="history" size="14" />{{ $product->updated_at?->format('H:i - d/m/Y') ?: '—' }}</span>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <button class="table-toggle {{ $product->is_featured ? 'is-active' : '' }}" type="button" role="switch"
                                        aria-checked="{{ $product->is_featured ? 'true' : 'false' }}"
                                        wire:click="toggleFeatured({{ $product->id }})"
                                        title="{{ $product->is_featured ? 'Bỏ nổi bật' : 'Đánh dấu nổi bật' }}"
                                        @cannot('products.update') disabled @endcannot>
                                        <span class="table-toggle-track" aria-hidden="true"><span></span></span>
                                        <span>{{ $product->is_featured ? 'Bật' : 'Tắt' }}</span>
                                    </button>
                                </td>
                                <td><div class="row-actions">
                                    @can('products.update')<a class="icon-button" href="{{ route('admin.products.edit', $product) }}" wire:navigate title="Sửa"><x-ui.icon name="edit" size="18" /></a>@endcan
                                    @can('products.view')<a class="icon-button" href="{{ route('admin.products.preview', $product) }}" target="_blank" title="Xem trước"><x-ui.icon name="eye" size="18" /></a>@endcan
                                    @can('products.create')<button class="icon-button" type="button" wire:click="duplicate({{ $product->id }})" title="Nhân bản"><x-ui.icon name="copy" size="18" /></button>@endcan
                                    @can('products.delete')<button class="icon-button is-danger" type="button" wire:click="requestDelete({{ $product->id }})" title="Xóa" aria-label="Xóa sản phẩm {{ $product->getTranslation('title', 'vi', false) }}"><x-ui.icon name="trash" size="18" /></button>@endcan
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mobile-product-list">
                @foreach($products as $product)
                    <article class="product-mobile-card" wire:key="mobile-product-{{ $product->id }}">
                        <div class="mobile-product-controls">
                            <label><input class="table-checkbox" type="checkbox" wire:model.live="selected" value="{{ $product->id }}"> Chọn</label>
                            <label>Thứ tự <input class="input order-input" type="number" min="0" max="999999" wire:model="sortOrders.{{ $product->id }}"></label>
                        </div>
                        <div class="product-cell"><div class="product-thumb" x-data="{ imageFailed: false }">@if($product->featuredMedia)<img src="{{ $product->featuredMedia->url }}" alt="" x-show="!imageFailed" x-on:error="imageFailed = true"><span x-show="imageFailed" x-cloak><x-ui.icon name="image" /></span>@else<x-ui.icon name="image" />@endif</div><div><strong>{{ $product->getTranslation('title', 'vi', false) ?: 'Chưa có tên' }}</strong><small>{{ $product->sku }}</small></div></div>
                        <div class="mobile-product-featured"><span>Nổi bật</span><button class="table-toggle {{ $product->is_featured ? 'is-active' : '' }}" type="button" role="switch" aria-checked="{{ $product->is_featured ? 'true' : 'false' }}" wire:click="toggleFeatured({{ $product->id }})" @cannot('products.update') disabled @endcannot><span class="table-toggle-track" aria-hidden="true"><span></span></span><span>{{ $product->is_featured ? 'Bật' : 'Tắt' }}</span></button></div>
                        <small class="product-date-line"><span class="category-date-item"><x-ui.icon name="calendar" size="14" />{{ $product->created_at?->format('H:i - d/m/Y') ?: '—' }}</span><span>-</span><span class="category-date-item"><x-ui.icon name="history" size="14" />{{ $product->updated_at?->format('H:i - d/m/Y') ?: '—' }}</span></small>
                        <div class="mobile-card-actions">@can('products.update')<a class="button button-secondary" href="{{ route('admin.products.edit', $product) }}" wire:navigate><x-ui.icon name="edit" size="18" /> Sửa</a>@endcan</div>
                    </article>
                @endforeach
            </div>
            <x-ui.pagination :paginator="$products" :per-page-options="[10, 20, 50, 100]" />
        @endif
    </section>

    @if($pendingDeleteId || $pendingBulkDelete)
        <x-ui.delete-confirmation-modal wire-key="product-delete-confirmation" title="Xóa sản phẩm?" confirm-label="Có, xóa sản phẩm" warning="Sản phẩm sẽ không còn xuất hiện trong danh sách quản lý.">
            @if($pendingBulkDelete)
                Bạn sắp chuyển <strong>{{ count($selected) }} sản phẩm đã chọn</strong> vào thùng rác.
            @else
                Bạn sắp chuyển sản phẩm <strong>“{{ $pendingDeleteName }}”</strong> vào thùng rác.
            @endif
        </x-ui.delete-confirmation-modal>
    @endif

</div>
