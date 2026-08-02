@php
    $title = old("title.{$locale}", $product?->getTranslation('title', $locale, false) ?? '');
    $slug = old("slug.{$locale}", $product?->getTranslation('slug', $locale, false) ?? '');
    $wasPublished = $product?->getTranslation('translation_status', $locale, false) === 'published';
@endphp
<section id="panel-{{ $locale }}" class="tab-panel" role="tabpanel" aria-labelledby="tab-{{ $locale }}" x-show="active === '{{ $locale }}'" x-cloak
    x-data="productSlug(@js($title), @js($slug), {{ $wasPublished ? 'true' : 'false' }})">
    <x-form.section title="Nội dung {{ $label }}" description="Thông tin hiển thị cho phiên bản {{ $label }}" icon="languages">
        <div class="localized-fields">
            <div class="form-field">
                <label for="title-{{ $locale }}">Tên sản phẩm @if($locale === 'vi')<span aria-hidden="true">*</span>@endif</label>
                <input id="title-{{ $locale }}" class="input @error("title.{$locale}") is-invalid @enderror" name="title[{{ $locale }}]" x-model="title" @input="onTitle()" @if($locale === 'vi') required @endif>
                <x-form.field-error name="title.{{ $locale }}" />
            </div>
            <div class="form-field">
                <label for="slug-{{ $locale }}">Đường dẫn (slug) @if($locale === 'vi')<span aria-hidden="true">*</span>@endif</label>
                <div class="slug-input">
                    <span>/products/</span>
                    <input id="slug-{{ $locale }}" class="input @error("slug.{$locale}") is-invalid @enderror" name="slug[{{ $locale }}]" x-model="slug" @input="markSlugEdited()" @if($locale === 'vi') required @endif>
                    <button type="button" @click="regenerate()">Tạo lại</button>
                </div>
                <p class="field-help slug-warning" x-show="published && changed">Thay đổi slug của nội dung đã xuất bản có thể cần redirect 301.</p>
                <x-form.field-error name="slug.{{ $locale }}" />
            </div>
            <x-form.textarea name="short_description[{{ $locale }}]" label="Mô tả ngắn" :value="$product?->getTranslation('short_description', $locale, false)" rows="3" />
            <x-form.textarea name="description[{{ $locale }}]" label="Mô tả chi tiết" :value="$product?->getTranslation('description', $locale, false)" rows="4" />
            <x-form.rich-text-editor name="content[{{ $locale }}]" :value="$product?->getTranslation('content', $locale, false)" />
            <x-form.seo-fields :locale="$locale" :product="$product" />
            <x-form.publication-fields :locale="$locale" :product="$product" :statuses="$statuses" />
        </div>
    </x-form.section>
</section>
