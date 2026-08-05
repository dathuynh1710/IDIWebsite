@props(['locales', 'initial' => 'vi', 'enabledLocales' => null, 'idPrefix' => ''])
<div class="language-tabs" data-initial="{{ $initial }}" x-data="languageTabs('{{ $initial }}')">
    <div class="tab-list" role="tablist" aria-label="Ngôn ngữ nội dung">
        @foreach($locales as $code => $label)
            @php
                $hasError = collect($errors->keys())->contains(fn ($key) => str_ends_with($key, ".{$code}"));
                $isEnabled = $enabledLocales === null || in_array($code, $enabledLocales, true);
            @endphp
            <button type="button" role="tab" id="{{ $idPrefix }}tab-{{ $code }}" :aria-selected="active === '{{ $code }}'" aria-controls="{{ $idPrefix }}panel-{{ $code }}"
                @click="select('{{ $code }}')" :class="{ 'is-active': active === '{{ $code }}' }" @disabled(!$isEnabled)
                @if(!$isEnabled) class="is-disabled" aria-disabled="true" title="Bật ngôn ngữ này để nhập nội dung" @endif>
                <span class="locale-code">{{ strtoupper($code) }}</span>
                {{ $label }}
                @if($hasError)<span class="tab-error" title="Có lỗi cần sửa">!</span>@endif
            </button>
        @endforeach
    </div>
    {{ $slot }}
</div>
