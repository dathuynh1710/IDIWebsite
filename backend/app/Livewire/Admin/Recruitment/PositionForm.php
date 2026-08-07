<?php

namespace App\Livewire\Admin\Recruitment;

use App\Livewire\AdminComponent;
use App\Models\JobPosition;
use App\Support\JobPositionRoutes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class PositionForm extends AdminComponent
{
    public ?JobPosition $position = null;

    public string $code = '';

    public string $department = '';

    public int $quantity = 1;

    public string $expires_at = '';

    public int $sort_order = 0;

    public bool $is_active = true;

    public array $enabled_locales = ['vi'];

    public array $title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $slug = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $location = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $summary = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $requirements = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $benefits = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $contact = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_keywords = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $translation_status = ['vi' => 'published', 'en' => 'draft', 'zh' => 'draft'];

    public array $locale_published_at = ['vi' => '', 'en' => '', 'zh' => ''];

    public function mount(?JobPosition $position = null): void
    {
        Gate::authorize($position?->exists ? 'recruitment.update' : 'recruitment.create');
        $this->position = $position?->exists ? $position : null;
        if (! $this->position) {
            return;
        }

        foreach (['title', 'slug', 'location', 'summary', 'description', 'requirements', 'benefits', 'contact', 'seo_title', 'meta_description', 'meta_keywords', 'translation_status', 'locale_published_at'] as $field) {
            $this->{$field} = array_merge($this->{$field}, $this->position->getTranslations($field));
        }
        $this->code = (string) ($this->position->code ?? '');
        $this->department = (string) ($this->position->department ?? '');
        $this->quantity = $this->position->quantity;
        $this->expires_at = $this->position->expires_at?->format('Y-m-d') ?? '';
        $this->sort_order = $this->position->sort_order;
        $this->is_active = $this->position->is_active;
        $this->enabled_locales = collect(['vi', 'en', 'zh'])
            ->filter(function (string $locale): bool {
                if ($locale === 'vi') {
                    return true;
                }

                $status = $this->position->getTranslation('translation_status', $locale, false);

                return in_array($status, ['published', 'scheduled'], true)
                    || (blank($status) && $this->hasLocalizedContent($locale));
            })
            ->values()
            ->all();
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
        abort_unless(in_array($locale, ['vi', 'en', 'zh'], true), 404);
        $this->slug[$locale] = Str::slug($this->title[$locale]);
    }

    public function save()
    {
        Gate::authorize($this->position ? 'recruitment.update' : 'recruitment.create');
        $this->updatedEnabledLocales();
        foreach ($this->enabled_locales as $locale) {
            if ($this->title[$locale] !== '' && $this->slug[$locale] === '') {
                $this->generateSlug($locale);
            }
        }

        $rules = [
            'code' => ['nullable', 'string', 'max:100', Rule::unique('job_positions', 'code')->ignore($this->position?->id)],
            'department' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'expires_at' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'enabled_locales' => ['required', 'array', 'min:1'],
            'enabled_locales.*' => ['required', Rule::in(['vi', 'en', 'zh'])],
            'title.*' => ['nullable', 'string', 'max:255'],
            'slug.*' => ['nullable', 'string', 'max:255'],
            'location.*' => ['nullable', 'string', 'max:255'],
            'summary.*' => ['nullable', 'string', 'max:2000'],
            'description.*' => ['nullable', 'string'],
            'requirements.*' => ['nullable', 'string'],
            'benefits.*' => ['nullable', 'string'],
            'contact.*' => ['nullable', 'string'],
            'seo_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description.*' => ['nullable', 'string', 'max:500'],
            'meta_keywords.*' => ['nullable', 'string', 'max:1000'],
            'locale_published_at.*' => ['nullable', 'date'],
        ];
        foreach ($this->enabled_locales as $locale) {
            $rules["title.{$locale}"] = ['required', 'string', 'max:255'];
            $rules["slug.{$locale}"] = ['required', 'string', 'max:255'];
        }

        $data = $this->validate($rules);
        $enabledLocales = collect($data['enabled_locales'])->flip();
        foreach (['title', 'slug', 'location', 'summary', 'seo_title', 'meta_description', 'meta_keywords'] as $field) {
            $submitted = collect($data[$field] ?? [])->intersectByKeys($enabledLocales)
                ->map(fn ($value) => trim((string) $value))
                ->filter(fn ($value) => $value !== '')
                ->all();
            $data[$field] = $this->mergeEnabledTranslations($field, $submitted);
        }
        foreach (['description', 'requirements', 'benefits', 'contact'] as $field) {
            $submitted = collect($data[$field] ?? [])->intersectByKeys($enabledLocales)
                ->map(fn ($html) => $this->sanitizeHtml((string) $html))
                ->filter()
                ->all();
            $data[$field] = $this->mergeEnabledTranslations($field, $submitted);
        }
        $submittedPublishedAt = collect($data['locale_published_at'] ?? [])->intersectByKeys($enabledLocales)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->all();
        $data['locale_published_at'] = $this->mergeEnabledTranslations('locale_published_at', $submittedPublishedAt);
        $data['translation_status'] = collect(['vi', 'en', 'zh'])->mapWithKeys(fn (string $locale) => [
            $locale => $enabledLocales->has($locale) ? 'published' : 'draft',
        ])->all();
        $data['expires_at'] = $data['expires_at'] ?: null;
        $data['created_by'] = $this->position?->created_by ?? auth()->id();
        $data['updated_by'] = auth()->id();
        unset($data['enabled_locales']);

        $this->position = JobPosition::updateOrCreate(['id' => $this->position?->id], $data);
        JobPositionRoutes::sync($this->position);
        $this->flashToast('Đã lưu vị trí tuyển dụng.');

        return $this->redirectRoute('admin.recruitment.positions.index', navigate: true);
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
        $translations = $this->position?->getTranslations($field) ?? [];

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
        foreach (['title', 'slug', 'location', 'summary', 'description', 'requirements', 'benefits', 'contact', 'seo_title', 'meta_description', 'meta_keywords'] as $field) {
            if (filled($this->position?->getTranslation($field, $locale, false))) {
                return true;
            }
        }

        return false;
    }

    public function render()
    {
        return view('livewire.admin.recruitment.position-form', [
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Tuyển dụng', 'route' => 'admin.recruitment.positions.index'], ['label' => $this->position ? 'Cập nhật' : 'Thêm mới']],
        ]);
    }
}
