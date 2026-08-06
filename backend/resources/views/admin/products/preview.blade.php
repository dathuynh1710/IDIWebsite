@php
    $title = $product->getTranslation('title', 'vi', false) ?: $product->sku;
    $shortDescription = $product->getTranslation('short_description', 'vi', false);
    $content = $product->getTranslation('content', 'vi', false);
    $category = $product->category?->getTranslation('name', 'vi', false) ?: 'Chưa phân loại';
    $status = $product->getTranslation('translation_status', 'vi', false) ?: 'draft';
    $statusLabel = [
        'draft' => 'Bản nháp',
        'translating' => 'Đang dịch',
        'review' => 'Chờ duyệt',
        'scheduled' => 'Đã lên lịch',
        'published' => 'Đã xuất bản',
        'hidden' => 'Tạm ẩn',
        'archived' => 'Lưu trữ',
    ][$status] ?? $status;
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Xem trước: {{ $title }}</title>
    <style>
        :root {
            color-scheme: light;
            --navy: #173454;
            --blue: #2868ad;
            --blue-soft: #eaf3fc;
            --ink: #172334;
            --muted: #68778a;
            --line: #dce4ed;
            --surface: #fff;
            --page: #f3f6fa;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--ink);
            background:
                radial-gradient(circle at 100% 0, #e4effb 0, transparent 32rem),
                var(--page);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.6;
        }

        .preview-toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 1px solid rgb(220 228 237 / 85%);
            background: rgb(255 255 255 / 88%);
            backdrop-filter: blur(14px);
        }

        .toolbar-inner {
            width: min(1180px, calc(100% - 40px));
            min-height: 72px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--navy);
        }

        .brand img { width: 42px; height: 42px; object-fit: contain; }
        .brand strong { display: block; font-size: 15px; }
        .brand small { display: block; color: var(--muted); font-size: 12px; }

        .back {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-height: 42px;
            padding: 0 16px;
            border: 1px solid var(--line);
            border-radius: 9px;
            color: var(--navy);
            background: var(--surface);
            font-size: 14px;
            font-weight: 650;
            text-decoration: none;
            transition: border-color .2s, background .2s, transform .2s;
        }

        .back:hover { border-color: #9ebfe2; background: var(--blue-soft); transform: translateY(-1px); }

        .preview {
            width: min(1180px, calc(100% - 40px));
            margin: 38px auto 64px;
        }

        .preview-label {
            margin: 0 0 14px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .product-hero {
            display: grid;
            grid-template-columns: minmax(320px, .88fr) minmax(0, 1.12fr);
            gap: clamp(32px, 6vw, 76px);
            align-items: center;
            padding: clamp(28px, 5vw, 60px);
            border: 1px solid rgb(220 228 237 / 90%);
            border-radius: 22px;
            background: var(--surface);
            box-shadow: 0 18px 50px rgb(23 52 84 / 8%);
        }

        .product-media {
            position: relative;
            min-width: 0;
            margin: 0;
            overflow: hidden;
            aspect-ratio: 1;
            border-radius: 18px;
            background: linear-gradient(145deg, #edf3f8, #f9fbfd);
        }

        .product-media img { width: 100%; height: 100%; display: block; object-fit: cover; }

        .image-placeholder {
            width: 100%;
            height: 100%;
            display: grid;
            place-items: center;
            color: #91a1b4;
            font-size: 15px;
        }

        .eyebrow {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 9px;
            margin-bottom: 18px;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0 11px;
            border-radius: 999px;
            color: var(--blue);
            background: var(--blue-soft);
            font-size: 12px;
            font-weight: 750;
        }

        .sku { color: var(--muted); font-size: 13px; font-weight: 650; }

        h1 {
            max-width: 720px;
            margin: 0;
            color: var(--navy);
            font-size: clamp(34px, 5vw, 58px);
            line-height: 1.08;
            letter-spacing: -.035em;
        }

        .scientific-name {
            margin: 14px 0 0;
            color: var(--muted);
            font-size: 17px;
            font-style: italic;
        }

        .lead {
            max-width: 680px;
            margin: 26px 0 0;
            color: #405065;
            font-size: 18px;
            line-height: 1.75;
        }

        .product-meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin: 32px 0 0;
        }

        .meta-item {
            padding: 15px 16px;
            border: 1px solid var(--line);
            border-radius: 11px;
            background: #fbfcfe;
        }

        .meta-item span { display: block; color: var(--muted); font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
        .meta-item strong { display: block; margin-top: 5px; color: var(--navy); font-size: 14px; }

        .product-body {
            display: grid;
            grid-template-columns: 240px minmax(0, 1fr);
            gap: clamp(28px, 6vw, 72px);
            margin-top: 24px;
            padding: clamp(28px, 5vw, 60px);
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--surface);
        }

        .section-heading span { color: var(--blue); font-size: 12px; font-weight: 750; letter-spacing: .08em; text-transform: uppercase; }
        .section-heading h2 { margin: 7px 0 0; color: var(--navy); font-size: 25px; line-height: 1.25; }

        .content { min-width: 0; color: #344459; font-size: 16px; line-height: 1.8; }
        .content > :first-child { margin-top: 0; }
        .content > :last-child { margin-bottom: 0; }
        .content img { max-width: 100%; height: auto; border-radius: 12px; }
        .content a { color: var(--blue); }
        .description { margin: 0 0 24px; padding-bottom: 24px; border-bottom: 1px solid var(--line); font-size: 17px; }

        @media (max-width: 840px) {
            .product-hero { grid-template-columns: 1fr; }
            .product-media { width: min(100%, 560px); margin: 0 auto; }
            .product-body { grid-template-columns: 1fr; }
        }

        @media (max-width: 600px) {
            .toolbar-inner, .preview { width: min(100% - 24px, 1180px); }
            .toolbar-inner { min-height: 62px; }
            .brand small { display: none; }
            .back { padding: 0 12px; }
            .back span { display: none; }
            .preview { margin-top: 20px; }
            .product-hero, .product-body { padding: 20px; border-radius: 15px; }
            .product-hero { gap: 26px; }
            .product-media { border-radius: 12px; }
            h1 { font-size: 34px; }
            .lead { margin-top: 18px; font-size: 16px; }
            .product-meta { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="preview-toolbar">
        <div class="toolbar-inner">
            <div class="brand">
                <img src="{{ asset('images/idi-logo.svg') }}" alt="IDI Seafood">
                <div><strong>IDI Seafood</strong><small>Chế độ xem trước sản phẩm</small></div>
            </div>
            <a class="back" href="{{ route('admin.products.edit', $product) }}">
                <b aria-hidden="true">←</b><span>Quay lại chỉnh sửa</span>
            </a>
        </div>
    </header>

    <main class="preview">
        <p class="preview-label">Bản xem trước nội dung tiếng Việt</p>

        <section class="product-hero">
            <figure class="product-media">
                @if($product->featuredMedia)
                    <img src="{{ $product->featuredMedia->url }}" alt="{{ $title }}">
                @else
                    <div class="image-placeholder">Chưa có ảnh sản phẩm</div>
                @endif
            </figure>

            <div class="product-summary">
                <div class="eyebrow">
                    <span class="pill">{{ $category }}</span>
                    <span class="sku">{{ $product->sku }}</span>
                </div>
                <h1>{{ $title }}</h1>
                @if($product->scientific_name)
                    <p class="scientific-name">{{ $product->scientific_name }}</p>
                @endif
                @if($shortDescription)
                    <div class="lead">{!! $shortDescription !!}</div>
                @endif

                <div class="product-meta">
                    <div class="meta-item"><span>Trạng thái</span><strong>{{ $statusLabel }}</strong></div>
                    <div class="meta-item"><span>Hiển thị</span><strong>{{ $product->is_active ? 'Đang bật' : 'Đang tắt' }}</strong></div>
                    <div class="meta-item"><span>Nổi bật</span><strong>{{ $product->is_featured ? 'Có' : 'Không' }}</strong></div>
                </div>
            </div>
        </section>

        @if($content)
            <section class="product-body">
                <div class="section-heading">
                    <span>Thông tin sản phẩm</span>
                    <h2>Nội dung chi tiết</h2>
                </div>
                <div class="content">
                    {!! $content !!}
                </div>
            </section>
        @endif
    </main>
</body>
</html>
