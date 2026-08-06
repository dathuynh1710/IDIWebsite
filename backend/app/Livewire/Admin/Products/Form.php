<?php

namespace App\Livewire\Admin\Products;

use App\Livewire\AdminComponent;
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

    public string $scientific_name = '';

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

    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $locale_published_at = ['vi' => '', 'en' => '', 'zh' => ''];

    public function mount(?Product $product = null, bool $modal = false): void
    {
        $product = $product?->exists ? $product : null;
        $this->modal = $modal;
        Gate::authorize($product ? 'products.update' : 'products.create');
        $this->product = $product?->load('featuredMedia');
        if (! $product) {
            return;
        }

        foreach (['sku', 'scientific_name', 'product_category_id', 'sort_order', 'is_featured', 'is_active'] as $field) {
            $this->{$field} = $product->{$field} ?? $this->{$field};
        }
        foreach (['title', 'slug', 'short_description', 'content', 'seo_title', 'meta_description', 'locale_published_at'] as $field) {
            foreach (['vi', 'en', 'zh'] as $locale) {
                $this->{$field}[$locale] = $product->getTranslation($field, $locale, false) ?? $this->{$field}[$locale];
            }
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
            'scientific_name' => ['nullable', 'string', 'max:255'],
            'product_category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
            'is_featured' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'enabled_locales' => ['required', 'array', 'min:1'],
            'enabled_locales.*' => ['required', Rule::in(['vi', 'en', 'zh'])],
        ];

        foreach ($this->enabled_locales as $locale) {
            $rules["title.{$locale}"] = ['required', 'string', 'max:255'];
            $rules["slug.{$locale}"] = ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'];
            $rules["short_description.{$locale}"] = ['nullable', 'string', 'max:5000'];
            $rules["content.{$locale}"] = ['nullable', 'string', 'max:100000'];
            $rules["seo_title.{$locale}"] = ['nullable', 'string', 'max:255'];
            $rules["meta_description.{$locale}"] = ['nullable', 'string', 'max:500'];
            $rules["locale_published_at.{$locale}"] = ['nullable', 'date'];
        }

        $validated = $this->validate($rules);
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

        DB::transaction(function () use ($validated, $localized): void {
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
                'scientific_name' => $validated['scientific_name'] ?: null,
                'product_category_id' => $validated['product_category_id'],
                'featured_media_id' => $mediaId,
                'sort_order' => $validated['sort_order'],
                'is_featured' => $validated['is_featured'],
                'is_active' => $validated['is_active'],
                'updated_by' => auth()->id(),
            ];

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
