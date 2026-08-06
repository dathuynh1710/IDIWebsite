@php
    $toast = session('toast');
    $validationMessages = $errors->all();

    if (! $toast && $validationMessages !== []) {
        $toast = \App\Support\Toast::payload(
            implode(' ', array_values(array_unique($validationMessages))),
            'error',
        );
    }
@endphp

<script id="toast-bootstrap" type="application/json">{!! json_encode($toast, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
