@props(['locales', 'initial' => 'vi'])
<div class="language-tabs" data-initial="{{ $initial }}" x-data="languageTabs('{{ $initial }}')">
    <div class="tab-list" role="tablist" aria-label="Ngôn ngữ nội dung">
        @foreach($locales as $code => $label)
            @php
                $hasError = collect($errors->keys())->contains(fn ($key) => str_ends_with($key, ".{$code}"));
            @endphp
            <button type="button" role="tab" id="tab-{{ $code }}" :aria-selected="active === '{{ $code }}'" aria-controls="panel-{{ $code }}"
                @click="select('{{ $code }}')" :class="{ 'is-active': active === '{{ $code }}' }">
                <span class="locale-code">{{ strtoupper($code) }}</span>
                {{ $label }}
                @if($hasError)<span class="tab-error" title="Có lỗi cần sửa">!</span>@endif
            </button>
        @endforeach
    </div>
    {{ $slot }}
</div>
