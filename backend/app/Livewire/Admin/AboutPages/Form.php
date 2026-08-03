<?php

namespace App\Livewire\Admin\AboutPages;

use App\Models\Media;
use App\Models\Page;
use App\Support\AboutPageRoutes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('layouts.admin')]
class Form extends Component
{
    use WithFileUploads;

    public ?Page $page = null;

    public ?int $parent_id = null;

    public string $template = 'about';

    public string $code = '';

    public $featured_image;

    public bool $remove_image = false;

    public int $sort_order = 0;

    public bool $is_active = true;

    public array $title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $slug = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $summary = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $content = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_keywords = ['vi' => '', 'en' => '', 'zh' => ''];

    public function mount(?Page $page = null): void
    {
        $page = $page?->exists ? $page : null;
        Gate::authorize($page ? 'pages.update' : 'pages.create');
        abort_if($page && ! Page::query()->about()->whereKey($page)->exists(), 404);
        $this->page = $page?->load('featuredMedia');

        if (! $page) {
            return;
        }

        foreach (['parent_id', 'template', 'code', 'sort_order', 'is_active'] as $field) {
            $this->{$field} = $page->{$field} ?? $this->{$field};
        }
        foreach (['title', 'slug', 'summary', 'content', 'seo_title', 'meta_description', 'meta_keywords'] as $field) {
            foreach (['vi', 'en', 'zh'] as $locale) {
                $this->{$field}[$locale] = $page->getTranslation($field, $locale, false) ?? $this->{$field}[$locale];
            }
        }
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
        $this->slug = collect($this->slug)->map(fn ($value) => Str::slug((string) $value))->all();
        $pageId = $this->page?->id;
        $validated = $this->validate([
            'parent_id' => ['nullable', 'integer', 'exists:pages,id', Rule::notIn(array_filter([$pageId]))],
            'template' => ['required', Rule::in(array_keys(Page::ABOUT_TEMPLATES))],
            'code' => ['nullable', 'string', 'max:100', Rule::unique('pages', 'code')->ignore($pageId)],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['required', 'boolean'],
            'title.vi' => ['required', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'title.zh' => ['nullable', 'string', 'max:255'],
            'slug.vi' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'slug.en' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'slug.zh' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'summary.*' => ['nullable', 'string', 'max:2000'],
            'content.*' => ['nullable', 'string', 'max:100000'],
            'seo_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description.*' => ['nullable', 'string', 'max:500'],
            'meta_keywords.*' => ['nullable', 'string', 'max:1000'],
        ], [], ['title.vi' => 'tiêu đề tiếng Việt', 'slug.vi' => 'đường dẫn tiếng Việt']);

        $localized = [];
        foreach (['title', 'slug', 'summary', 'seo_title', 'meta_description', 'meta_keywords'] as $field) {
            $localized[$field] = collect($validated[$field] ?? [])
                ->map(fn ($value) => is_string($value) ? trim($value) : $value)
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->all();
        }
        $localized['content'] = collect($validated['content'] ?? [])
            ->map(fn ($html) => $this->sanitizeHtml((string) $html))->filter()->all();

        DB::transaction(function () use ($validated, $localized): void {
            $mediaId = $this->remove_image ? null : $this->page?->featured_media_id;
            if ($this->featured_image) {
                $fileName = Str::uuid().'.'.$this->featured_image->extension();
                $this->featured_image->storeAs('about', $fileName, 'public');
                $mediaId = Media::create([
                    'disk' => 'public',
                    'directory' => 'about',
                    'file_name' => $fileName,
                    'original_name' => $this->featured_image->getClientOriginalName(),
                    'mime_type' => $this->featured_image->getMimeType(),
                    'extension' => $this->featured_image->extension(),
                    'file_size' => $this->featured_image->getSize(),
                    'title' => $localized['title'],
                    'alt_text' => $localized['title'],
                    'created_by' => auth()->id(),
                ])->id;
            }

            $data = array_merge($localized, [
                'parent_id' => $validated['parent_id'],
                'template' => $validated['template'],
                'code' => filled($validated['code']) ? Str::upper(trim($validated['code'])) : null,
                'featured_media_id' => $mediaId,
                'sort_order' => $validated['sort_order'],
                'is_active' => $validated['is_active'],
                'updated_by' => auth()->id(),
            ]);

            if ($this->page?->exists) {
                $this->page->update($data);
                $this->page->refresh()->load('featuredMedia');
            } else {
                $data['created_by'] = auth()->id();
                $this->page = Page::create($data)->load('featuredMedia');
            }

            AboutPageRoutes::sync($this->page);
        });

        $this->featured_image = null;
        $this->remove_image = false;
        if ($pageId === null) {
            $this->js("history.replaceState({}, '', '".route('admin.about-pages.edit', $this->page)."')");
        }
        $this->dispatch('admin-toast', message: $pageId ? 'Cập nhật nội dung giới thiệu thành công.' : 'Tạo nội dung giới thiệu thành công.', type: 'success');
    }

    private function sanitizeHtml(string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object|embed)[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = preg_replace('/\son\w+\s*=\s*(["\']).*?\1/is', '', $html) ?? '';
        $html = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:.*?\2/is', '$1="#"', $html) ?? '';

        return trim(strip_tags($html, '<p><br><h2><h3><h4><strong><b><em><i><u><ul><ol><li><a><blockquote><pre><code><table><thead><tbody><tr><th><td><img>'));
    }

    public function render()
    {
        return view('livewire.admin.about-pages.form', [
            'parents' => Page::query()->about()->when($this->page, fn ($query) => $query->whereKeyNot($this->page->id))->orderBy('sort_order')->get(),
            'templates' => Page::ABOUT_TEMPLATES,
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Quản lý giới thiệu', 'route' => 'admin.about-pages.index'],
                ['label' => $this->page?->exists ? 'Chỉnh sửa' : 'Thêm mới'],
            ],
        ])->title(($this->page?->exists ? 'Cập nhật giới thiệu' : 'Thêm giới thiệu').' - '.config('admin.name'));
    }
}
