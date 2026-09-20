<?php

namespace App\Livewire\Admin\Products;

use App\Livewire\AdminComponent;
use App\Models\CatalogAttribute;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('layouts.admin')]
class Form extends AdminComponent
{
    use WithFileUploads;

    public ?Product $product = null;

    public bool $modal = false;

    public string $sku = '';

    public ?int $product_category_id = null;

    public $featured_image;

    public bool $remove_image = false;

    public int $sort_order = 0;

    public bool $is_featured = false;

    public bool $is_active = true;

    public array $enabled_locales = ['vi'];

    public array $title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $slug = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $short_description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $content = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $product_specification = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $presentation = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $packaging = ['vi' => '', 'en' => '', 'zh' => ''];

    public string $sizes = '';

    public array $nutrition = [
        'calories' => '',
        'protein' => '',
        'fat' => '',
        'saturated_fat' => '',
    ];

    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $locale_published_at = ['vi' => '', 'en' => '', 'zh' => ''];

    public function mount(?Product $product = null, bool $modal = false): void
    {
        $product = $product?->exists ? $product : null;
        $this->modal = $modal;
        Gate::authorize($product ? 'products.update' : 'products.create');
        $this->product = $product?->load('featuredMedia', 'productAttributes.attribute');
        if (! $product) {
            $this->sort_order = ((int) Product::max('sort_order')) + 1;

            return;
        }

        foreach (['sku', 'product_category_id', 'sort_order', 'is_featured', 'is_active'] as $field) {
            $this->{$field} = $product->{$field} ?? $this->{$field};
        }
        foreach (['title', 'slug', 'short_description', 'content', 'seo_title', 'meta_description', 'locale_published_at'] as $field) {
            foreach (['vi', 'en', 'zh'] as $locale) {
                $this->{$field}[$locale] = $product->getTranslation($field, $locale, false) ?? $this->{$field}[$locale];
            }
        }

        $details = $product->schema_extra ?? [];
        foreach (['vi', 'en', 'zh'] as $locale) {
            $this->product_specification[$locale] = $this->localizedDetail($details['product_specification'] ?? null, $locale);
            $this->packaging[$locale] = $this->localizedDetail($details['packaging'] ?? null, $locale);
        }

        $sizeAttribute = $product->productAttributes->first(fn ($item) => $item->attribute?->code === 'SIZE');
        $this->sizes = implode(PHP_EOL, $this->detailList($sizeAttribute?->value));

        $presentationAttribute = $product->productAttributes->first(fn ($item) => $item->attribute?->code === 'PACKING');
        foreach (['vi', 'en', 'zh'] as $locale) {
            $this->presentation[$locale] = implode(PHP_EOL, $this->detailList(
                $this->localizedDetailValue($presentationAttribute?->value, $locale)
            ));
        }

        foreach (array_keys($this->nutrition) as $field) {
            $this->nutrition[$field] = (string) ($details['nutrition'][$field] ?? '');
        }

        $this->enabled_locales = collect(['vi', 'en', 'zh'])
            ->filter(fn (string $locale): bool => $locale === 'vi' || $this->hasLocalizedContent($product, $locale))
            ->values()
            ->all();
    }

    public function updatedEnabledLocales(): void
    {
        $this->enabled_locales = collect($this->enabled_locales)
            ->push('vi')
            ->intersect(['vi', 'en', 'zh'])
            ->unique()
            ->sortBy(fn (string $locale): int => array_search($locale, ['vi', 'en', 'zh'], true))
            ->values()
            ->all();
    }

    public function generateSlug(string $locale): void
    {
        $this->slug[$locale] = Str::slug($this->title[$locale] ?? '');
    }

    public function removeFeaturedImage(): void
    {
        $this->featured_image = null;
        $this->remove_image = true;
    }

    public function save(): void
    {
        $this->updatedEnabledLocales();
        $this->slug = collect($this->slug)->map(fn ($value) => Str::slug((string) $value))->all();
        $productId = $this->product?->id;
        $rules = [
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'product_category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
            'is_featured' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sizes' => ['nullable', 'string', 'max:5000'],
            'nutrition' => ['array'],
            'nutrition.calories' => ['nullable', 'string', 'max:100'],
            'nutrition.protein' => ['nullable', 'string', 'max:100'],
            'nutrition.fat' => ['nullable', 'string', 'max:100'],
            'nutrition.saturated_fat' => ['nullable', 'string', 'max:100'],
            'enabled_locales' => ['required', 'array', 'min:1'],
            'enabled_locales.*' => ['required', Rule::in(['vi', 'en', 'zh'])],
        ];

        foreach ($this->enabled_locales as $locale) {
            $rules["title.{$locale}"] = ['required', 'string', 'max:255'];
            $rules["slug.{$locale}"] = ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'];
            $rules["short_description.{$locale}"] = ['nullable', 'string', 'max:5000'];
            $rules["content.{$locale}"] = ['nullable', 'string', 'max:100000'];
            $rules["product_specification.{$locale}"] = ['nullable', 'string', 'max:5000'];
            $rules["presentation.{$locale}"] = ['nullable', 'string', 'max:5000'];
            $rules["packaging.{$locale}"] = ['nullable', 'string', 'max:5000'];
            $rules["seo_title.{$locale}"] = ['nullable', 'string', 'max:255'];
            $rules["meta_description.{$locale}"] = ['nullable', 'string', 'max:500'];
            $rules["locale_published_at.{$locale}"] = ['nullable', 'date'];
        }

        $localeLabels = ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'];
        $attributes = [
            'sku' => 'Mã sản phẩm (SKU)',
            'product_category_id' => 'Danh mục sản phẩm',
            'featured_image' => 'Ảnh đại diện',
            'sort_order' => 'Thứ tự hiển thị',
            'is_featured' => 'Nổi bật',
            'is_active' => 'Trạng thái',
        ];
        foreach ($this->enabled_locales as $locale) {
            $label = $localeLabels[$locale] ?? $locale;
            $attributes["title.{$locale}"] = "Tên sản phẩm ({$label})";
            $attributes["slug.{$locale}"] = "Đường dẫn ({$label})";
            $attributes["short_description.{$locale}"] = "Mô tả ngắn ({$label})";
            $attributes["content.{$locale}"] = "Nội dung ({$label})";
            $attributes["seo_title.{$locale}"] = "Tiêu đề SEO ({$label})";
            $attributes["meta_description.{$locale}"] = "Meta description ({$label})";
            $attributes["locale_published_at.{$locale}"] = "Ngày đăng ({$label})";
        }

        $validated = $this->validate($rules, [], $attributes);
        $enabledLocales = collect($validated['enabled_locales'])->flip();

        $localized = [];
        foreach (['title', 'slug', 'seo_title', 'meta_description', 'locale_published_at'] as $field) {
            $localized[$field] = collect($validated[$field] ?? [])->intersectByKeys($enabledLocales)
                ->map(fn ($value) => is_string($value) ? trim($value) : $value)
                ->filter(fn ($value) => $value !== null && $value !== '')->all();
        }
        foreach (['short_description', 'content'] as $field) {
            $localized[$field] = collect($validated[$field] ?? [])->intersectByKeys($enabledLocales)
                ->map(fn ($html) => $this->sanitizeHtml((string) $html))->filter()->all();
        }
        $localized['translation_status'] = collect($validated['enabled_locales'])
            ->mapWithKeys(fn (string $locale): array => [$locale => 'published'])
            ->all();

        $detailTranslations = [];
        foreach (['product_specification', 'packaging'] as $field) {
            $detailTranslations[$field] = collect($validated[$field] ?? [])
                ->intersectByKeys($enabledLocales)
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->all();
        }
        $presentation = collect($validated['presentation'] ?? [])
            ->intersectByKeys($enabledLocales)
            ->map(fn ($value) => $this->parseDetailList((string) $value))
            ->filter()
            ->all();
        $sizes = $this->parseDetailList($validated['sizes'] ?? '');
        $nutrition = collect($validated['nutrition'] ?? [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->all();

        DB::transaction(function () use ($validated, $localized, $detailTranslations, $presentation, $sizes, $nutrition): void {
            $mediaId = $this->remove_image ? null : $this->product?->featured_media_id;
            if ($this->featured_image) {
                $fileName = Str::uuid().'.'.$this->featured_image->extension();
                $this->featured_image->storeAs('products', $fileName, 'public');
                $mediaId = Media::create([
                    'disk' => 'public', 'directory' => 'products', 'file_name' => $fileName,
                    'original_name' => $this->featured_image->getClientOriginalName(),
                    'mime_type' => $this->featured_image->getMimeType(),
                    'extension' => $this->featured_image->extension(),
                    'file_size' => $this->featured_image->getSize(),
                    'title' => ['vi' => $this->title['vi'] ?: $fileName],
                    'alt_text' => ['vi' => $this->title['vi'] ?: 'Ảnh sản phẩm'],
                    'created_by' => auth()->id(),
                ])->id;
            }

            $data = [
                'sku' => trim($validated['sku']),
                'product_category_id' => $validated['product_category_id'],
                'featured_media_id' => $mediaId,
                'sort_order' => $validated['sort_order'],
                'is_featured' => $validated['is_featured'],
                'is_active' => $validated['is_active'],
                'updated_by' => auth()->id(),
            ];

            $schemaExtra = $this->product?->schema_extra ?? [];
            $schemaExtra['product_specification'] = $detailTranslations['product_specification'];
            $schemaExtra['packaging'] = $detailTranslations['packaging'];
            $schemaExtra['nutrition'] = $nutrition;
            $data['schema_extra'] = $schemaExtra;

            if ($this->product?->exists) {
                foreach ($localized as $field => $translations) {
                    $this->product->replaceTranslations($field, $translations);
                }
                $this->product->update($data);
                $this->product->refresh()->load('featuredMedia');
            } else {
                $data['created_by'] = auth()->id();
                $this->product = Product::create(array_merge($data, $localized))->load('featuredMedia');
            }

            $this->syncProductAttribute('SIZE', $sizes, 0);
            $this->syncProductAttribute('PACKING', $presentation, 1);
        });

        $this->featured_image = null;
        $this->remove_image = false;
        if ($productId === null && ! $this->modal) {
            $this->js("history.replaceState({}, '', '".route('admin.products.edit', $this->product)."')");
        }
        $this->toast($productId ? 'Cập nhật sản phẩm thành công.' : 'Tạo sản phẩm thành công.');
        $this->dispatch('product-saved');
    }

    private function sanitizeHtml(string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object|embed)[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = preg_replace('/\son\w+\s*=\s*(["\']).*?\1/is', '', $html) ?? '';
        $html = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:.*?\2/is', '$1="#"', $html) ?? '';

        return trim(strip_tags($html, '<p><br><h2><h3><h4><strong><b><em><i><u><ul><ol><li><a><blockquote><pre><code><table><thead><tbody><tr><th><td><img>'));
    }

    private function syncProductAttribute(string $code, array $value, int $sortOrder): void
    {
        $attribute = CatalogAttribute::where('code', $code)->first();
        if (! $attribute || ! $this->product) {
            return;
        }

        if ($value === []) {
            $this->product->productAttributes()->where('attribute_id', $attribute->id)->delete();

            return;
        }

        $this->product->productAttributes()->updateOrCreate(
            ['attribute_id' => $attribute->id],
            ['value' => $value, 'numeric_value' => null, 'boolean_value' => null, 'sort_order' => $sortOrder],
        );
    }

    private function parseDetailList(string $value): array
    {
        return collect(preg_split('/[\r\n,]+/', $value) ?: [])
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function detailList(mixed $value): array
    {
        return is_array($value) && array_is_list($value) ? array_values(array_filter($value, 'filled')) : [];
    }

    private function localizedDetailValue(mixed $value, string $locale): mixed
    {
        if (! is_array($value) || array_is_list($value)) {
            return $value;
        }

        return $value[$locale] ?? null;
    }

    private function localizedDetail(mixed $value, string $locale): string
    {
        $localized = $this->localizedDetailValue($value, $locale);

        return is_string($localized) ? $localized : '';
    }

    private function hasLocalizedContent(Product $product, string $locale): bool
    {
        foreach (['title', 'slug', 'short_description', 'description', 'content', 'seo_title', 'meta_description'] as $field) {
            if (filled($product->getTranslation($field, $locale, false))) {
                return true;
            }
        }

        return false;
    }

    public function render()
    {
        return view('livewire.admin.products.form', [
            'categories' => ProductCategory::orderBy('sort_order')->get(),
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Sản phẩm', 'route' => 'admin.products.index'],
                ['label' => $this->product?->exists ? 'Chỉnh sửa' : 'Thêm mới'],
            ],
        ])->title(($this->product?->exists ? "Sửa {$this->product->sku}" : 'Thêm sản phẩm').' - '.config('admin.name'));
    }
}
