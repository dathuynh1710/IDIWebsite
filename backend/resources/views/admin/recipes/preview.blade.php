@php
    $title = $recipe->getTranslation('title', $locale, false) ?: $recipe->code;
    $summary = $recipe->getTranslation('summary', $locale, false);
    $content = $recipe->getTranslation('content', $locale, false);
    $difficulty = ['easy' => 'Dễ', 'medium' => 'Trung bình', 'hard' => 'Khó'][$recipe->difficulty] ?? '—';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Xem trước: {{ $title }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;color:#182b3f;background:#f2f6f8;font-family:Inter,system-ui,sans-serif;line-height:1.65}.bar{position:sticky;top:0;z-index:2;background:#fff;border-bottom:1px solid #dce5ea}.bar div{width:min(1120px,calc(100% - 32px));min-height:64px;margin:auto;display:flex;align-items:center;justify-content:space-between}.bar a{color:#165c83;text-decoration:none;font-weight:700}.page{width:min(1120px,calc(100% - 32px));margin:34px auto 64px}.hero{display:grid;grid-template-columns:minmax(300px,.9fr) minmax(0,1.1fr);gap:42px;align-items:center;padding:38px;border-radius:20px;background:#fff;box-shadow:0 16px 45px #15354d12}.media{overflow:hidden;aspect-ratio:5/3;border-radius:15px;background:#e8eff3;display:grid;place-items:center}.media img,.media video{width:100%;height:100%;object-fit:cover}h1{margin:12px 0;color:#123c58;font-size:clamp(34px,5vw,56px);line-height:1.08}.eyebrow{color:#2780a9;font-weight:800;text-transform:uppercase;letter-spacing:.08em;font-size:12px}.lead{font-size:18px;color:#516474}.facts{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-top:24px}.fact{padding:12px;border-radius:9px;background:#edf5f8}.fact small,.fact strong{display:block}.fact small{color:#718492}.body{display:grid;grid-template-columns:1fr 1.7fr;gap:22px;margin-top:22px}.body.is-single{grid-template-columns:1fr}.card{padding:28px;border-radius:16px;background:#fff}.card h2{margin-top:0;color:#123c58}.ingredient{display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid #e5ecef}.steps{counter-reset:step}.step{position:relative;padding:0 0 24px 44px}.step:before{counter-increment:step;content:counter(step);position:absolute;left:0;top:0;width:30px;height:30px;display:grid;place-items:center;border-radius:50%;color:#fff;background:#2780a9;font-weight:800}.content{margin-top:22px}.content img{max-width:100%}@media(max-width:760px){.hero,.body{grid-template-columns:1fr}.hero{padding:22px}.facts{grid-template-columns:1fr 1fr}}
    </style>
</head>
<body>
    <header class="bar"><div><strong>IDI Seafood · Xem trước Recipe</strong><a href="{{ route('admin.recipes.edit', $recipe) }}">← Quay lại chỉnh sửa</a></div></header>
    <main class="page">
        <section class="hero">
            <div class="media">@if($recipe->videoMedia)<video controls poster="{{ $recipe->featuredMedia?->url }}"><source src="{{ $recipe->videoMedia->url }}" type="{{ $recipe->videoMedia->mime_type }}"></video>@elseif($recipe->featuredMedia)<img src="{{ $recipe->featuredMedia->url }}" alt="{{ $title }}">@else<span>Chưa có ảnh công thức</span>@endif</div>
            <div><span class="eyebrow">Recipe · {{ strtoupper($locale) }}</span><h1>{{ $title }}</h1>@if($summary)<p class="lead">{{ $summary }}</p>@endif
                <div class="facts"><div class="fact"><small>Khẩu phần</small><strong>{{ $recipe->servings ?: '—' }}</strong></div><div class="fact"><small>Chuẩn bị</small><strong>{{ $recipe->preparation_time ?? 0 }} phút</strong></div><div class="fact"><small>Nấu</small><strong>{{ $recipe->cooking_time ?? 0 }} phút</strong></div><div class="fact"><small>Độ khó</small><strong>{{ $difficulty }}</strong></div></div>
            </div>
        </section>
        @if($recipe->show_ingredients || $recipe->show_steps)
            <div @class(['body', 'is-single' => ! $recipe->show_ingredients || ! $recipe->show_steps])>
                @if($recipe->show_ingredients)<section class="card"><h2>Nguyên liệu</h2>@forelse($recipe->ingredients as $item)<div class="ingredient"><span>{{ $item->getTranslation('name', $locale, false) ?: $item->getTranslation('name', 'vi', false) }}</span><strong>{{ trim(($item->quantity ?? '').' '.($item->getTranslation('unit', $locale, false) ?: '')) }}</strong></div>@empty<p>Chưa có nguyên liệu.</p>@endforelse</section>@endif
                @if($recipe->show_steps)<section class="card"><h2>Cách làm</h2><div class="steps">@forelse($recipe->steps as $item)<div class="step">{{ $item->getTranslation('instruction', $locale, false) ?: $item->getTranslation('instruction', 'vi', false) }}</div>@empty<p>Chưa có hướng dẫn.</p>@endforelse</div></section>@endif
            </div>
        @endif
        @if($content)<section class="card content">{!! $content !!}</section>@endif
    </main>
</body>
</html>
