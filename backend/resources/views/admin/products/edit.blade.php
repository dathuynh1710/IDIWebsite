@extends('layouts.admin')

@section('title', 'Sửa '.$product->sku.' - '.config('admin.name'))
@section('page-context', 'Chỉnh sửa sản phẩm')

@section('content')
    <x-admin.page-header title="Sửa sản phẩm #{{ $product->sku }}" description="Cập nhật thông tin chung, bản dịch và trạng thái xuất bản" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.products.index') }}"><x-ui.icon name="arrow-left" size="18" /> Quay lại</a>
            <a class="button button-ghost" href="{{ route('admin.products.preview', $product) }}" target="_blank"><x-ui.icon name="eye" size="18" /> Xem trước</a>
            <x-ui.button type="submit" form="product-form" icon="save">Lưu thay đổi</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>
    <form id="product-form" method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" data-dirty-form>
        @csrf
        @method('PUT')
        @include('admin.products._form', ['product' => $product])
    </form>
@endsection
