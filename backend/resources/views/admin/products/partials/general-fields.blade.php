@php
    $categoryOptions = $categories->mapWithKeys(fn ($category) => [$category->id => $category->getTranslation('name', 'vi', false)])->all();
@endphp
<x-form.section title="Thông tin chung" description="Dữ liệu dùng chung cho mọi ngôn ngữ" icon="package">
    <div class="form-stack">
        <x-form.input name="sku" label="Mã sản phẩm (SKU)" :value="$product?->sku" placeholder="VD: IDI-PAN-001" required />
        <x-form.select name="product_category_id" label="Danh mục" :options="$categoryOptions" :selected="$product?->product_category_id" placeholder="Chọn danh mục" />
        <div class="form-field">
            <label>Ảnh đại diện</label>
            <x-form.media-picker :media="$product?->featuredMedia" />
        </div>
        <x-form.input name="sort_order" label="Thứ tự hiển thị" type="number" :value="$product?->sort_order ?? 0" min="0" required />
        <div class="switch-group">
            <x-form.switch name="is_featured" label="Sản phẩm nổi bật" :checked="$product?->is_featured ?? false" />
            <x-form.switch name="is_active" label="Đang hiển thị" :checked="$product?->is_active ?? true" />
        </div>
        @if($product)
            <div class="audit-info">
                <div><span>Ngày tạo</span><strong>{{ $product->created_at?->format('d/m/Y H:i') }}</strong></div>
                <div><span>Cập nhật</span><strong>{{ $product->updated_at?->format('d/m/Y H:i') }}</strong></div>
            </div>
        @endif
    </div>
</x-form.section>
