@props(['title' => null])
<section {{ $attributes->class(['card']) }}>
    @if($title || isset($header))
        <header class="card-header">
            @if($title)<h2>{{ $title }}</h2>@endif
            {{ $header ?? '' }}
        </header>
    @endif
    <div class="card-body">{{ $slot }}</div>
</section>
