<?php

namespace App\Livewire\Admin\News;

use App\Models\PostCategory;
use App\Support\PostRoutes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class CategoryForm extends Component
{
    public ?PostCategory $category = null;
    public ?int $parent_id = null;
    public string $code = '';
    public int $sort_order = 0;
    public bool $is_active = true;
    public array $name = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $slug = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $description = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $translation_status = ['vi' => 'published', 'en' => 'draft', 'zh' => 'draft'];

    public function mount(?PostCategory $category = null): void
    {
        Gate::authorize($category?->exists ? 'posts.update' : 'posts.create');
        $this->category = $category?->exists ? $category : null;
        if ($this->category) {
            foreach (['name', 'slug', 'description', 'seo_title', 'meta_description', 'translation_status'] as $field) {
                $this->{$field} = array_merge($this->{$field}, $this->category->getTranslations($field));
            }
            foreach (['parent_id', 'code', 'sort_order', 'is_active'] as $field) {
                $this->{$field} = $field === 'code' ? (string) ($this->category->{$field} ?? '') : $this->category->{$field};
            }
        }
    }

    public function generateSlug(string $locale): void
    {
        $this->slug[$locale] = Str::slug($this->name[$locale]);
    }

    public function save()
    {
        Gate::authorize($this->category ? 'posts.update' : 'posts.create');
        foreach (['vi', 'en', 'zh'] as $locale) {
            if ($this->name[$locale] !== '' && $this->slug[$locale] === '') {
                $this->generateSlug($locale);
            }
        }
        $data = $this->validate([
            'parent_id' => ['nullable', Rule::exists('post_categories', 'id')->whereNull('deleted_at'), Rule::notIn(array_filter([$this->category?->id]))],
            'code' => ['nullable', 'string', 'max:100', Rule::unique('post_categories', 'code')->ignore($this->category?->id)],
            'name.vi' => ['required', 'string', 'max:255'], 'name.en' => ['nullable', 'string', 'max:255'], 'name.zh' => ['nullable', 'string', 'max:255'],
            'slug.vi' => ['required', 'string', 'max:255'], 'slug.en' => ['nullable', 'string', 'max:255'], 'slug.zh' => ['nullable', 'string', 'max:255'],
            'description.*' => ['nullable', 'string', 'max:2000'], 'seo_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description.*' => ['nullable', 'string', 'max:500'], 'translation_status.*' => ['required', Rule::in(['draft', 'published'])],
            'sort_order' => ['required', 'integer', 'min:0'], 'is_active' => ['boolean'],
        ]);
        $data['created_by'] = $this->category?->created_by ?? auth()->id();
        $data['updated_by'] = auth()->id();
        $this->category = PostCategory::updateOrCreate(['id' => $this->category?->id], $data);
        PostRoutes::syncCategory($this->category);
        session()->flash('success', 'Đã lưu danh mục tin tức.');
        return $this->redirectRoute('admin.news.categories.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.news.category-form', [
            'parents' => PostCategory::whereKeyNot($this->category?->id)->orderBy('sort_order')->get(),
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'statuses' => ['draft' => 'Bản nháp', 'published' => 'Xuất bản'],
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Danh mục tin tức', 'route' => 'admin.news.categories.index'], ['label' => $this->category ? 'Cập nhật' : 'Thêm mới']],
        ]);
    }
}
