@extends('layouts.admin')

@section('title', 'Danh mục sản phẩm - '.config('admin.name'))
@section('page-context', 'Danh mục sản phẩm')

@section('content')
    <x-admin.page-header
        title="Danh mục sản phẩm"
        description="Sắp xếp và kiểm soát nhóm sản phẩm hiển thị trên website"
        :breadcrumbs="$breadcrumbs"
    >
        <x-slot:actions>
            @can('products.create')
                <a class="button button-primary" href="{{ route('admin.product-categories.create') }}">
                    <x-ui.icon name="plus" size="18" /> Thêm danh mục
                </a>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <section class="filter-card card">
        <form method="GET" action="{{ route('admin.product-categories.index') }}" class="category-filter-grid">
            <div class="filter-search">
                <label for="category-search">Tìm kiếm</label>
                <div>
                    <x-ui.icon name="search" size="18" />
                    <input id="category-search" class="input" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tên, mã hoặc đường dẫn">
                </div>
            </div>
            <div>
                <label for="category-status">Trạng thái</label>
                <select id="category-status" class="select" name="status">
                    <option value="">Tất cả danh mục</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Đang hiển thị</option>
                    <option value="hidden" @selected(($filters['status'] ?? '') === 'hidden')>Đang ẩn</option>
                    <option value="trashed" @selected(($filters['status'] ?? '') === 'trashed')>Thùng rác</option>
                </select>
            </div>
            <div class="filter-actions">
                <button class="button button-primary" type="submit"><x-ui.icon name="filter" size="18" /> Lọc</button>
                <a class="button button-ghost" href="{{ route('admin.product-categories.index') }}">Đặt lại</a>
            </div>
        </form>
    </section>

    <section class="card category-list-card">
        @if($categories->isEmpty())
            <x-ui.empty-state
                title="{{ ($filters['status'] ?? '') === 'trashed' ? 'Thùng rác đang trống' : 'Chưa có danh mục phù hợp' }}"
                description="Thử thay đổi bộ lọc hoặc thêm danh mục sản phẩm mới."
            >
                @can('products.create')
                    <a class="button button-primary" href="{{ route('admin.product-categories.create') }}">
                        <x-ui.icon name="plus" size="18" /> Thêm danh mục
                    </a>
                @endcan
            </x-ui.empty-state>
        @else
            @if(($filters['status'] ?? '') !== 'trashed')
                <div x-data="bulkCategories()">
                    <form id="category-bulk-form" method="POST" action="{{ route('admin.product-categories.bulk') }}" @submit="validateSelection($event)">
                        @csrf
                    </form>
                    <div class="category-toolbar">
                        <div class="category-toolbar-actions">
                            @can('products.update')
                                <button class="button button-success" type="submit" form="category-bulk-form" name="action" value="hide"><x-ui.icon name="eye-off" size="17" /> Ẩn</button>
                                <button class="button button-success" type="submit" form="category-bulk-form" name="action" value="show"><x-ui.icon name="eye" size="17" /> Hiện</button>
                                <button class="button button-secondary" type="submit" form="category-bulk-form" name="action" value="reorder"><x-ui.icon name="save" size="17" /> Cập nhật thứ tự</button>
                            @endcan
                            @can('products.delete')
                                <button class="button button-danger" type="submit" form="category-bulk-form" name="action" value="delete" @click="confirmDelete($event)">
                                    <x-ui.icon name="trash" size="17" /> Xóa
                                </button>
                            @endcan
                        </div>
                        <span class="category-selection-count" x-text="selectedLabel">Chưa chọn danh mục</span>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table category-table">
                            <thead>
                                <tr>
                                    <th class="selection-column"><input class="table-checkbox" type="checkbox" aria-label="Chọn tất cả danh mục" x-ref="selectAll" @change="toggleAll($event)"></th>
                                    <th class="order-column">Thứ tự</th>
                                    <th>Tên danh mục</th>
                                    <th>Danh mục cha</th>
                                    <th>Sản phẩm</th>
                                    <th>Trạng thái</th>
                                    <th>Cập nhật</th>
                                    <th class="table-actions-heading">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                    @php
                                        $name = $category->getTranslation('name', 'vi', false) ?: 'Chưa có tên';
                                        $slug = $category->getTranslation('slug', 'vi', false);
                                    @endphp
                                    <tr class="{{ $category->is_active ? '' : 'is-muted-row' }}">
                                        <td><input class="table-checkbox category-row-checkbox" type="checkbox" form="category-bulk-form" name="category_ids[]" value="{{ $category->id }}" aria-label="Chọn {{ $name }}" @change="syncSelection()"></td>
                                        <td><input class="input order-input" type="number" min="0" max="999999" form="category-bulk-form" name="sort_orders[{{ $category->id }}]" value="{{ $category->sort_order }}" aria-label="Thứ tự của {{ $name }}"></td>
                                        <td>
                                            <div class="category-name-cell">
                                                <strong>{{ $name }}</strong>
                                                @if($slug)<small><x-ui.icon name="link" size="14" /> /vi/{{ $slug }}.html</small>@endif
                                                <small><x-ui.icon name="history" size="14" /> Tạo {{ $category->created_at->format('d/m/Y H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $category->parent?->getTranslation('name', 'vi', false) ?: '—' }}</td>
                                        <td><span class="category-product-count">{{ $category->products_count }}</span></td>
                                        <td><x-ui.badge :tone="$category->is_active ? 'success' : 'neutral'">{{ $category->is_active ? 'Đang hiển thị' : 'Đang ẩn' }}</x-ui.badge></td>
                                        <td><time title="{{ $category->updated_at }}">{{ $category->updated_at->diffForHumans() }}</time></td>
                                        <td>
                                            <div class="row-actions">
                                                @can('products.update')
                                                    <a class="icon-button is-success" href="{{ route('admin.product-categories.edit', $category) }}" title="Sửa danh mục" aria-label="Sửa {{ $name }}"><x-ui.icon name="edit" size="18" /></a>
                                                    <form method="POST" action="{{ route('admin.product-categories.visibility', $category) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="is_active" value="{{ $category->is_active ? 0 : 1 }}">
                                                        <button class="icon-button {{ $category->is_active ? 'is-dark' : 'is-success' }}" type="submit" title="{{ $category->is_active ? 'Ẩn danh mục' : 'Hiện danh mục' }}" aria-label="{{ $category->is_active ? 'Ẩn' : 'Hiện' }} {{ $name }}">
                                                            <x-ui.icon :name="$category->is_active ? 'eye-off' : 'eye'" size="18" />
                                                        </button>
                                                    </form>
                                                @endcan
                                                @can('products.delete')
                                                    <form id="delete-category-{{ $category->id }}" method="POST" action="{{ route('admin.product-categories.destroy', $category) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                    <x-ui.confirm-dialog
                                                        form-id="delete-category-{{ $category->id }}"
                                                        title="Xóa danh mục?"
                                                        message="Danh mục “{{ $name }}” sẽ vào thùng rác. {{ $category->products_count }} sản phẩm liên quan vẫn được giữ nguyên."
                                                        confirm-label="Xóa danh mục"
                                                    >
                                                        <x-slot:trigger><button class="icon-button is-danger" type="button" title="Xóa danh mục" aria-label="Xóa {{ $name }}"><x-ui.icon name="trash" size="18" /></button></x-slot:trigger>
                                                    </x-ui.confirm-dialog>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="data-table category-table">
                        <thead><tr><th>Tên danh mục</th><th>Mã</th><th>Đã xóa</th><th class="table-actions-heading">Thao tác</th></tr></thead>
                        <tbody>
                            @foreach($categories as $category)
                                @php($name = $category->getTranslation('name', 'vi', false) ?: 'Chưa có tên')
                                <tr>
                                    <td><strong>{{ $name }}</strong></td>
                                    <td><code>{{ $category->code ?: '—' }}</code></td>
                                    <td>{{ $category->deleted_at?->format('d/m/Y H:i') }}</td>
                                    <td><div class="row-actions">
                                        @can('products.delete')
                                            <form method="POST" action="{{ route('admin.product-categories.restore', $category->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="button button-secondary" type="submit"><x-ui.icon name="restore" size="17" /> Khôi phục</button>
                                            </form>
                                        @endcan
                                    </div></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <x-ui.pagination :paginator="$categories" />
        @endif
    </section>
@endsection
