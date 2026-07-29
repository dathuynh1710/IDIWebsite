@props(['title', 'description' => null, 'icon' => null])
<section {{ $attributes->class(['form-section card']) }}>
    <header class="form-section-header">
        @if($icon)<span><x-ui.icon :name="$icon" /></span>@endif
        <div><h2>{{ $title }}</h2>@if($description)<p>{{ $description }}</p>@endif</div>
    </header>
    <div class="form-section-body">{{ $slot }}</div>
</section>
