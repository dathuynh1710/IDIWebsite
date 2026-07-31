<?php

namespace App\Livewire\Admin\News;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Settings extends Component
{
    public array $page_title = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];
    public int $items_per_page = 12;
    public int $category_items_per_page = 10;
    public int $featured_limit = 3;
    public int $related_limit = 6;
    public int $thumbnail_size = 320;
    public int $max_upload_width = 1600;
    public bool $show_placeholder_image = true;
    public bool $allow_print = true;
    public bool $allow_comments = false;
    public bool $fetch_remote_images = false;

    public function mount(): void
    {
        Gate::authorize('posts.update');
        $module = DB::table('modules')->where('code', 'news')->first();
        if (! $module) {
            return;
        }
        foreach (['page_title', 'seo_title', 'meta_description'] as $field) {
            $this->{$field} = array_merge(['vi' => '', 'en' => '', 'zh' => ''], json_decode($module->{$field} ?: '{}', true) ?: []);
        }
        $settings = DB::table('module_settings')->where('module_id', $module->id)->pluck('setting_value', 'setting_key');
        foreach ($settings as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = json_decode($value, true);
            }
        }
    }

    public function save(): void
    {
        Gate::authorize('posts.update');
        $data = $this->validate([
            'page_title.vi' => ['required', 'string', 'max:255'], 'page_title.en' => ['nullable', 'string', 'max:255'], 'page_title.zh' => ['nullable', 'string', 'max:255'],
            'seo_title.*' => ['nullable', 'string', 'max:255'], 'meta_description.*' => ['nullable', 'string', 'max:500'],
            'items_per_page' => ['required', 'integer', 'min:1', 'max:100'], 'category_items_per_page' => ['required', 'integer', 'min:1', 'max:100'],
            'featured_limit' => ['required', 'integer', 'min:1', 'max:20'], 'related_limit' => ['required', 'integer', 'min:0', 'max:30'],
            'thumbnail_size' => ['required', 'integer', 'min:100', 'max:2000'], 'max_upload_width' => ['required', 'integer', 'min:320', 'max:5000'],
            'show_placeholder_image' => ['boolean'], 'allow_print' => ['boolean'], 'allow_comments' => ['boolean'], 'fetch_remote_images' => ['boolean'],
        ]);
        DB::table('modules')->updateOrInsert(['code' => 'news'], [
            'name' => 'News', 'module_type' => 'content',
            'page_title' => json_encode($data['page_title'], JSON_UNESCAPED_UNICODE),
            'seo_title' => json_encode($data['seo_title'], JSON_UNESCAPED_UNICODE),
            'meta_description' => json_encode($data['meta_description'], JSON_UNESCAPED_UNICODE),
            'is_active' => true, 'updated_at' => now(), 'created_at' => now(),
        ]);
        $moduleId = DB::table('modules')->where('code', 'news')->value('id');
        DB::table('modules')->where('id', $moduleId)->update([
            'page_title' => json_encode($data['page_title'], JSON_UNESCAPED_UNICODE),
            'seo_title' => json_encode($data['seo_title'], JSON_UNESCAPED_UNICODE),
            'meta_description' => json_encode($data['meta_description'], JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
        foreach (array_diff_key($data, array_flip(['page_title', 'seo_title', 'meta_description'])) as $key => $value) {
            DB::table('module_settings')->updateOrInsert(
                ['module_id' => $moduleId, 'setting_key' => $key],
                ['setting_value' => json_encode($value), 'setting_type' => is_bool($value) ? 'boolean' : 'number', 'updated_at' => now(), 'created_at' => now()]
            );
        }
        $this->dispatch('admin-toast', message: 'Đã lưu cấu hình tin tức.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.news.settings', [
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'breadcrumbs' => [['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'], ['label' => 'Tin tức'], ['label' => 'Cấu hình']],
        ]);
    }
}
