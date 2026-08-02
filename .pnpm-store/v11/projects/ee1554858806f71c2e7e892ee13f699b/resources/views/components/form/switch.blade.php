@props(['name', 'label', 'checked' => false, 'helper' => null])
@php
    $errorKey = str_replace(['][', '[', ']'], ['.', '.', ''], $name);
    $fieldId = str_replace(['.', '[', ']'], '-', $name);
    $isChecked = (bool) old($errorKey, $checked);
@endphp
<div class="switch-field">
    <input type="hidden" name="{{ $name }}" value="0">
    <label for="{{ $fieldId }}">
        <input id="{{ $fieldId }}" type="checkbox" name="{{ $name }}" value="1" @checked($isChecked) {{ $attributes }}>
        <span class="switch-track" aria-hidden="true"><span></span></span>
        <span><strong>{{ $label }}</strong>@if($helper)<small>{{ $helper }}</small>@endif</span>
    </label>
</div>
