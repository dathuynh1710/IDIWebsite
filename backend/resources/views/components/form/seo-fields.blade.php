@props(['locale', 'product' => null])
@php
    $seoTitle = old("seo_title.{$locale}", $product?->getTranslation('seo_title', $locale, false));
    $metaDescription = old("meta_description.{$locale}", $product?->getTranslation('meta_description', $locale, false));
@endphp
<details class="seo-panel" open x-data="{ seoTitle: @js($seoTitle ?? ''), metaDescription: @js($metaDescription ?? '') }">
    <summary>Thiết lập SEO</summary>
    <div class="seo-grid">
        <div class="form-field">
            <label for="seo-title-{{ $locale }}">Tiêu đề SEO</label>
            <input id="seo-title-{{ $locale }}" class="input" name="seo_title[{{ $locale }}]" x-model="seoTitle" maxlength="255">
            <p class="field-help"><span x-text="seoTitle.length"></span>/70 ký tự gợi ý</p>
            <x-form.field-error name="seo_title.{{ $locale }}" />
        </div>
        <div class="form-field">
            <label for="meta-description-{{ $locale }}">Meta description</label>
            <textarea id="meta-description-{{ $locale }}" class="textarea" rows="3" name="meta_description[{{ $locale }}]" x-model="metaDescription" maxlength="500"></textarea>
            <p class="field-help"><span x-text="metaDescription.length"></span>/160 ký tự gợi ý</p>
            <x-form.field-error name="meta_description.{{ $locale }}" />
        </div>
        <div class="snippet-preview">
            <small>Xem trước kết quả tìm kiếm</small>
            <strong x-text="seoTitle || 'Tiêu đề sản phẩm'"></strong>
            <span>idiseafood.com/products/...</span>
            <p x-text="metaDescription || 'Mô tả sản phẩm sẽ hiển thị tại đây.'"></p>
        </div>
    </div>
</details>
