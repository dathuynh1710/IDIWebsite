@props([
    'name',
    'label' => 'Nội dung',
    'model' => null,
    'value' => null,
    'rows' => 9,
    'placeholder' => 'Nhập nội dung...',
])
@php
    $errorKey = str_replace(['][', '[', ']'], ['.', '.', ''], $name);
    $fieldId = 'ckeditor5-'.substr(md5($name), 0, 10);
@endphp
<div class="form-field ckeditor5-field">
    <label for="{{ $fieldId }}">{{ $label }}</label>
    <div class="ckeditor5-host" wire:ignore>
        <textarea id="{{ $fieldId }}" name="{{ $name }}" rows="{{ $rows }}"
            class="textarea ckeditor5-textarea" data-placeholder="{{ $placeholder }}"
            @if($model) wire:model="{{ $model }}" @endif
            @if($errors->has($errorKey)) aria-invalid="true" @endif>{{ old($errorKey, $value) }}</textarea>
    </div>
    <p class="field-help">Hỗ trợ đầy đủ định dạng chữ, màu sắc, căn lề, danh sách, bảng, ảnh, video, mã nguồn HTML và chế độ toàn màn hình.</p>
    <x-form.field-error :name="$errorKey" />
</div>
