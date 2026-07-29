@props(['name', 'label', 'options' => [], 'selected' => null, 'placeholder' => null, 'required' => false])
@php
    $errorKey = str_replace(['][', '[', ']'], ['.', '.', ''], $name);
    $fieldId = str_replace(['.', '[', ']'], '-', $name);
    $current = old($errorKey, $selected);
@endphp
<div class="form-field">
    <label for="{{ $fieldId }}">{{ $label }} @if($required)<span aria-hidden="true">*</span>@endif</label>
    <select id="{{ $fieldId }}" name="{{ $name }}" @if($required) required @endif
        @if($errors->has($errorKey)) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @endif
        {{ $attributes->class(['select', 'is-invalid' => $errors->has($errorKey)]) }}>
        @if($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach($options as $value => $text)
            <option value="{{ $value }}" @selected((string) $current === (string) $value)>{{ $text }}</option>
        @endforeach
    </select>
    <x-form.field-error :name="$errorKey" />
</div>
