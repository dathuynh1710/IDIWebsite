
<div class="product-form-grid">
    <div class="product-form-sidebar">
        @include('admin.products.partials.general-fields')
    </div>
    <div class="product-form-content">
        <x-form.language-tabs :locales="$locales" :initial="$errors->hasAny(['title.en','slug.en','title.zh','slug.zh']) ? ($errors->hasAny(['title.en','slug.en']) ? 'en' : 'zh') : 'vi'">
            @foreach($locales as $locale => $label)
                @include('admin.products.partials.localized-fields', ['locale' => $locale, 'label' => $label])
            @endforeach
        </x-form.language-tabs>
    </div>
</div>

<div class="mobile-form-actions">
    <a class="button button-secondary" href="{{ route('admin.products.index') }}">Hủy</a>
    <x-ui.button type="submit" icon="save">{{ $product ? 'Lưu thay đổi' : 'Tạo sản phẩm' }}</x-ui.button>
</div>
