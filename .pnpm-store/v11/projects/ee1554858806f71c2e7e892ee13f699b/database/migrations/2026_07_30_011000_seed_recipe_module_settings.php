<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('modules')->updateOrInsert(['code' => 'recipes'], [
            'name' => 'Recipes',
            'module_type' => 'content',
            'page_title' => json_encode([
                'vi' => 'Công thức bạn có thể thử',
                'en' => 'Recipes you can try',
                'zh' => '值得尝试的食谱',
            ], JSON_UNESCAPED_UNICODE),
            'description' => json_encode([
                'vi' => 'Khám phá những công thức ngon, dễ thực hiện từ IDI Seafood.',
                'en' => 'Discover delicious, easy-to-follow recipes from IDI Seafood.',
                'zh' => '探索 IDI Seafood 提供的美味易做食谱。',
            ], JSON_UNESCAPED_UNICODE),
            'seo_title' => json_encode([
                'vi' => 'Công thức bạn có thể thử',
                'en' => 'Recipes you can try',
                'zh' => '值得尝试的食谱',
            ], JSON_UNESCAPED_UNICODE),
            'is_active' => true,
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        $moduleId = (int) DB::table('modules')->where('code', 'recipes')->value('id');
        foreach ([
            ['key' => 'items_per_page', 'value' => 12, 'type' => 'number'],
            ['key' => 'show_placeholder_image', 'value' => true, 'type' => 'boolean'],
            ['key' => 'thumbnail_size', 'value' => 180, 'type' => 'number'],
            ['key' => 'max_upload_width', 'value' => 1600, 'type' => 'number'],
        ] as $setting) {
            DB::table('module_settings')->updateOrInsert([
                'module_id' => $moduleId,
                'setting_key' => $setting['key'],
            ], [
                'setting_value' => json_encode($setting['value'], JSON_THROW_ON_ERROR),
                'setting_type' => $setting['type'],
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Preserve administrator-authored module content and settings on rollback.
    }
};
