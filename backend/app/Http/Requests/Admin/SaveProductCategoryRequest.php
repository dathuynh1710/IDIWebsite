<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveProductCategoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => Str::upper(trim((string) $this->input('code'))),
            'name' => $this->cleanTranslations($this->input('name', [])),
            'slug' => collect($this->input('slug', []))
                ->map(fn ($value): string => Str::slug((string) $value))
                ->all(),
            'description' => $this->cleanTranslations($this->input('description', [])),
            'parent_id' => $this->filled('parent_id') ? (int) $this->input('parent_id') : null,
            'sort_order' => (int) $this->input('sort_order', 0),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $categoryId = $category instanceof ProductCategory ? $category->id : null;

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('product_categories', 'id')->whereNull('deleted_at'),
                Rule::notIn(array_filter([$categoryId])),
            ],
            'code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('product_categories', 'code')->ignore($categoryId),
            ],
            'name' => ['required', 'array'],
            'name.vi' => ['required', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'name.zh' => ['nullable', 'string', 'max:255'],
            'slug' => ['required', 'array'],
            'slug.vi' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'slug.en' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'slug.zh' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description.*' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'mã danh mục',
            'name.vi' => 'tên danh mục tiếng Việt',
            'slug.vi' => 'đường dẫn tiếng Việt',
            'parent_id' => 'danh mục cha',
            'sort_order' => 'thứ tự',
        ];
    }

    private function cleanTranslations(array $values): array
    {
        return collect($values)
            ->map(fn ($value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->all();
    }
}
