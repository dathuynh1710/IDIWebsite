@extends('layouts.admin')

@section('title', 'Sửa danh mục sản phẩm - '.config('admin.name'))
@section('page-context', 'Sửa danh mục sản phẩm')

@section('content')
    <x-admin.page-header title="Sửa danh mục: {{ $category->getTranslation('name', 'vi', false) }}" description="Cập nhật tên, đường dẫn, thứ tự và trạng thái hiển thị" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            <a class="button button-secondary" href="{{ route('admin.product-categories.index') }}"><x-ui.icon name="arrow-left" size="18" /> Quay lại</a>
            <x-ui.button type="submit" form="category-form" icon="save">Lưu thay đổi</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>
    <form id="category-form" method="POST" action="{{ route('admin.product-categories.update', $category) }}" data-dirty-form>
        @csrf
        @method('PUT')
        @include('admin.product-categories._form')
    </form>
@endsection
