@props(['title', 'description' => null, 'breadcrumbs' => []])
<div class="page-heading">
    <div>
        <x-admin.breadcrumb :items="$breadcrumbs" />
        <h1>{{ $title }}</h1>
        @if($description)<p>{{ $description }}</p>@endif
    </div>
    @isset($actions)
        <div class="page-actions">{{ $actions }}</div>
    @endisset
</div>
