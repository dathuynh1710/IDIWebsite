@extends('layouts.admin')

@section('title', 'Thêm sản phẩm - '.config('admin.name'))
@section('page-context', 'Thêm sản phẩm')

@section('content')
    <x-admin.page-header title="Thêm sản phẩm mới" description="Tạo thông tin sản phẩm và nội dung cho từng ngôn ngữ" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.products.index') }}"><x-ui.icon name="arrow-left" size="18" /> Quay lại</a>
            <x-ui.button type="submit" form="product-form" icon="save">Lưu sản phẩm</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>
    <form id="product-form" method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" data-dirty-form>
        @csrf
        @include('admin.products._form', ['product' => null])
    </form>
@endsection
