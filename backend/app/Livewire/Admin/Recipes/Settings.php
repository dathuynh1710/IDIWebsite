<?php

namespace App\Livewire\Admin\Recipes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Cấu hình Recipes')]
class Settings extends Component
{
    public array $page_title = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $description = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];
    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];
    public bool $is_active = true;
    public int $items_per_page = 12;
    public bool $show_placeholder_image = true;
    public int $thumbnail_size = 180;
    public int $max_upload_width = 1600;

    public function mount(): void
    {
        Gate::authorize('recipes.update');
        $module = DB::table('modules')->where('code', 'recipes')->first();
        if (! $module) {
            return;
        }

        foreach (['page_title', 'description', 'seo_title', 'meta_description'] as $field) {
            $this->{$field} = array_replace($this->{$field}, json_decode($module->{$field} ?: '[]', true) ?: []);
        }
        $this->is_active = (bool) $module->is_active;
        $settings = DB::table('module_settings')->where('module_id', $module->id)->pluck('setting_value', 'setting_key');
        foreach (['items_per_page', 'show_placeholder_image', 'thumbnail_size', 'max_upload_width'] as $key) {
            if ($settings->has($key)) {
                $this->{$key} = json_decode($settings[$key], true);
            }
        }
    }

    public function save(): void
    {
        Gate::authorize('recipes.update');
        $validated = $this->validate([
            'page_title.vi' => ['required', 'string', 'max:255'],
            'page_title.en' => ['nullable', 'string', 'max:255'],
            'page_title.zh' => ['nullable', 'string', 'max:255'],
            'description.*' => ['nullable', 'string', 'max:2000'],
            'seo_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description.*' => ['nullable', 'string', 'max:500'],
            'is_active' => ['required', 'boolean'],
            'items_per_page' => ['required', 'integer', 'min:1', 'max:100'],
            'show_placeholder_image' => ['required', 'boolean'],
            'thumbnail_size' => ['required', 'integer', 'min:50', 'max:1000'],
            'max_upload_width' => ['required', 'integer', 'min:320', 'max:5000'],
        ]);

        DB::transaction(function () use ($validated): void {
            DB::table('modules')->updateOrInsert(['code' => 'recipes'], [
                'name' => 'Recipes',
                'module_type' => 'content',
                'page_title' => json_encode($validated['page_title'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode($validated['description'], JSON_UNESCAPED_UNICODE),
                'seo_title' => json_encode($validated['seo_title'], JSON_UNESCAPED_UNICODE),
                'meta_description' => json_encode($validated['meta_description'], JSON_UNESCAPED_UNICODE),
                'is_active' => $validated['is_active'],
                'updated_at' => now(),
                'created_at' => now(),
            ]);
            $moduleId = (int) DB::table('modules')->where('code', 'recipes')->value('id');
            foreach ([
                'items_per_page' => 'number',
                'show_placeholder_image' => 'boolean',
                'thumbnail_size' => 'number',
                'max_upload_width' => 'number',
            ] as $key => $type) {
                DB::table('module_settings')->updateOrInsert([
                    'module_id' => $moduleId,
                    'setting_key' => $key,
                ], [
                    'setting_value' => json_encode($validated[$key], JSON_THROW_ON_ERROR),
                    'setting_type' => $type,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
            }
        });

        $this->dispatch('admin-toast', message: 'Đã cập nhật cấu hình Recipes.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.recipes.settings', [
            'locales' => config('admin.locales'),
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Quản lý Recipes', 'route' => 'admin.recipes.index'],
                ['label' => 'Cấu hình chung'],
            ],
        ]);
    }
}
