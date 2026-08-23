@php
    $title = $recipe->getTranslation('title', $locale, false) ?: $recipe->code;
    $summary = $recipe->getTranslation('summary', $locale, false);
    $contentLeft = $recipe->getTranslation('content_left', $locale, false);
    $contentRight = $recipe->getTranslation('content_right', $locale, false);
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Xem trước: {{ $title }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;color:#182b3f;background:#f2f6f8;font-family:Inter,system-ui,sans-serif;line-height:1.65}.bar{position:sticky;top:0;z-index:2;background:#fff;border-bottom:1px solid #dce5ea}.bar div{width:min(1120px,calc(100% - 32px));min-height:64px;margin:auto;display:flex;align-items:center;justify-content:space-between}.bar a{color:#165c83;text-decoration:none;font-weight:700}.page{width:min(1120px,calc(100% - 32px));margin:34px auto 64px}.hero{display:grid;grid-template-columns:minmax(300px,.9fr) minmax(0,1.1fr);gap:42px;align-items:center;padding:38px;border-radius:20px;background:#fff;box-shadow:0 16px 45px #15354d12}.media{overflow:hidden;aspect-ratio:5/3;border-radius:15px;background:#e8eff3;display:grid;place-items:center}.media img,.media video{width:100%;height:100%;object-fit:cover}h1{margin:12px 0;color:#123c58;font-size:clamp(34px,5vw,56px);line-height:1.08}.eyebrow{color:#2780a9;font-weight:800;text-transform:uppercase;letter-spacing:.08em;font-size:12px}.lead{font-size:18px;color:#516474}.body{display:grid;grid-template-columns:1fr 1fr;gap:22px;margin-top:22px}.card{min-width:0;padding:28px;border-radius:16px;background:#fff}.card img{max-width:100%;height:auto}.card>:first-child{margin-top:0}.card>:last-child{margin-bottom:0}@media(max-width:760px){.hero,.body{grid-template-columns:1fr}.hero{padding:22px}}
    </style>
</head>
<body>
    <header class="bar"><div><strong>IDI Seafood · Xem trước Recipe</strong><a href="{{ route('admin.recipes.edit', $recipe) }}">← Quay lại chỉnh sửa</a></div></header>
    <main class="page">
        <section class="hero">
            <div class="media">@if($recipe->videoMedia)<video controls poster="{{ $recipe->featuredMedia?->url }}"><source src="{{ $recipe->videoMedia->url }}" type="{{ $recipe->videoMedia->mime_type }}"></video>@elseif($recipe->featuredMedia)<img src="{{ $recipe->featuredMedia->url }}" alt="{{ $title }}">@else<span>Chưa có ảnh công thức</span>@endif</div>
            <div><span class="eyebrow">Recipe · {{ strtoupper($locale) }}</span><h1>{{ $title }}</h1>@if($summary)<p class="lead">{{ $summary }}</p>@endif</div>
        </section>
        @if($contentLeft || $contentRight)
            <div class="body">
                <section class="card">{!! $contentLeft !!}</section>
                <section class="card">{!! $contentRight !!}</section>
            </div>
        @endif
    </main>
</body>
</html>
