@props(['items' => []])
<nav class="breadcrumb" aria-label="Breadcrumb">
    <ol>
        @foreach($items as $item)
            <li>
                @if(!$loop->last && isset($item['route']))
                    <a href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                @else
                    <span @if($loop->last) aria-current="page" @endif>{{ $item['label'] }}</span>
                @endif
                @unless($loop->last)<x-ui.icon name="chevron-right" size="14" />@endunless
            </li>
        @endforeach
    </ol>
</nav>
