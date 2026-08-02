@extends('layouts.admin')

@section('title', 'Sản phẩm - '.config('admin.name'))
@section('page-context', 'Quản lý sản phẩm')

@section('content')
    <x-admin.page-header title="Sản phẩm" description="Quản lý danh mục sản phẩm đa ngôn ngữ" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @can('products.create')
                <a class="button button-primary" href="{{ route('admin.products.create') }}"><x-ui.icon name="plus" size="18" /> Thêm sản phẩm</a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <section class="filter-card card">
        <form method="GET" action="{{ route('admin.products.index') }}" class="filter-grid">
            <div class="filter-search">
                <label for="search">Tìm kiếm</label>
                <div><x-ui.icon name="search" size="18" /><input id="search" class="input" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="SKU hoặc tên sản phẩm"></div>
            </div>
            <div>
                <label for="category">Danh mục</label>
                <select id="category" class="select" name="category">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string)($filters['category'] ?? '') === (string)$category->id)>{{ $category->getTranslation('name', 'vi', false) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="active">Trạng thái</label>
                <select id="active" class="select" name="active">
                    <option value="">Tất cả</option>
                    <option value="1" @selected(($filters['active'] ?? '') === '1')>Đang hiển thị</option>
                    <option value="0" @selected(($filters['active'] ?? '') === '0')>Đang ẩn</option>
                </select>
            </div>
            <div>
                <label for="date_from">Cập nhật từ</label>
                <input id="date_from" class="input" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="filter-actions">
                <button class="button button-primary" type="submit"><x-ui.icon name="filter" size="18" /> Lọc</button>
                <a class="button button-ghost" href="{{ route('admin.products.index') }}">Đặt lại</a>
            </div>
        </form>
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
                            <tr>
                                <td><div class="product-cell">
                                    <div class="product-thumb">
                                        @if($product->featuredMedia)<img src="{{ $product->featuredMedia->url }}" alt="" onerror="this.hidden=true">@else<x-ui.icon name="image" />@endif
                                    </div>
                                    <div><strong>{{ $product->getTranslation('title', 'vi', false) ?: 'Chưa có tên' }}</strong><small>{{ $product->scientific_name }}</small></div>
                                </div></td>
                                <td><code>{{ $product->sku }}</code></td>
                                <td>{{ $product->category?->getTranslation('name', 'vi', false) ?: '—' }}</td>
                                <td>{{ $product->sort_order }}</td>
                                <td><span class="boolean-dot {{ $product->is_featured ? 'is-true' : '' }}"></span>{{ $product->is_featured ? 'Có' : 'Không' }}</td>
                                <td><x-ui.badge :tone="$statusTone">{{ $statusLabels[$status] ?? $status }}</x-ui.badge></td>
                                <td><time title="{{ $product->updated_at }}">{{ $product->updated_at->diffForHumans() }}</time></td>
                                <td><div class="row-actions">
                                    @can('products.update')<a class="icon-button" href="{{ route('admin.products.edit', $product) }}" aria-label="Sửa {{ $product->sku }}" title="Sửa"><x-ui.icon name="edit" size="18" /></a>@endcan
                                    @can('products.view')<a class="icon-button" href="{{ route('admin.products.preview', $product) }}" target="_blank" aria-label="Xem trước {{ $product->sku }}" title="Xem trước"><x-ui.icon name="eye" size="18" /></a>@endcan
                                    @can('products.create')
                                        <form method="POST" action="{{ route('admin.products.duplicate', $product) }}">@csrf<button class="icon-button" type="submit" aria-label="Nhân bản {{ $product->sku }}" title="Nhân bản"><x-ui.icon name="copy" size="18" /></button></form>
                                    @endcan
                                    @can('products.delete')
                                        <form id="delete-product-{{ $product->id }}" method="POST" action="{{ route('admin.products.destroy', $product) }}">@csrf @method('DELETE')</form>
                                        <x-ui.confirm-dialog form-id="delete-product-{{ $product->id }}" message="Sản phẩm “{{ $product->getTranslation('title', 'vi', false) ?: $product->sku }}” sẽ được chuyển vào thùng rác.">
                                            <x-slot:trigger><button class="icon-button is-danger" type="button" aria-label="Xóa {{ $product->sku }}" title="Xóa"><x-ui.icon name="trash" size="18" /></button></x-slot:trigger>
                                        </x-ui.confirm-dialog>
                                    @endcan
                                </div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mobile-product-list">
                @foreach($products as $product)
                    <article class="product-mobile-card">
                        <div class="product-cell">
                            <div class="product-thumb">@if($product->featuredMedia)<img src="{{ $product->featuredMedia->url }}" alt="" onerror="this.hidden=true">@else<x-ui.icon name="image" />@endif</div>
                            <div><strong>{{ $product->getTranslation('title', 'vi', false) ?: 'Chưa có tên' }}</strong><small>{{ $product->sku }}</small></div>
                        </div>
                        <dl><div><dt>Danh mục</dt><dd>{{ $product->category?->getTranslation('name', 'vi', false) ?: '—' }}</dd></div><div><dt>Cập nhật</dt><dd>{{ $product->updated_at->diffForHumans() }}</dd></div></dl>
                        <div class="mobile-card-actions">
                            @can('products.update')<a class="button button-secondary" href="{{ route('admin.products.edit', $product) }}"><x-ui.icon name="edit" size="18" /> Sửa</a>@endcan
                            @can('products.view')<a class="button button-ghost" href="{{ route('admin.products.preview', $product) }}"><x-ui.icon name="eye" size="18" /> Xem</a>@endcan
                        </div>
                    </article>
                @endforeach
            </div>
            <x-ui.pagination :paginator="$products" />
        @endif
    </section>
@endsection
