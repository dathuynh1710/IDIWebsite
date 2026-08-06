<?php

namespace App\Livewire\Admin\Investors;

use App\Livewire\AdminComponent;
use App\Models\DocumentCategory;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class CategoryForm extends AdminComponent
{
    public ?DocumentCategory $category = null;

    public ?int $parent_id = null;

    public int $sort_order = 0;

    public bool $is_active = true;

    public array $name = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $slug = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $description = ['vi' => '', 'en' => '', 'zh' => ''];

    public function mount(?DocumentCategory $category = null): void
    {
        Gate::authorize($category?->exists ? 'investors.update' : 'investors.create');
        $this->category = $category?->exists ? $category : null;
        if ($this->category) {
            foreach (['name', 'slug', 'description'] as $field) {
                $this->{$field} = array_merge($this->{$field}, $this->category->getTranslations($field));
            }
            foreach (['parent_id', 'sort_order', 'is_active'] as $field) {
                $this->{$field} = $this->category->{$field};
            }
        }
    }

    public function generateSlug(string $locale): void
    {
        $this->slug[$locale] = Str::slug($this->name[$locale]);
    }

    public function save()
    {
        Gate::authorize($this->category ? 'investors.update' : 'investors.create');
        foreach (['vi', 'en', 'zh'] as $locale) {
            if ($this->name[$locale] !== '' && $this->slug[$locale] === '') {
                $this->generateSlug($locale);
            }
        }
        $data = $this->validate([
            'parent_id' => ['nullable', Rule::exists('document_categories', 'id')->whereNull('deleted_at'), Rule::notIn(array_filter([$this->category?->id]))],
            'name.vi' => ['required', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'name.zh' => ['nullable', 'string', 'max:255'],
            'slug.vi' => ['required', 'string', 'max:255'],
            'slug.en' => ['nullable', 'string', 'max:255'],
            'slug.zh' => ['nullable', 'string', 'max:255'],
            'description.*' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
        foreach (['name', 'slug', 'description'] as $field) {
            $data[$field] = collect($data[$field] ?? [])->map(fn ($value) => trim((string) $value))->filter(fn ($value) => $value !== '')->all();
        }
        $data['created_by'] = $this->category?->created_by ?? auth()->id();
        $data['updated_by'] = auth()->id();
        $this->category = DocumentCategory::updateOrCreate(['id' => $this->category?->id], $data);
        $this->flashToast('Đã lưu danh mục quan hệ cổ đông.');

        return $this->redirectRoute('admin.investors.categories.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.investors.category-form', [
            'parents' => DocumentCategory::whereKeyNot($this->category?->id)->orderBy('sort_order')->get(),
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Danh mục cổ đông', 'route' => 'admin.investors.categories.index'], ['label' => $this->category ? 'Cập nhật' : 'Thêm mới']],
        ]);
    }
}
