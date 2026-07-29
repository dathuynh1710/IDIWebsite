@props(['name', 'label', 'value' => null, 'rows' => 4, 'required' => false, 'helper' => null])
@php
    $errorKey = str_replace(['][', '[', ']'], ['.', '.', ''], $name);
    $fieldId = str_replace(['.', '[', ']'], '-', $name);
@endphp
<div class="form-field">
    <label for="{{ $fieldId }}">{{ $label }} @if($required)<span aria-hidden="true">*</span>@endif</label>
    <textarea id="{{ $fieldId }}" name="{{ $name }}" rows="{{ $rows }}" @if($required) required @endif
        @if($errors->has($errorKey)) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @endif
        {{ $attributes->class(['textarea', 'is-invalid' => $errors->has($errorKey)]) }}>{{ old($errorKey, $value) }}</textarea>
    @if($helper)<p class="field-help">{{ $helper }}</p>@endif
    <x-form.field-error :name="$errorKey" />
</div>
