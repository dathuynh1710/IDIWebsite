@props([
    'name',
    'label',
    'value' => null,
    'type' => 'text',
    'required' => false,
    'helper' => null,
    'id' => null,
])
@php
    $errorKey = str_replace(['][', '[', ']'], ['.', '.', ''], $name);
    $fieldId = $id ?: str_replace(['.', '[', ']'], '-', $name);
    $describedBy = ($helper ? "{$fieldId}-help " : '').($errors->has($errorKey) ? "{$fieldId}-error" : '');
@endphp
<div class="form-field">
    <label for="{{ $fieldId }}">{{ $label }} @if($required)<span aria-hidden="true">*</span>@endif</label>
    <input
        id="{{ $fieldId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($errorKey, $value) }}"
        @if($required) required @endif
        @if($describedBy) aria-describedby="{{ trim($describedBy) }}" @endif
        @if($errors->has($errorKey)) aria-invalid="true" @endif
        {{ $attributes->class(['input', 'is-invalid' => $errors->has($errorKey)]) }}
    >
    @if($helper)<p id="{{ $fieldId }}-help" class="field-help">{{ $helper }}</p>@endif
    <x-form.field-error :name="$errorKey" />
</div>
