@props(['title', 'description' => null, 'icon' => 'package'])
<div class="empty-state">
    <span><x-ui.icon :name="$icon" size="32" /></span>
    <h3>{{ $title }}</h3>
    @if($description)<p>{{ $description }}</p>@endif
    {{ $slot }}
</div>
