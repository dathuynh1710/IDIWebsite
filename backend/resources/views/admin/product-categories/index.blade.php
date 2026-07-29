@extends('layouts.admin')
@section('title', 'Danh mục sản phẩm - '.config('admin.name'))
@section('page-context', 'Danh mục sản phẩm')
@section('content')
    <x-admin.page-header title="Danh mục sản phẩm" description="Danh mục đang được dùng trong catalog" :breadcrumbs="$breadcrumbs" />
    <section class="card">
        <div class="table-responsive"><table class="data-table"><thead><tr><th>Tên danh mục</th><th>Mã</th><th>Sản phẩm</th><th>Thứ tự</th><th>Trạng thái</th></tr></thead><tbody>
        @forelse($categories as $category)<tr><td><strong>{{ $category->getTranslation('name', 'vi', false) }}</strong></td><td><code>{{ $category->code }}</code></td><td>{{ $category->products_count }}</td><td>{{ $category->sort_order }}</td><td><x-ui.badge :tone="$category->is_active ? 'success' : 'neutral'">{{ $category->is_active ? 'Hoạt động' : 'Tạm ẩn' }}</x-ui.badge></td></tr>@empty<tr><td colspan="5"><x-ui.empty-state title="Chưa có danh mục" /></td></tr>@endforelse
        </tbody></table></div>
        <x-ui.pagination :paginator="$categories" />
    </section>
@endsection
