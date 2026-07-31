@extends('layouts.admin')

@section('title', 'Thêm danh mục sản phẩm - '.config('admin.name'))
@section('page-context', 'Thêm danh mục sản phẩm')

@section('content')
    <x-admin.page-header title="Thêm danh mục sản phẩm" description="Tạo nhóm sản phẩm mới và nội dung theo từng ngôn ngữ" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.product-categories.index') }}"><x-ui.icon name="arrow-left" size="18" /> Quay lại</a>
            <x-ui.button type="submit" form="category-form" icon="save">Lưu danh mục</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>
    <form id="category-form" method="POST" action="{{ route('admin.product-categories.store') }}" data-dirty-form>
        @csrf
        @include('admin.product-categories._form', ['category' => null])
    </form>
@endsection
