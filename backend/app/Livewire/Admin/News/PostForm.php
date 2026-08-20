<?php

namespace App\Livewire\Admin\News;

use App\Livewire\AdminComponent;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostCategory;
use App\Support\PostRoutes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('layouts.admin')]
class PostForm extends AdminComponent
{
    use WithFileUploads;

    public ?Post $post = null;

    public ?int $post_category_id = null;

    public string $code = '';

    public $featured_image;

    public bool $remove_image = false;

    public int $sort_order = 0;

    public bool $is_featured = false;

    public bool $is_active = true;

    public array $enabled_locales = ['vi'];

    public array $title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $slug = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $excerpt = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $content = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $og_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $og_description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $locale_published_at = ['vi' => '', 'en' => '', 'zh' => ''];

    public function mount(?Post $post = null): void
    {
        Gate::authorize($post?->exists ? 'posts.update' : 'posts.create');
        $this->post = $post?->exists ? $post : null;
        if ($this->post) {
            foreach (['title', 'slug', 'excerpt', 'content', 'seo_title', 'meta_description', 'og_title', 'og_description', 'locale_published_at'] as $field) {
                $this->{$field} = array_merge($this->{$field}, $this->post->getTranslations($field));
            }
            foreach (['post_category_id', 'code', 'sort_order', 'is_featured', 'is_active'] as $field) {
                $this->{$field} = $field === 'code'
                    ? (string) ($this->post->{$field} ?? '')
                    : ($this->post->{$field} ?? $this->{$field});
            }
            $this->enabled_locales = collect(['vi', 'en', 'zh'])
                ->filter(function (string $locale): bool {
                    if ($locale === 'vi') {
                        return true;
                    }

                    $status = $this->post->getTranslation('translation_status', $locale, false);

                    return $status === 'published' || (blank($status) && $this->hasLocalizedContent($locale));
                })
                ->values()
                ->all();
        }
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
        $this->slug[$locale] = Str::slug($this->title[$locale]);
    }

    public function removeFeaturedImage(): void
    {
        $this->featured_image = null;
        $this->remove_image = true;
    }

    public function save()
    {
        Gate::authorize($this->post ? 'posts.update' : 'posts.create');
        $this->updatedEnabledLocales();
        foreach ($this->enabled_locales as $locale) {
            if ($this->title[$locale] !== '' && $this->slug[$locale] === '') {
                $this->generateSlug($locale);
            }
        }
        $rules = [
            'post_category_id' => ['required', Rule::exists('post_categories', 'id')->whereNull('deleted_at')],
            'code' => ['nullable', 'string', 'max:100', Rule::unique('posts', 'code')->ignore($this->post?->id)],
            'featured_image' => [$this->post ? 'nullable' : 'required', 'image', 'max:8192'],
            'enabled_locales' => ['required', 'array', 'min:1'],
            'enabled_locales.*' => ['required', Rule::in(['vi', 'en', 'zh'])],
            'excerpt.*' => ['nullable', 'string', 'max:2000'], 'content.*' => ['nullable', 'string'],
            'seo_title.*' => ['nullable', 'string', 'max:255'], 'meta_description.*' => ['nullable', 'string', 'max:500'],
            'og_title.*' => ['nullable', 'string', 'max:255'], 'og_description.*' => ['nullable', 'string', 'max:500'],
            'locale_published_at.*' => ['nullable', 'date'], 'sort_order' => ['required', 'integer', 'min:0'],
            'is_featured' => ['boolean'], 'is_active' => ['boolean'],
        ];
        foreach ($this->enabled_locales as $locale) {
            $rules["title.{$locale}"] = ['required', 'string', 'max:255'];
            $rules["slug.{$locale}"] = ['required', 'string', 'max:255'];
        }

        $localeLabels = ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'];
        $attributes = [
            'post_category_id'  => 'Chuyên mục',
            'code'              => 'Mã bài viết',
            'featured_image'    => 'Ảnh đại diện',
            'sort_order'        => 'Thứ tự hiển thị',
            'is_featured'       => 'Nổi bật',
            'is_active'         => 'Trạng thái',
        ];
        foreach ($this->enabled_locales as $locale) {
            $label = $localeLabels[$locale] ?? $locale;
            $attributes["title.{$locale}"]              = "Tiêu đề ({$label})";
            $attributes["slug.{$locale}"]               = "Đường dẫn ({$label})";
            $attributes["excerpt.{$locale}"]            = "Tóm tắt ({$label})";
            $attributes["seo_title.{$locale}"]          = "Tiêu đề SEO ({$label})";
            $attributes["meta_description.{$locale}"]   = "Meta description ({$label})";
            $attributes["og_title.{$locale}"]           = "OG Title ({$label})";
            $attributes["og_description.{$locale}"]     = "OG Description ({$label})";
            $attributes["locale_published_at.{$locale}"] = "Ngày đăng ({$label})";
        }

        $data = $this->validate($rules, [], $attributes);
        $enabledLocales = collect($data['enabled_locales'])->flip();
        foreach (['title', 'slug', 'excerpt', 'seo_title', 'meta_description', 'og_title', 'og_description'] as $field) {
            $submitted = collect($data[$field] ?? [])->intersectByKeys($enabledLocales)
                ->map(fn ($value) => trim((string) $value))->filter(fn ($value) => $value !== '')->all();
            $data[$field] = $this->mergeEnabledTranslations($field, $submitted);
        }
        $submittedPublishedAt = collect($data['locale_published_at'] ?? [])->intersectByKeys($enabledLocales)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->all();
        $data['locale_published_at'] = $this->mergeEnabledTranslations('locale_published_at', $submittedPublishedAt);
        $data['translation_status'] = collect(['vi', 'en', 'zh'])->mapWithKeys(fn ($locale) => [
            $locale => $enabledLocales->has($locale) ? 'published' : 'draft',
        ])->all();
        $submittedContent = collect($data['content'] ?? [])->intersectByKeys($enabledLocales)
            ->map(fn ($html) => $this->sanitizeHtml((string) $html))->filter()->all();
        $data['content'] = $this->mergeEnabledTranslations('content', $submittedContent);
        unset($data['featured_image'], $data['enabled_locales']);
        if ($this->featured_image) {
            $path = $this->featured_image->store('news', 'public');
            $data['featured_media_id'] = Media::create([
                'disk' => 'public', 'directory' => dirname($path), 'file_name' => basename($path),
                'original_name' => $this->featured_image->getClientOriginalName(),
                'mime_type' => $this->featured_image->getMimeType(), 'extension' => $this->featured_image->getClientOriginalExtension(),
                'file_size' => $this->featured_image->getSize(), 'title' => $this->title, 'alt_text' => $this->title, 'created_by' => auth()->id(),
            ])->id;
        } elseif ($this->remove_image) {
            $data['featured_media_id'] = null;
        }
        $data['author_id'] = $this->post?->author_id ?? auth()->id();
        $data['created_by'] = $this->post?->created_by ?? auth()->id();
        $data['updated_by'] = auth()->id();
        $this->post = Post::updateOrCreate(['id' => $this->post?->id], $data);
        PostRoutes::syncPost($this->post);
        $this->flashToast('Đã lưu bài viết.');

        return $this->redirectRoute('admin.news.posts.index', navigate: true);
    }

    private function sanitizeHtml(string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = preg_replace('/\son\w+\s*=\s*(["\']).*?\1/isu', '', $html) ?? '';
        $html = preg_replace('/\s(href|src)\s*=\s*(["\'])\s*javascript:.*?\2/isu', '', $html) ?? '';

        return trim(strip_tags($html, '<p><br><h2><h3><h4><strong><b><em><i><u><ul><ol><li><a><blockquote><pre><code><table><thead><tbody><tr><th><td><img>'));
    }

    private function mergeEnabledTranslations(string $field, array $submitted): array
    {
        $translations = $this->post?->getTranslations($field) ?? [];

        foreach ($this->enabled_locales as $locale) {
            if (array_key_exists($locale, $submitted)) {
                $translations[$locale] = $submitted[$locale];
            } else {
                unset($translations[$locale]);
            }
        }

        return $translations;
    }

    private function hasLocalizedContent(string $locale): bool
    {
        foreach (['title', 'slug', 'excerpt', 'content', 'seo_title', 'meta_description', 'og_title', 'og_description'] as $field) {
            if (filled($this->post?->getTranslation($field, $locale, false))) {
                return true;
            }
        }

        return false;
    }

    public function render()
    {
        return view('livewire.admin.news.post-form', [
            'categories' => PostCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Tin tức', 'route' => 'admin.news.posts.index'], ['label' => $this->post ? 'Cập nhật' : 'Thêm mới']],
        ]);
    }
}
