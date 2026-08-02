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
            <div><label for="date_from">Cập nhật từ</label><input id="date_from" class="input" type="date" wire:model.live="date_from"></div>
            <div class="filter-actions"><button class="button button-ghost" type="button" wire:click="resetFilters">Đặt lại</button></div>
        </div>
    </section>

    <section class="card product-list-card">
        @if($products->isEmpty())
            <x-ui.empty-state title="Không tìm thấy sản phẩm" description="Thử thay đổi bộ lọc hoặc tạo sản phẩm mới." />
        @else
            <div class="table-responsive desktop-product-table">
                <table class="data-table">
                    <thead><tr><th>Sản phẩm</th><th>SKU</th><th>Danh mục</th><th>Thứ tự</th><th>Nổi bật</th><th>Trạng thái</th><th>Cập nhật</th><th class="table-actions-heading">Thao tác</th></tr></thead>
                    <tbody>
                        @foreach($products as $product)
                            @php
                                $status = $product->getTranslation('translation_status', 'vi', false) ?: 'draft';
                                $statusLabels = ['draft'=>'Bản nháp','translating'=>'Đang dịch','review'=>'Chờ duyệt','scheduled'=>'Đã lên lịch','published'=>'Đã xuất bản','hidden'=>'Tạm ẩn','archived'=>'Lưu trữ'];
                                $statusTone = ['published'=>'success','scheduled'=>'info','review'=>'warning','hidden'=>'neutral','archived'=>'neutral'][$status] ?? 'neutral';
                            @endphp
                            <tr wire:key="product-{{ $product->id }}">
                                <td><div class="product-cell"><div class="product-thumb" x-data="{ imageFailed: false }">@if($product->featuredMedia)<img src="{{ $product->featuredMedia->url }}" alt="" x-show="!imageFailed" x-on:error="imageFailed = true"><span x-show="imageFailed" x-cloak><x-ui.icon name="image" /></span>@else<x-ui.icon name="image" />@endif</div><div><strong>{{ $product->getTranslation('title', 'vi', false) ?: 'Chưa có tên' }}</strong><small>{{ $product->scientific_name }}</small></div></div></td>
                                <td><code>{{ $product->sku }}</code></td>
                                <td>{{ $product->category?->getTranslation('name', 'vi', false) ?: '—' }}</td>
                                <td>{{ $product->sort_order }}</td>
                                <td><span class="boolean-dot {{ $product->is_featured ? 'is-true' : '' }}"></span>{{ $product->is_featured ? 'Có' : 'Không' }}</td>
                                <td><x-ui.badge :tone="$statusTone">{{ $statusLabels[$status] ?? $status }}</x-ui.badge></td>
                                <td>{{ $product->updated_at->diffForHumans() }}</td>
                                <td><div class="row-actions">
                                    @can('products.update')<a class="icon-button" href="{{ route('admin.products.edit', $product) }}" wire:navigate title="Sửa"><x-ui.icon name="edit" size="18" /></a>@endcan
                                    @can('products.view')<a class="icon-button" href="{{ route('admin.products.preview', $product) }}" target="_blank" title="Xem trước"><x-ui.icon name="eye" size="18" /></a>@endcan
                                    @can('products.create')<button class="icon-button" type="button" wire:click="duplicate({{ $product->id }})" title="Nhân bản"><x-ui.icon name="copy" size="18" /></button>@endcan
                                    @can('products.delete')<button class="icon-button is-danger" type="button" wire:click="delete({{ $product->id }})" wire:confirm="Chuyển sản phẩm này vào thùng rác?" title="Xóa"><x-ui.icon name="trash" size="18" /></button>@endcan
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mobile-product-list">
                @foreach($products as $product)
                    <article class="product-mobile-card" wire:key="mobile-product-{{ $product->id }}">
                        <div class="product-cell"><div class="product-thumb" x-data="{ imageFailed: false }">@if($product->featuredMedia)<img src="{{ $product->featuredMedia->url }}" alt="" x-show="!imageFailed" x-on:error="imageFailed = true"><span x-show="imageFailed" x-cloak><x-ui.icon name="image" /></span>@else<x-ui.icon name="image" />@endif</div><div><strong>{{ $product->getTranslation('title', 'vi', false) ?: 'Chưa có tên' }}</strong><small>{{ $product->sku }}</small></div></div>
                        <div class="mobile-card-actions">@can('products.update')<a class="button button-secondary" href="{{ route('admin.products.edit', $product) }}" wire:navigate><x-ui.icon name="edit" size="18" /> Sửa</a>@endcan</div>
                    </article>
                @endforeach
            </div>
            <x-ui.pagination :paginator="$products" />
        @endif
    </section>

</div>
