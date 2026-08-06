<?php

namespace App\Livewire\Admin\ProductCategories;

use App\Livewire\AdminComponent;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class Form extends AdminComponent
{
    public ?ProductCategory $category = null;

    public bool $modal = false;

    public ?int $parent_id = null;

    public string $code = '';

    public array $name = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $slug = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $description = ['vi' => '', 'en' => '', 'zh' => ''];

    public int $sort_order = 0;

    public bool $is_active = true;

    public function mount(?ProductCategory $category = null, bool $modal = false): void
    {
        $category = $category?->exists ? $category : null;
        $this->modal = $modal;
        Gate::authorize($category ? 'products.update' : 'products.create');
        $this->category = $category;

        if ($category) {
            $this->parent_id = $category->parent_id;
            $this->code = (string) $category->code;
            foreach (['vi', 'en', 'zh'] as $locale) {
                $this->name[$locale] = $category->getTranslation('name', $locale, false) ?? '';
                $this->slug[$locale] = $category->getTranslation('slug', $locale, false) ?? '';
                $this->description[$locale] = $category->getTranslation('description', $locale, false) ?? '';
            }
            $this->sort_order = (int) ($category->sort_order ?? 0);
            $this->is_active = (bool) ($category->is_active ?? true);
        }
    }

    public function generateSlug(string $locale): void
    {
        $this->slug[$locale] = Str::slug($this->name[$locale] ?? '');
    }

    public function save(): void
    {
        $this->code = Str::upper(trim($this->code));
        $this->slug = collect($this->slug)->map(fn ($value) => Str::slug((string) $value))->all();
        $categoryId = $this->category?->id;

        $validated = $this->validate([
            'parent_id' => ['nullable', 'integer', Rule::exists('product_categories', 'id')->whereNull('deleted_at'), Rule::notIn(array_filter([$categoryId]))],
            'code' => ['nullable', 'string', 'max:100', Rule::unique('product_categories', 'code')->ignore($categoryId)],
            'name.vi' => ['required', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'name.zh' => ['nullable', 'string', 'max:255'],
            'slug.vi' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'slug.en' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'slug.zh' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description.*' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['required', 'boolean'],
        ]);

        $data = array_merge($validated, ['updated_by' => auth()->id()]);
        if ($this->category?->exists) {
            $this->category->update($data);
            $message = 'Đã cập nhật danh mục sản phẩm.';
        } else {
            $data['created_by'] = auth()->id();
            $this->category = ProductCategory::create($data);
            $message = 'Đã thêm danh mục sản phẩm.';
            if (! $this->modal) {
                $this->js("history.replaceState({}, '', '".route('admin.product-categories.edit', $this->category)."')");
            }
        }

        $this->toast($message);
        $this->dispatch('category-saved');
    }

    public function render()
    {
        return view('livewire.admin.product-categories.form', [
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'parentOptions' => ProductCategory::query()
                ->when($this->category?->exists, fn ($query) => $query->whereKeyNot($this->category->id))
                ->orderBy('sort_order')->get()
                ->mapWithKeys(fn (ProductCategory $item) => [$item->id => $item->getTranslation('name', 'vi', false)])
                ->all(),
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Danh mục sản phẩm', 'route' => 'admin.product-categories.index'],
                ['label' => $this->category?->exists ? 'Chỉnh sửa' : 'Thêm mới'],
            ],
        ])->title(($this->category?->exists ? 'Sửa danh mục' : 'Thêm danh mục').' - '.config('admin.name'));
    }
}
