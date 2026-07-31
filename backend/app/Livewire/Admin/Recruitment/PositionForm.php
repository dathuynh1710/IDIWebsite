<?php

namespace App\Livewire\Admin\Recruitment;

use App\Models\JobPosition;
use App\Support\JobPositionRoutes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class PositionForm extends Component
{
    public ?JobPosition $position = null;
    public string $code = '';
    public string $department = '';
    public int $quantity = 1;
    public string $expires_at = '';
    public int $sort_order = 0;
    public bool $is_active = true;
    public array $title = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $slug = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $location = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $summary = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $description = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $requirements = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $benefits = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $translation_status = ['vi' => 'published', 'en' => 'draft', 'zh' => 'draft'];
    public array $locale_published_at = ['vi' => '', 'en' => '', 'zh' => ''];

    public function mount(?JobPosition $position = null): void
    {
        Gate::authorize($position?->exists ? 'recruitment.update' : 'recruitment.create');
        $this->position = $position?->exists ? $position : null;
        if (! $this->position) {
            return;
        }
        foreach (['title', 'slug', 'location', 'summary', 'description', 'requirements', 'benefits', 'seo_title', 'meta_description', 'translation_status', 'locale_published_at'] as $field) {
            $this->{$field} = array_merge($this->{$field}, $this->position->getTranslations($field));
        }
        $this->code = (string) ($this->position->code ?? '');
        $this->department = (string) ($this->position->department ?? '');
        $this->quantity = $this->position->quantity;
        $this->expires_at = $this->position->expires_at?->format('Y-m-d') ?? '';
        $this->sort_order = $this->position->sort_order;
        $this->is_active = $this->position->is_active;
    }

    public function generateSlug(string $locale): void
    {
        abort_unless(in_array($locale, ['vi', 'en', 'zh'], true), 404);
        $this->slug[$locale] = Str::slug($this->title[$locale]);
    }

    public function save()
    {
        Gate::authorize($this->position ? 'recruitment.update' : 'recruitment.create');
        foreach (['vi', 'en', 'zh'] as $locale) {
            if ($this->title[$locale] !== '' && $this->slug[$locale] === '') {
                $this->generateSlug($locale);
            }
        }
        $data = $this->validate([
            'code' => ['nullable', 'string', 'max:100', Rule::unique('job_positions', 'code')->ignore($this->position?->id)],
            'department' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'expires_at' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'title.vi' => ['required', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'title.zh' => ['nullable', 'string', 'max:255'],
            'slug.vi' => ['required', 'string', 'max:255'],
            'slug.en' => ['nullable', 'string', 'max:255'],
            'slug.zh' => ['nullable', 'string', 'max:255'],
            'location.*' => ['nullable', 'string', 'max:255'],
            'summary.*' => ['nullable', 'string', 'max:2000'],
            'description.*' => ['nullable', 'string'],
            'requirements.*' => ['nullable', 'string'],
            'benefits.*' => ['nullable', 'string'],
            'seo_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description.*' => ['nullable', 'string', 'max:500'],
            'translation_status.*' => ['required', Rule::in(['draft', 'scheduled', 'published'])],
            'locale_published_at.*' => ['nullable', 'date'],
        ]);
        foreach (['title', 'slug', 'location', 'summary', 'seo_title', 'meta_description'] as $field) {
            $data[$field] = collect($data[$field] ?? [])->map(fn ($value) => trim((string) $value))->filter()->all();
        }
        foreach (['description', 'requirements', 'benefits'] as $field) {
            $data[$field] = collect($data[$field] ?? [])->map(fn ($html) => $this->sanitizeHtml((string) $html))->filter()->all();
        }
        $data['expires_at'] = $data['expires_at'] ?: null;
        $data['created_by'] = $this->position?->created_by ?? auth()->id();
        $data['updated_by'] = auth()->id();
        $this->position = JobPosition::updateOrCreate(['id' => $this->position?->id], $data);
        JobPositionRoutes::sync($this->position);
        session()->flash('success', 'Đã lưu vị trí tuyển dụng.');
        return $this->redirectRoute('admin.recruitment.positions.index', navigate: true);
    }

    private function sanitizeHtml(string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = preg_replace('/\son\w+\s*=\s*(["\']).*?\1/isu', '', $html) ?? '';
        return trim(strip_tags($html, '<p><br><h2><h3><h4><strong><b><em><i><u><ul><ol><li><a><blockquote><table><thead><tbody><tr><th><td>'));
    }

    public function render()
    {
        return view('livewire.admin.recruitment.position-form', [
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'statuses' => ['draft' => 'Bản nháp', 'scheduled' => 'Hẹn giờ', 'published' => 'Xuất bản'],
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Tuyển dụng', 'route' => 'admin.recruitment.positions.index'], ['label' => $this->position ? 'Cập nhật' : 'Thêm mới']],
        ]);
    }
}
