<?php

namespace App\Livewire\Admin\Recruitment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Settings extends Component
{
    public array $page_title = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $description = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];
    public int $items_per_page = 10;
    public bool $application_enabled = true;
    public string $notification_email = '';

    public function mount(): void
    {
        Gate::authorize('recruitment.update');
        $module = DB::table('modules')->where('code', 'careers')->first();
        if (! $module) {
            return;
        }
        foreach (['page_title', 'description', 'seo_title', 'meta_description'] as $field) {
            $this->{$field} = array_merge($this->{$field}, json_decode($module->{$field} ?: '{}', true) ?: []);
        }
        foreach (DB::table('module_settings')->where('module_id', $module->id)->pluck('setting_value', 'setting_key') as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = json_decode($value, true);
            }
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
            'seo_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description.*' => ['nullable', 'string', 'max:500'],
            'items_per_page' => ['required', 'integer', 'min:1', 'max:100'],
            'application_enabled' => ['boolean'],
            'notification_email' => ['nullable', 'email', 'max:255'],
        ]);
        $moduleId = DB::table('modules')->where('code', 'careers')->value('id');
        if (! $moduleId) {
            $moduleId = DB::table('modules')->insertGetId([
                'code' => 'careers', 'name' => 'Careers', 'module_type' => 'recruitment',
                'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        DB::table('modules')->where('id', $moduleId)->update([
            'page_title' => json_encode($data['page_title'], JSON_UNESCAPED_UNICODE),
            'description' => json_encode($this->sanitizeLocalizedHtml($data['description']), JSON_UNESCAPED_UNICODE),
            'seo_title' => json_encode($data['seo_title'], JSON_UNESCAPED_UNICODE),
            'meta_description' => json_encode($data['meta_description'], JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
        foreach (['items_per_page', 'application_enabled', 'notification_email'] as $key) {
            DB::table('module_settings')->updateOrInsert(
                ['module_id' => $moduleId, 'setting_key' => $key],
                ['setting_value' => json_encode($data[$key]), 'setting_type' => is_bool($data[$key]) ? 'boolean' : (is_int($data[$key]) ? 'number' : 'string'), 'created_at' => now(), 'updated_at' => now()]
            );
        }
        $this->dispatch('admin-toast', message: 'Đã lưu cấu hình tuyển dụng.', type: 'success');
    }

    private function sanitizeLocalizedHtml(array $values): array
    {
        return collect($values)->map(function ($html) {
            $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', (string) $html) ?? '';
            return trim(strip_tags($html, '<p><br><h2><h3><strong><b><em><i><ul><ol><li><a>'));
        })->filter()->all();
    }

    public function render()
    {
        return view('livewire.admin.recruitment.settings', [
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Tuyển dụng'], ['label' => 'Cấu hình']],
        ]);
    }
}
