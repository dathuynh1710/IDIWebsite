<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $slugs = collect($this->input('slug', []))
            ->map(fn (?string $slug): string => Str::slug((string) $slug))
            ->all();

        $this->merge([
            'slug' => $slugs,
            'is_featured' => $this->boolean('is_featured'),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => (int) $this->input('sort_order', 0),
        ]);
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $productId = is_object($product) ? $product->getKey() : $product;
        $statuses = 'draft,translating,review,scheduled,published,hidden,archived';

        return [
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'product_category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
            'is_featured' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'title' => ['required', 'array'],
            'title.vi' => ['required', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'title.zh' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'array'],
            'slug.vi' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'slug.en' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'slug.zh' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'short_description.*' => ['nullable', 'string', 'max:5000'],
            'content.*' => ['nullable', 'string', 'max:100000'],
            'seo_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description.*' => ['nullable', 'string', 'max:500'],
            'translation_status.*' => ['required', "in:{$statuses}"],
            'locale_published_at.*' => ['nullable', 'date'],
            'locale_published_at.vi' => ['required_if:translation_status.vi,scheduled'],
            'locale_published_at.en' => ['required_if:translation_status.en,scheduled'],
            'locale_published_at.zh' => ['required_if:translation_status.zh,scheduled'],
        ];
    }

    public function attributes(): array
    {
        return [
            'sku' => 'mã sản phẩm',
            'title.vi' => 'tên sản phẩm tiếng Việt',
            'slug.vi' => 'đường dẫn tiếng Việt',
            'product_category_id' => 'danh mục',
            'featured_image' => 'ảnh sản phẩm',
        ];
    }
}
