<?php

namespace App\Livewire\Admin\News;

use App\Models\Media;
use App\Models\Post;
use App\Models\PostCategory;
use App\Support\PostRoutes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('layouts.admin')]
class PostForm extends Component
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
    public array $title = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $slug = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $excerpt = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $content = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $og_title = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $og_description = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $translation_status = ['vi' => 'published', 'en' => 'draft', 'zh' => 'draft'];
    public array $locale_published_at = ['vi' => '', 'en' => '', 'zh' => ''];

    public function mount(?Post $post = null): void
    {
        Gate::authorize($post?->exists ? 'posts.update' : 'posts.create');
        $this->post = $post?->exists ? $post : null;
        if ($this->post) {
            foreach (['title', 'slug', 'excerpt', 'content', 'seo_title', 'meta_description', 'og_title', 'og_description', 'translation_status', 'locale_published_at'] as $field) {
                $this->{$field} = array_merge($this->{$field}, $this->post->getTranslations($field));
            }
            foreach (['post_category_id', 'code', 'sort_order', 'is_featured', 'is_active'] as $field) {
                $this->{$field} = $field === 'code' ? (string) ($this->post->{$field} ?? '') : $this->post->{$field};
            }
        }
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
        foreach (['vi', 'en', 'zh'] as $locale) {
            if ($this->title[$locale] !== '' && $this->slug[$locale] === '') {
                $this->generateSlug($locale);
            }
        }
        $data = $this->validate([
            'post_category_id' => ['required', Rule::exists('post_categories', 'id')->whereNull('deleted_at')],
            'code' => ['nullable', 'string', 'max:100', Rule::unique('posts', 'code')->ignore($this->post?->id)],
            'featured_image' => [$this->post?->featured_media_id && ! $this->remove_image ? 'nullable' : 'required', 'image', 'max:8192'],
            'title.vi' => ['required', 'string', 'max:255'], 'title.en' => ['nullable', 'string', 'max:255'], 'title.zh' => ['nullable', 'string', 'max:255'],
            'slug.vi' => ['required', 'string', 'max:255'], 'slug.en' => ['nullable', 'string', 'max:255'], 'slug.zh' => ['nullable', 'string', 'max:255'],
            'excerpt.*' => ['nullable', 'string', 'max:2000'], 'content.*' => ['nullable', 'string'],
            'seo_title.*' => ['nullable', 'string', 'max:255'], 'meta_description.*' => ['nullable', 'string', 'max:500'],
            'og_title.*' => ['nullable', 'string', 'max:255'], 'og_description.*' => ['nullable', 'string', 'max:500'],
            'translation_status.*' => ['required', Rule::in(['draft', 'scheduled', 'published'])],
            'locale_published_at.*' => ['nullable', 'date'], 'sort_order' => ['required', 'integer', 'min:0'],
            'is_featured' => ['boolean'], 'is_active' => ['boolean'],
        ]);
        foreach (['title', 'slug', 'excerpt', 'seo_title', 'meta_description', 'og_title', 'og_description'] as $field) {
            $data[$field] = collect($data[$field] ?? [])->map(fn ($value) => trim((string) $value))->filter(fn ($value) => $value !== '')->all();
        }
        $data['content'] = collect($data['content'] ?? [])->map(fn ($html) => $this->sanitizeHtml((string) $html))->filter()->all();
        unset($data['featured_image']);
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
        session()->flash('success', 'Đã lưu bài viết.');
        return $this->redirectRoute('admin.news.posts.index', navigate: true);
    }

    private function sanitizeHtml(string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = preg_replace('/\son\w+\s*=\s*(["\']).*?\1/isu', '', $html) ?? '';
        $html = preg_replace('/\s(href|src)\s*=\s*(["\'])\s*javascript:.*?\2/isu', '', $html) ?? '';

        return trim(strip_tags($html, '<p><br><h2><h3><h4><strong><b><em><i><u><ul><ol><li><a><blockquote><pre><code><table><thead><tbody><tr><th><td><img>'));
    }

    public function render()
    {
        return view('livewire.admin.news.post-form', [
            'categories' => PostCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'statuses' => ['draft' => 'Bản nháp', 'scheduled' => 'Hẹn giờ', 'published' => 'Xuất bản'],
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Tin tức', 'route' => 'admin.news.posts.index'], ['label' => $this->post ? 'Cập nhật' : 'Thêm mới']],
        ]);
    }
}
