@extends('layouts.admin')

@section('title', 'Bảng điều khiển - '.config('admin.name'))
@section('page-context', 'Bảng điều khiển')

@section('content')
    <x-admin.page-header title="Bảng điều khiển" description="Tổng quan hoạt động nội dung IDI Seafood" :breadcrumbs="$breadcrumbs" />

    <div class="stat-grid">
        @foreach($stats as $stat)
            <article class="stat-card stat-{{ $stat['tone'] }}">
                <span><x-ui.icon :name="$stat['icon']" /></span>
                <div><strong>{{ number_format($stat['value']) }}</strong><p>{{ $stat['label'] }}</p></div>
            </article>
        @endforeach
    </div>

    <div class="dashboard-grid">
        <x-ui.card title="Sản phẩm cập nhật gần đây">
            @if($recentProducts->isEmpty())
                <x-ui.empty-state title="Chưa có sản phẩm" description="Bắt đầu bằng việc tạo sản phẩm đầu tiên.">
                    @can('products.create')
                        <a class="button button-primary" href="{{ route('admin.products.create') }}">Thêm sản phẩm</a>
                    @endcan
                </x-ui.empty-state>
            @else
                <div class="compact-list">
                    @foreach($recentProducts as $product)
                        <a href="{{ route('admin.products.edit', $product) }}">
                            <span class="list-icon"><x-ui.icon name="package" /></span>
                            <span><strong>{{ $product->getTranslation('title', 'vi', false) ?: $product->sku }}</strong><small>{{ $product->category?->getTranslation('name', 'vi', false) ?: 'Chưa phân loại' }}</small></span>
                            <time>{{ $product->updated_at->diffForHumans() }}</time>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-ui.card>

        <x-ui.card title="Thao tác nhanh">
            <div class="quick-actions">
                @can('products.create')
                    <a href="{{ route('admin.products.create') }}"><x-ui.icon name="plus" /><span><strong>Thêm sản phẩm</strong><small>Tạo nội dung đa ngôn ngữ</small></span></a>
                @endcan
                @can('products.view')
                    <a href="{{ route('admin.products.index') }}"><x-ui.icon name="package" /><span><strong>Quản lý sản phẩm</strong><small>Lọc, sửa và xuất bản</small></span></a>
                @endcan
                <a href="#"><x-ui.icon name="image" /><span><strong>Thư viện media</strong><small>Quản lý ảnh và tài liệu</small></span></a>
            </div>
        </x-ui.card>
    </div>
@endsection
