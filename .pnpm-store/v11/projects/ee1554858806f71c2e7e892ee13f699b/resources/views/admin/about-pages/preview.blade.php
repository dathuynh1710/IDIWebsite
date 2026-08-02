<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->getTranslation('seo_title', $locale, false) ?: $page->getTranslation('title', $locale, false) }}</title>
    <meta name="description" content="{{ $page->getTranslation('meta_description', $locale, false) }}">
    <style>
        body{margin:0;color:#243649;background:#f3f6f8;font-family:Arial,sans-serif}.preview-bar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:12px 24px;color:#fff;background:#173f68}.preview-bar a{color:#fff;text-decoration:none}.preview-languages{display:flex;gap:8px}.preview-languages a{padding:7px 11px;border:1px solid #ffffff55;border-radius:5px}.preview-languages a.active{color:#173f68;background:#fff}.preview-page{max-width:1040px;margin:32px auto;padding:48px;background:#fff;border-radius:12px;box-shadow:0 12px 35px #15314a18}.preview-page img{width:100%;max-height:460px;object-fit:cover;border-radius:9px}.preview-page h1{margin:26px 0 12px;font-size:40px}.preview-summary{color:#617387;font-size:18px;line-height:1.65}.preview-content{margin-top:30px;line-height:1.75}.preview-status{padding:3px 8px;border-radius:999px;color:#815d00;background:#fff2c6;font-size:12px}@media(max-width:700px){.preview-page{margin:14px;padding:24px}.preview-page h1{font-size:30px}}
    </style>
</head>
<body>
    <header class="preview-bar">
        <div><strong>Xem trước giới thiệu</strong> <span class="preview-status">{{ $page->is_active ? 'Đang hiển thị' : 'Đang ẩn' }}</span></div>
        <nav class="preview-languages">@foreach(['vi'=>'VI','en'=>'EN','zh'=>'中文'] as $code => $label)<a href="{{ route('admin.about-pages.preview', ['page' => $page, 'locale' => $code]) }}" class="{{ $locale === $code ? 'active' : '' }}">{{ $label }}</a>@endforeach</nav>
    </header>
    <main class="preview-page">
        @if($page->featuredMedia)<img src="{{ $page->featuredMedia->url }}" alt="{{ $page->getTranslation('title', $locale, false) }}">@endif
        <h1>{{ $page->getTranslation('title', $locale, false) ?: 'Chưa có bản dịch' }}</h1>
        <p class="preview-summary">{{ $page->getTranslation('summary', $locale, false) }}</p>
        <article class="preview-content">{!! $page->getTranslation('content', $locale, false) !!}</article>
    </main>
</body>
</html>
