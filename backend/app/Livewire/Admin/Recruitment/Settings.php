<?php

namespace App\Livewire\Admin\Recruitment;

use App\Livewire\AdminComponent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('layouts.admin')]
class Settings extends AdminComponent
{
    use WithFileUploads;

    public array $page_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $benefits_content = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $contact_content = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_keywords = ['vi' => '', 'en' => '', 'zh' => ''];

    public bool $application_enabled = true;

    public $hero_desktop;

    public $hero_mobile;

    public array $gallery_uploads = [];

    public ?string $existing_hero_desktop = null;

    public ?string $existing_hero_mobile = null;

    public array $existing_gallery_images = [];

    public function mount(): void
    {
        Gate::authorize('recruitment.update');
        $module = DB::table('modules')->where('code', 'careers')->first();
        if (! $module) {
            return;
        }
        foreach (['page_title', 'description', 'seo_title', 'meta_description'] as $field) {
            $this->{$field} = array_replace($this->{$field}, json_decode($module->{$field} ?: '{}', true) ?: []);
        }

        $settings = DB::table('module_settings')->where('module_id', $module->id)->pluck('setting_value', 'setting_key');
        foreach (['benefits_content', 'contact_content', 'meta_keywords'] as $key) {
            if ($settings->has($key)) {
                $this->{$key} = array_replace($this->{$key}, json_decode($settings[$key], true) ?: []);
            }
        }
        $this->application_enabled = $settings->has('application_enabled') ? (bool) json_decode($settings['application_enabled'], true) : true;
        $this->existing_hero_desktop = $settings->has('hero_desktop') ? json_decode($settings['hero_desktop'], true) : null;
        $this->existing_hero_mobile = $settings->has('hero_mobile') ? json_decode($settings['hero_mobile'], true) : null;
        $this->existing_gallery_images = $settings->has('gallery_images') ? (json_decode($settings['gallery_images'], true) ?: []) : [];
    }

    public function removeHero(string $type): void
    {
        abort_unless(in_array($type, ['desktop', 'mobile'], true), 404);
        if ($type === 'desktop') {
            $this->hero_desktop = null;
            $this->existing_hero_desktop = null;
        } else {
            $this->hero_mobile = null;
            $this->existing_hero_mobile = null;
        }
    }

    public function removeGalleryImage(int $index): void
    {
        if (array_key_exists($index, $this->existing_gallery_images)) {
            array_splice($this->existing_gallery_images, $index, 1);
        }
    }

    public function save(): void
    {
        Gate::authorize('recruitment.update');
        $data = $this->validate([
            'page_title.vi' => ['required', 'string', 'max:255'],
            'page_title.en' => ['nullable', 'string', 'max:255'],
            'page_title.zh' => ['nullable', 'string', 'max:255'],
            'description.*' => ['nullable', 'string'],
            'benefits_content.*' => ['nullable', 'string'],
            'contact_content.*' => ['nullable', 'string'],
            'seo_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description.*' => ['nullable', 'string', 'max:500'],
            'meta_keywords.*' => ['nullable', 'string', 'max:1000'],
            'application_enabled' => ['boolean'],
            'hero_desktop' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'hero_mobile' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'gallery_uploads' => ['array'],
            'gallery_uploads.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'existing_gallery_images' => ['array', 'max:3'],
            'existing_gallery_images.*' => ['string', 'max:1000'],
        ]);

        if (count($this->existing_gallery_images) + count($this->gallery_uploads) > 3) {
            $this->addError('gallery_uploads', 'Thư viện chỉ hiển thị tối đa 3 ảnh.');

            return;
        }

        foreach (['description', 'benefits_content', 'contact_content'] as $field) {
            $data[$field] = $this->sanitizeLocalizedHtml($data[$field] ?? []);
        }
        foreach (['page_title', 'seo_title', 'meta_description', 'meta_keywords'] as $field) {
            $data[$field] = $this->cleanTranslations($data[$field] ?? []);
        }

        $heroDesktopPath = $this->existing_hero_desktop;
        $heroMobilePath = $this->existing_hero_mobile;
        if ($this->hero_desktop) {
            $heroDesktopPath = $this->hero_desktop->store('recruitment/settings', 'public');
        }
        if ($this->hero_mobile) {
            $heroMobilePath = $this->hero_mobile->store('recruitment/settings', 'public');
        }
        $galleryPaths = $this->existing_gallery_images;
        foreach ($this->gallery_uploads as $image) {
            $galleryPaths[] = $image->store('recruitment/gallery', 'public');
        }

        DB::transaction(function () use ($data, $heroDesktopPath, $heroMobilePath, $galleryPaths): void {
            DB::table('modules')->updateOrInsert(['code' => 'careers'], [
                'name' => 'Careers',
                'module_type' => 'recruitment',
                'page_title' => json_encode($data['page_title'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'description' => json_encode($data['description'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'seo_title' => json_encode($data['seo_title'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'meta_description' => json_encode($data['meta_description'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $moduleId = (int) DB::table('modules')->where('code', 'careers')->value('id');
            foreach ([
                'benefits_content' => [$data['benefits_content'], 'json'],
                'contact_content' => [$data['contact_content'], 'json'],
                'meta_keywords' => [$data['meta_keywords'], 'json'],
                'application_enabled' => [$data['application_enabled'], 'boolean'],
                'hero_desktop' => [$heroDesktopPath, 'image'],
                'hero_mobile' => [$heroMobilePath, 'image'],
                'gallery_images' => [$galleryPaths, 'json'],
            ] as $key => [$value, $type]) {
                DB::table('module_settings')->updateOrInsert([
                    'module_id' => $moduleId,
                    'setting_key' => $key,
                ], [
                    'setting_value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                    'setting_type' => $type,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $this->existing_hero_desktop = $heroDesktopPath;
        $this->existing_hero_mobile = $heroMobilePath;
        $this->existing_gallery_images = $galleryPaths;
        $this->reset('hero_desktop', 'hero_mobile', 'gallery_uploads');
        $this->toast('Đã lưu cấu hình tuyển dụng.');
    }

    private function sanitizeLocalizedHtml(array $values): array
    {
        return collect($values)->map(function ($html): string {
            $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', (string) $html) ?? '';
            $html = preg_replace('/\son\w+\s*=\s*(["\']).*?\1/isu', '', $html) ?? '';
            $html = preg_replace('/\s(href|src)\s*=\s*(["\'])\s*javascript:.*?\2/isu', '', $html) ?? '';

            return trim(strip_tags($html, '<p><br><h1><h2><h3><h4><strong><b><em><i><u><ul><ol><li><a><blockquote><pre><code><table><thead><tbody><tr><th><td><img>'));
        })->filter()->all();
    }

    private function cleanTranslations(array $values): array
    {
        return collect($values)->map(fn ($value) => trim((string) $value))->filter()->all();
    }

    public function render()
    {
        return view('livewire.admin.recruitment.settings', [
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'heroDesktopUrl' => $this->existing_hero_desktop ? Storage::disk('public')->url($this->existing_hero_desktop) : null,
            'heroMobileUrl' => $this->existing_hero_mobile ? Storage::disk('public')->url($this->existing_hero_mobile) : null,
            'galleryImageUrls' => collect($this->existing_gallery_images)->map(fn (string $path) => Storage::disk('public')->url($path))->all(),
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Tuyển dụng'], ['label' => 'Cấu hình']],
        ]);
    }
}
