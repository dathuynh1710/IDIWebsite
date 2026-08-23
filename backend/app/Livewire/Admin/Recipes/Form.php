<?php

namespace App\Livewire\Admin\Recipes;

use App\Livewire\AdminComponent;
use App\Models\Media;
use App\Models\Recipe;
use App\Support\RecipeRoutes;
use Illuminate\Support\Carbon;
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

    public ?Recipe $recipe = null;

    public string $code = '';

    public $featured_image;

    public $video_file;

    public bool $remove_image = false;

    public bool $remove_video = false;

    public int $sort_order = 0;

    public bool $is_featured = false;

    public bool $is_active = true;

    public string $published_at = '';

    public array $title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $slug = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $summary = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $content_left = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $content_right = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];

    public function mount(?Recipe $recipe = null): void
    {
        $recipe = $recipe?->exists ? $recipe : null;
        Gate::authorize($recipe ? 'recipes.update' : 'recipes.create');
        $this->recipe = $recipe?->load(['featuredMedia', 'videoMedia']);

        if (! $recipe) {
            $this->published_at = now()->format('Y-m-d\TH:i');

            return;
        }

        foreach (['code', 'sort_order', 'is_featured', 'is_active'] as $field) {
            $this->{$field} = $recipe->{$field} ?? $this->{$field};
        }
        foreach (['title', 'slug', 'summary', 'content_left', 'content_right', 'seo_title', 'meta_description'] as $field) {
            foreach (['vi', 'en', 'zh'] as $locale) {
                $this->{$field}[$locale] = $recipe->getTranslation($field, $locale, false) ?? $this->{$field}[$locale];
            }
        }
        $publishedAt = collect($recipe->getTranslations('locale_published_at'))->filter()->first();
        $this->published_at = $publishedAt
            ? Carbon::parse($publishedAt)->format('Y-m-d\TH:i')
            : $recipe->created_at->format('Y-m-d\TH:i');
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

    public function removeVideo(): void
    {
        $this->video_file = null;
        $this->remove_video = true;
    }

    public function save(): void
    {
        $this->slug = collect($this->slug)->map(fn ($value) => Str::slug((string) $value))->all();
        $recipeId = $this->recipe?->id;
        $validated = $this->validate([
            'code' => ['nullable', 'string', 'max:100', Rule::unique('recipes', 'code')->ignore($recipeId)],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,webm,mov', 'max:51200'],
            'remove_image' => ['boolean'], 'remove_video' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
            'is_featured' => ['required', 'boolean'], 'is_active' => ['required', 'boolean'],
            'published_at' => ['required', 'date'],
            'title.vi' => ['required', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'], 'title.zh' => ['nullable', 'string', 'max:255'],
            'slug.vi' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'slug.en' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'slug.zh' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'summary.*' => ['nullable', 'string', 'max:2000'],
            'content_left.*' => ['nullable', 'string', 'max:100000'],
            'content_right.*' => ['nullable', 'string', 'max:100000'],
            'seo_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description.*' => ['nullable', 'string', 'max:500'],
        ], [], [
            'code' => 'Mã recipe',
            'featured_image' => 'Ảnh đại diện',
            'video_file' => 'Video',
            'sort_order' => 'Thứ tự hiển thị',
            'published_at' => 'Ngày đăng',
            'title.vi' => 'Tiêu đề (Tiếng Việt)',
            'title.en' => 'Tiêu đề (English)',
            'title.zh' => 'Tiêu đề (中文)',
            'slug.vi' => 'Đường dẫn (Tiếng Việt)',
            'slug.en' => 'Đường dẫn (English)',
            'slug.zh' => 'Đường dẫn (中文)',
            'summary.vi' => 'Tóm tắt (Tiếng Việt)',
            'summary.en' => 'Tóm tắt (English)',
            'summary.zh' => 'Tóm tắt (中文)',
            'seo_title.vi' => 'Tiêu đề SEO (Tiếng Việt)',
            'seo_title.en' => 'Tiêu đề SEO (English)',
            'seo_title.zh' => 'Tiêu đề SEO (中文)',
            'meta_description.vi' => 'Meta description (Tiếng Việt)',
            'meta_description.en' => 'Meta description (English)',
            'meta_description.zh' => 'Meta description (中文)',
        ]);

        $localized = [];
        foreach (['title', 'slug', 'summary', 'seo_title', 'meta_description'] as $field) {
            $localized[$field] = $this->cleanTranslations($validated[$field] ?? []);
        }
        foreach (['content_left', 'content_right'] as $field) {
            $localized[$field] = collect($validated[$field] ?? [])->map(fn ($html) => $this->sanitizeHtml((string) $html))->filter()->all();
        }
        $publishedAt = Carbon::parse($validated['published_at'])->toIso8601String();
        $localized['translation_status'] = collect(['vi', 'en', 'zh'])->mapWithKeys(
            fn (string $locale): array => [$locale => filled($localized['title'][$locale] ?? null) ? 'published' : 'draft']
        )->all();
        $localized['locale_published_at'] = collect(['vi', 'en', 'zh'])
            ->filter(fn (string $locale): bool => filled($localized['title'][$locale] ?? null))
            ->mapWithKeys(fn (string $locale): array => [$locale => $publishedAt])
            ->all();

        DB::transaction(function () use ($validated, $localized): void {
            $imageId = $this->remove_image ? null : $this->recipe?->featured_media_id;
            $videoId = $this->remove_video ? null : $this->recipe?->video_media_id;
            if ($this->featured_image) {
                $imageId = $this->storeMedia($this->featured_image, 'recipes/images', $localized['title'], 'Ảnh công thức');
            }
            if ($this->video_file) {
                $videoId = $this->storeMedia($this->video_file, 'recipes/videos', $localized['title'], 'Video công thức');
            }

            $data = array_merge($localized, [
                'code' => filled($validated['code']) ? Str::upper(trim($validated['code'])) : null,
                'featured_media_id' => $imageId, 'video_media_id' => $videoId,
                'sort_order' => $validated['sort_order'],
                'is_featured' => $validated['is_featured'], 'is_active' => $validated['is_active'],
                'updated_by' => auth()->id(),
            ]);

            if ($this->recipe?->exists) {
                $this->recipe->update($data);
            } else {
                $data['created_by'] = auth()->id();
                $this->recipe = Recipe::create($data);
            }

            RecipeRoutes::sync($this->recipe);
        });

        $this->recipe->refresh()->load(['featuredMedia', 'videoMedia']);
        $this->featured_image = null;
        $this->video_file = null;
        $this->remove_image = false;
        $this->remove_video = false;
        if ($recipeId === null) {
            $this->js("history.replaceState({}, '', '".route('admin.recipes.edit', $this->recipe)."')");
        }
        $this->toast($recipeId ? 'Cập nhật công thức thành công.' : 'Tạo công thức thành công.');
    }

    private function cleanTranslations(array $values): array
    {
        return collect($values)->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value) => $value !== null && $value !== '')->all();
    }

    private function storeMedia($file, string $directory, array $title, string $fallback): int
    {
        $fileName = Str::uuid().'.'.$file->extension();
        $file->storeAs($directory, $fileName, 'public');
        $dimensions = str_starts_with((string) $file->getMimeType(), 'image/') ? (@getimagesize($file->getRealPath()) ?: [null, null]) : [null, null];

        return Media::create([
            'disk' => 'public', 'directory' => $directory, 'file_name' => $fileName,
            'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType(),
            'extension' => $file->extension(), 'file_size' => $file->getSize(),
            'width' => $dimensions[0], 'height' => $dimensions[1],
            'title' => $title, 'alt_text' => ['vi' => $title['vi'] ?? $fallback], 'created_by' => auth()->id(),
        ])->id;
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
        return view('livewire.admin.recipes.form', [
            'locales' => config('admin.locales'),
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Quản lý Recipes', 'route' => 'admin.recipes.index'],
                ['label' => $this->recipe?->exists ? 'Chỉnh sửa' : 'Thêm mới'],
            ],
        ])->title(($this->recipe?->exists ? 'Cập nhật Recipe' : 'Thêm Recipe').' - '.config('admin.name'));
    }
}
