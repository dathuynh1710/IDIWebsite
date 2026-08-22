<?php

namespace App\Livewire\Admin\Investors;

use App\Livewire\AdminComponent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class Settings extends AdminComponent
{
    public array $page_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];

    public int $items_per_page = 15;

    public int $max_upload_size = 20;

    public function mount(): void
    {
        Gate::authorize('investors.update');
        $module = DB::table('modules')->where('code', 'investors')->first();
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
        Gate::authorize('investors.update');
        $data = $this->validate([
            'page_title.vi' => ['required', 'string', 'max:255'],
            'page_title.en' => ['nullable', 'string', 'max:255'],
            'page_title.zh' => ['nullable', 'string', 'max:255'],
            'description.*' => ['nullable', 'string', 'max:5000'],
            'seo_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description.*' => ['nullable', 'string', 'max:500'],
            'items_per_page' => ['required', 'integer', 'min:5', 'max:100'],
            'max_upload_size' => ['required', 'integer', 'min:1', 'max:100'],
        ], [], [
            'page_title.vi'       => 'Tiêu đề trang (Tiếng Việt)',
            'page_title.en'       => 'Tiêu đề trang (English)',
            'page_title.zh'       => 'Tiêu đề trang (中文)',
            'description.vi'      => 'Mô tả (Tiếng Việt)',
            'description.en'      => 'Mô tả (English)',
            'description.zh'      => 'Mô tả (中文)',
            'seo_title.vi'        => 'Tiêu đề SEO (Tiếng Việt)',
            'seo_title.en'        => 'Tiêu đề SEO (English)',
            'seo_title.zh'        => 'Tiêu đề SEO (中文)',
            'meta_description.vi' => 'Meta description (Tiếng Việt)',
            'meta_description.en' => 'Meta description (English)',
            'meta_description.zh' => 'Meta description (中文)',
            'items_per_page'      => 'Số tài liệu mỗi trang',
            'max_upload_size'     => 'Dung lượng tải lên tối đa (MB)',
        ]);

        DB::transaction(function () use ($data): void {
            DB::table('modules')->updateOrInsert(['code' => 'investors'], [
                'name' => 'Investor Relations',
                'module_type' => 'documents',
                'page_title' => json_encode($data['page_title'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode($data['description'], JSON_UNESCAPED_UNICODE),
                'seo_title' => json_encode($data['seo_title'], JSON_UNESCAPED_UNICODE),
                'meta_description' => json_encode($data['meta_description'], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
            $moduleId = (int) DB::table('modules')->where('code', 'investors')->value('id');
            foreach (['items_per_page', 'max_upload_size'] as $key) {
                DB::table('module_settings')->updateOrInsert(
                    ['module_id' => $moduleId, 'setting_key' => $key],
                    ['setting_value' => json_encode($data[$key]), 'setting_type' => 'number', 'updated_at' => now(), 'created_at' => now()]
                );
            }
        });

        $this->toast('Đã lưu cấu hình quan hệ cổ đông.');
    }

    public function render()
    {
        return view('livewire.admin.investors.settings', [
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Quan hệ cổ đông'], ['label' => 'Cấu hình']],
        ]);
    }
}
