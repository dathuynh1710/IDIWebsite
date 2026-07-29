@props(['name', 'label' => 'Nội dung', 'value' => null])
@php
    $errorKey = str_replace(['][', '[', ']'], ['.', '.', ''], $name);
    $fieldId = 'editor-'.substr(md5($name), 0, 10);
@endphp
<div class="form-field rich-editor" x-ignore>
    <label for="{{ $fieldId }}">{{ $label }}</label>
    <textarea id="{{ $fieldId }}" class="rich-text-textarea" name="{{ $name }}"
        @if($errors->has($errorKey)) aria-invalid="true" @endif>{{ old($errorKey, $value) }}</textarea>
    <p class="field-help">Hỗ trợ tiêu đề, định dạng chữ, căn lề, liên kết, ảnh, bảng, danh sách và hoàn tác.</p>
    <x-form.field-error :name="$errorKey" />
</div>
