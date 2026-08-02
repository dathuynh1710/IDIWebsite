<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $moduleId = DB::table('modules')->where('code', 'news')->value('id');
        if (! $moduleId) {
            return;
        }

        foreach ([
            'items_per_page' => [12, 'number'],
            'category_items_per_page' => [10, 'number'],
            'featured_limit' => [3, 'number'],
            'related_limit' => [6, 'number'],
            'show_placeholder_image' => [true, 'boolean'],
            'thumbnail_size' => [320, 'number'],
            'max_upload_width' => [1600, 'number'],
            'allow_print' => [true, 'boolean'],
            'allow_comments' => [false, 'boolean'],
            'fetch_remote_images' => [false, 'boolean'],
        ] as $key => [$value, $type]) {
            DB::table('module_settings')->updateOrInsert(
                ['module_id' => $moduleId, 'setting_key' => $key],
                ['setting_value' => json_encode($value), 'setting_type' => $type, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        $moduleId = DB::table('modules')->where('code', 'news')->value('id');
        DB::table('module_settings')->where('module_id', $moduleId)
            ->whereIn('setting_key', ['category_items_per_page', 'featured_limit', 'related_limit', 'show_placeholder_image', 'thumbnail_size', 'max_upload_width', 'allow_print', 'allow_comments', 'fetch_remote_images'])
            ->delete();
    }
};
