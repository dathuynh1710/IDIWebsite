<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Toast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const LOCALES = [
        'vi' => 'Tiếng Việt',
        'en' => 'English',
        'zh' => '中文',
    ];

    private const STATUSES = [
        'draft' => 'Bản nháp',
        'translating' => 'Đang dịch',
        'review' => 'Chờ duyệt',
        'scheduled' => 'Đã lên lịch',
        'published' => 'Đã xuất bản',
        'hidden' => 'Tạm ẩn',
        'archived' => 'Lưu trữ',
    ];

    public function index(Request $request): View
    {
        Gate::authorize('products.view');

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer'],
            'active' => ['nullable', 'in:0,1'],
            'date_from' => ['nullable', 'date'],
        ]);

        return view('admin.products.index', [
            'products' => Product::query()
                ->with(['category', 'featuredMedia'])
                ->filtered($filters)
                ->orderByDesc('sort_order')
                ->latest('updated_at')
                ->paginate(15)
                ->withQueryString(),
            'categories' => ProductCategory::orderBy('sort_order')->get(),
            'filters' => $filters,
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Sản phẩm'],
            ],
        ]);
    }

    public function create(): View
    {
        Gate::authorize('products.create');

        return view('admin.products.create', $this->formData());
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = DB::transaction(function () use ($request): Product {
            $data = $this->productData($request);
            $data['created_by'] = $request->user()->id;
            $data['updated_by'] = $request->user()->id;

            return Product::create($data);
        });

        return redirect()
            ->route('admin.products.edit', $product)
            ->with(Toast::success('Tạo sản phẩm thành công.'));
    }

    public function edit(Product $product): View
    {
        Gate::authorize('products.update');

        return view('admin.products.edit', $this->formData($product));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product): void {
            $data = $this->productData($request, $product);
            $data['updated_by'] = $request->user()->id;
            $product->update($data);
        });

        return redirect()
            ->route('admin.products.edit', $product)
            ->with(Toast::success('Cập nhật sản phẩm thành công.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('products.delete');
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with(Toast::success('Đã chuyển sản phẩm vào thùng rác.'));
    }

    public function duplicate(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('products.create');

        $copy = DB::transaction(function () use ($request, $product): Product {
            $copy = $product->replicate();
            $copy->sku = Str::limit($product->sku.'-COPY-'.now()->format('His'), 100, '');
            $copy->title = collect($product->getTranslations('title'))
                ->map(fn (string $title): string => $title.' (Bản sao)')
                ->all();
            $copy->slug = collect($product->getTranslations('slug'))
                ->map(fn (string $slug): string => Str::limit($slug.'-copy-'.now()->format('His'), 255, ''))
                ->all();
            $copy->translation_status = collect(self::LOCALES)->mapWithKeys(fn ($label, $locale) => [$locale => 'draft'])->all();
            $copy->locale_published_at = [];
            $copy->sort_order = ((int) Product::max('sort_order')) + 1;
            $copy->created_by = $request->user()->id;
            $copy->updated_by = $request->user()->id;
            $copy->save();

            return $copy;
        });

        return redirect()->route('admin.products.edit', $copy)->with(Toast::success('Đã nhân bản sản phẩm.'));
    }

    public function preview(Product $product): View
    {
        Gate::authorize('products.view');

        return view('admin.products.preview', ['product' => $product->load(['category', 'featuredMedia'])]);
    }

    public function uploadEditorImage(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('products.create') || $request->user()->can('products.update'), 403);

        $validated = $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);
        $file = $validated['file'];
        $fileName = Str::uuid().'.'.$file->extension();
        $file->storeAs('editor', $fileName, 'public');
        $dimensions = @getimagesize($file->getRealPath()) ?: [null, null];
        $media = Media::create([
            'disk' => 'public',
            'directory' => 'editor',
            'file_name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->extension(),
            'file_size' => $file->getSize(),
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'title' => ['vi' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)],
            'alt_text' => ['vi' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)],
            'created_by' => $request->user()->id,
        ]);

        return Toast::json('Tải ảnh lên thành công.', 'success', ['url' => $media->url]);
    }

    private function formData(?Product $product = null): array
    {
        return [
            'product' => $product,
            'categories' => ProductCategory::orderBy('sort_order')->get(),
            'locales' => self::LOCALES,
            'statuses' => self::STATUSES,
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Sản phẩm', 'route' => 'admin.products.index'],
                ['label' => $product ? 'Chỉnh sửa' : 'Thêm mới'],
            ],
        ];
    }

    private function productData(StoreProductRequest|UpdateProductRequest $request, ?Product $product = null): array
    {
        $validated = $request->validated();
        $localized = [];

        foreach (['title', 'slug', 'seo_title', 'meta_description', 'translation_status', 'locale_published_at'] as $field) {
            $localized[$field] = collect($validated[$field] ?? [])
                ->map(fn ($value) => is_string($value) ? trim($value) : $value)
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->all();
        }

        foreach (['short_description', 'content'] as $field) {
            $localized[$field] = collect($validated[$field] ?? [])
                ->map(fn (?string $html): string => $this->sanitizeHtml((string) $html))
                ->filter()
                ->all();
        }

        $mediaId = $product?->featured_media_id;
        if ($request->boolean('remove_image')) {
            $mediaId = null;
        }
        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $fileName = Str::uuid().'.'.$file->extension();
            $file->storeAs('products', $fileName, 'public');
            $dimensions = @getimagesize($file->getRealPath()) ?: [null, null];
            $mediaId = Media::create([
                'disk' => 'public',
                'directory' => 'products',
                'file_name' => $fileName,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->extension(),
                'file_size' => $file->getSize(),
                'width' => $dimensions[0],
                'height' => $dimensions[1],
                'title' => ['vi' => $localized['title']['vi'] ?? $file->getClientOriginalName()],
                'alt_text' => ['vi' => $localized['title']['vi'] ?? 'Ảnh sản phẩm'],
                'created_by' => $request->user()->id,
            ])->id;
        }

        return array_merge($localized, [
            'sku' => trim($validated['sku']),
            'product_category_id' => $validated['product_category_id'] ?? null,
            'featured_media_id' => $mediaId,
            'sort_order' => $validated['sort_order'],
            'is_featured' => $validated['is_featured'],
            'is_active' => $validated['is_active'],
        ]);
    }

    private function sanitizeHtml(string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object|embed)[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = preg_replace('/\son\w+\s*=\s*(["\']).*?\1/is', '', $html) ?? '';
        $html = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:.*?\2/is', '$1="#"', $html) ?? '';

        return trim(strip_tags($html, '<p><br><h2><h3><h4><strong><b><em><i><u><ul><ol><li><a><blockquote><pre><code><table><thead><tbody><tr><th><td><img>'));
    }
}
