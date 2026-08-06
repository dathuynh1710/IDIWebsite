<?php

namespace App\Livewire\Admin\AboutPages;

use App\Livewire\AdminComponent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Cấu hình giới thiệu')]
class Settings extends AdminComponent
{
    public array $page_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $description = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $seo_title = ['vi' => '', 'en' => '', 'zh' => ''];

    public array $meta_description = ['vi' => '', 'en' => '', 'zh' => ''];

    public function mount(): void
    {
        Gate::authorize('pages.update');
        $module = DB::table('modules')->where('code', 'about')->first();
        if (! $module) {
            return;
        }
        foreach (['page_title', 'description', 'seo_title', 'meta_description'] as $field) {
            $this->{$field} = array_replace($this->{$field}, json_decode($module->{$field} ?: '[]', true) ?: []);
        }
    }

    public function save(): void
    {
        Gate::authorize('pages.update');
        $validated = $this->validate([
            'page_title.vi' => ['required', 'string', 'max:255'],
            'page_title.en' => ['nullable', 'string', 'max:255'],
            'page_title.zh' => ['nullable', 'string', 'max:255'],
            'description.*' => ['nullable', 'string', 'max:1000'],
            'seo_title.*' => ['nullable', 'string', 'max:255'],
            'meta_description.*' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated): void {
            $values = [
                'name' => 'About',
                'module_type' => 'content',
                'page_title' => json_encode($validated['page_title'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode($validated['description'], JSON_UNESCAPED_UNICODE),
                'seo_title' => json_encode($validated['seo_title'], JSON_UNESCAPED_UNICODE),
                'meta_description' => json_encode($validated['meta_description'], JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ];
            DB::table('modules')->updateOrInsert(['code' => 'about'], $values + ['created_at' => now()]);
        });
        $this->toast('Đã cập nhật cấu hình giới thiệu.');
    }

    public function render()
    {
        return view('livewire.admin.about-pages.settings', [
            'locales' => ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文'],
            'breadcrumbs' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard'],
                ['label' => 'Quản lý giới thiệu', 'route' => 'admin.about-pages.index'],
                ['label' => 'Cấu hình'],
            ],
        ]);
    }
}
