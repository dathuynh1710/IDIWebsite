<?php

namespace App\Livewire\Admin\Recipes;

use App\Models\Media;
use App\Models\Recipe;
use App\Support\RecipeRoutes;
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

    public ?Recipe $recipe = null;

    public string $code = '';

    public $featured_image;

    public $video_file;

    public bool $remove_image = false;

    public bool $remove_video = false;

    public string $servings = '';

    public ?int $preparation_time = null;

    public ?int $cooking_time = null;

    public string $difficulty = 'easy';

    public int $sort_order = 0;

    public bool $is_featured = false;

    public bool $is_active = true;

    public bool $show_ingredients = true;

    public bool $show_steps = true;

    public array $title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $slug = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $summary = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $content = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $translation_status = ['vi' => 'draft', 'en' => 'draft', 'zh' => 'draft'];

    public array $locale_published_at = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $ingredients = [];

    public array $steps = [];

    public function mount(?Recipe $recipe = null): void
    {
        $recipe = $recipe?->exists ? $recipe : null;
        Gate::authorize($recipe ? 'recipes.update' : 'recipes.create');
        $this->recipe = $recipe?->load(['featuredMedia', 'videoMedia', 'ingredients', 'steps']);

        if (! $recipe) {
            $this->addIngredient();
            $this->addStep();

            return;
        }

        foreach (['code', 'servings', 'preparation_time', 'cooking_time', 'difficulty', 'sort_order', 'is_featured', 'is_active', 'show_ingredients', 'show_steps'] as $field) {
            $this->{$field} = $recipe->{$field} ?? $this->{$field};
        }
        foreach (['title', 'slug', 'summary', 'content', 'seo_title', 'meta_description', 'translation_status', 'locale_published_at'] as $field) {
            foreach (['vi', 'en', 'zh'] as $locale) {
                $this->{$field}[$locale] = $recipe->getTranslation($field, $locale, false) ?? $this->{$field}[$locale];
            }
        }
        $this->ingredients = $recipe->ingredients->map(fn ($item) => [
            'quantity' => $item->quantity ?? '',
            'name' => $this->translationsFor($item, 'name'),
            'unit' => $this->translationsFor($item, 'unit'),
            'note' => $this->translationsFor($item, 'note'),
        ])->all();
        $this->steps = $recipe->steps->map(fn ($item) => [
            'instruction' => $this->translationsFor($item, 'instruction'),
        ])->all();
        if ($this->ingredients === []) {
            $this->addIngredient();
        }
        if ($this->steps === []) {
            $this->addStep();
        }
    }

    public function addIngredient(): void
    {
        $this->ingredients[] = [
            'quantity' => '',
            'name' => ['vi' => '', 'en' => '', 'zh' => ''],
            'unit' => ['vi' => '', 'en' => '', 'zh' => ''],
            'note' => ['vi' => '', 'en' => '', 'zh' => ''],
        ];
    }

    public function removeIngredient(int $index): void
    {
        unset($this->ingredients[$index]);
        $this->ingredients = array_values($this->ingredients);
    }

    public function addStep(): void
    {
        $this->steps[] = ['instruction' => ['vi' => '', 'en' => '', 'zh' => '']];
    }

    public function removeStep(int $index): void
    {
        unset($this->steps[$index]);
        $this->steps = array_values($this->steps);
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
        $statuses = 'draft,translating,review,scheduled,published,hidden,archived';
        $validated = $this->validate([
            'code' => ['nullable', 'string', 'max:100', Rule::unique('recipes', 'code')->ignore($recipeId)],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,webm,mov', 'max:51200'],
            'remove_image' => ['boolean'], 'remove_video' => ['boolean'],
            'servings' => ['nullable', 'string', 'max:100'],
            'preparation_time' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'cooking_time' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999999'],
            'is_featured' => ['required', 'boolean'], 'is_active' => ['required', 'boolean'],
            'show_ingredients' => ['required', 'boolean'], 'show_steps' => ['required', 'boolean'],
            'title.vi' => ['required', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'], 'title.zh' => ['nullable', 'string', 'max:255'],
            'slug.vi' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'slug.en' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'slug.zh' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'summary.*' => ['nullable', 'string', 'max:2000'],
            'content.*' => ['nullable', 'string', 'max:100000'],
            'seo_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description.*' => ['nullable', 'string', 'max:500'],
            'translation_status.*' => ['required', "in:{$statuses}"],
            'locale_published_at.*' => ['nullable', 'date'],
            'locale_published_at.vi' => ['required_if:translation_status.vi,scheduled'],
            'locale_published_at.en' => ['required_if:translation_status.en,scheduled'],
            'locale_published_at.zh' => ['required_if:translation_status.zh,scheduled'],
            'ingredients' => ['array', 'max:100'],
            'ingredients.*.quantity' => ['nullable', 'string', 'max:100'],
            'ingredients.*.name.*' => ['nullable', 'string', 'max:255'],
            'ingredients.*.unit.*' => ['nullable', 'string', 'max:100'],
            'ingredients.*.note.*' => ['nullable', 'string', 'max:500'],
            'steps' => ['array', 'max:100'],
            'steps.*.instruction.*' => ['nullable', 'string', 'max:5000'],
        ]);

        $localized = [];
        foreach (['title', 'slug', 'summary', 'seo_title', 'meta_description', 'translation_status', 'locale_published_at'] as $field) {
            $localized[$field] = $this->cleanTranslations($validated[$field] ?? []);
        }
        $localized['content'] = collect($validated['content'] ?? [])->map(fn ($html) => $this->sanitizeHtml((string) $html))->filter()->all();

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
                'servings' => filled($validated['servings']) ? trim($validated['servings']) : null,
                'preparation_time' => $validated['preparation_time'], 'cooking_time' => $validated['cooking_time'],
                'difficulty' => $validated['difficulty'], 'sort_order' => $validated['sort_order'],
                'is_featured' => $validated['is_featured'], 'is_active' => $validated['is_active'],
                'show_ingredients' => $validated['show_ingredients'], 'show_steps' => $validated['show_steps'],
                'updated_by' => auth()->id(),
            ]);

            if ($this->recipe?->exists) {
                $this->recipe->update($data);
            } else {
                $data['created_by'] = auth()->id();
                $this->recipe = Recipe::create($data);
            }

            $this->recipe->ingredients()->delete();
            foreach ($validated['ingredients'] ?? [] as $sortOrder => $item) {
                $name = $this->cleanTranslations($item['name'] ?? []);
                if ($name === [] && blank($item['quantity'] ?? null)) {
                    continue;
                }
                $this->recipe->ingredients()->create([
                    'quantity' => filled($item['quantity'] ?? null) ? trim($item['quantity']) : null,
                    'name' => $name, 'unit' => $this->cleanTranslations($item['unit'] ?? []),
                    'note' => $this->cleanTranslations($item['note'] ?? []), 'sort_order' => $sortOrder,
                ]);
            }
            $this->recipe->steps()->delete();
            foreach ($validated['steps'] ?? [] as $sortOrder => $item) {
                $instruction = $this->cleanTranslations($item['instruction'] ?? []);
                if ($instruction === []) {
                    continue;
                }
                $this->recipe->steps()->create(['instruction' => $instruction, 'sort_order' => $sortOrder]);
            }
            RecipeRoutes::sync($this->recipe);
        });

        $this->recipe->refresh()->load(['featuredMedia', 'videoMedia', 'ingredients', 'steps']);
        $this->featured_image = null;
        $this->video_file = null;
        $this->remove_image = false;
        $this->remove_video = false;
        if ($recipeId === null) {
            $this->js("history.replaceState({}, '', '".route('admin.recipes.edit', $this->recipe)."')");
        }
        $this->dispatch('admin-toast', message: $recipeId ? 'Cập nhật công thức thành công.' : 'Tạo công thức thành công.', type: 'success');
    }

    private function translationsFor($model, string $field): array
    {
        return array_replace(['vi' => '', 'en' => '', 'zh' => ''], $model->getTranslations($field));
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
            'difficulties' => ['easy' => 'Dễ', 'medium' => 'Trung bình', 'hard' => 'Khó'],
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Quản lý Recipes', 'route' => 'admin.recipes.index'],
                ['label' => $this->recipe?->exists ? 'Chỉnh sửa' : 'Thêm mới'],
            ],
        ])->title(($this->recipe?->exists ? 'Cập nhật Recipe' : 'Thêm Recipe').' - '.config('admin.name'));
    }
}
