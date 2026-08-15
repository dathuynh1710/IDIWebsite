@props(['title', 'description' => null, 'breadcrumbs' => []])
<div {{ $attributes->class(['page-heading']) }}>
    <div>
        <h1>{{ $title }}</h1>
    </div>
    @isset($actions)
        <div class="page-actions">{{ $actions }}</div>
    @endisset
</div>
