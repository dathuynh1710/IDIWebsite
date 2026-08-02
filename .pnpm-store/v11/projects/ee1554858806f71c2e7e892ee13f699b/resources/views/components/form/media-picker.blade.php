@props(['name' => 'featured_image', 'media' => null])
<div class="media-picker" x-data="mediaPicker(@js($media?->url))">
    <div class="media-preview" :class="{ 'has-image': preview }">
        <img x-show="preview" :src="preview" alt="Xem trước ảnh sản phẩm" x-on:error="preview = null">
        <div x-show="!preview" class="media-placeholder">
            <x-ui.icon name="image" size="32" />
            <span>Chưa có ảnh</span>
        </div>
    </div>
    <input type="hidden" name="remove_image" :value="removed ? 1 : 0">
    <input x-ref="input" id="{{ $name }}" class="sr-only" type="file" name="{{ $name }}" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" @change="pick($event)">
    <div class="media-actions">
        <label class="button button-secondary" for="{{ $name }}"><x-ui.icon name="upload" size="18" /> Chọn ảnh</label>
        <button type="button" class="button button-ghost" x-show="preview" @click="remove()">Xóa ảnh</button>
    </div>
    <p class="field-help">JPG, PNG hoặc WEBP. Khuyến nghị 370 × 370px, tối đa 5MB.</p>
    <x-form.field-error :name="$name" />
</div>
