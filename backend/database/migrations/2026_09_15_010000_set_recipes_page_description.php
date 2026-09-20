<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $module = DB::table('modules')->where('code', 'recipes')->first();
        if (! $module) {
            return;
        }

        $description = json_decode($module->description ?: '[]', true) ?: [];
        $legacy = [
            'vi' => 'Khám phá những công thức ngon, dễ thực hiện từ IDI Seafood.',
            'en' => 'Discover delicious, easy-to-follow recipes from IDI Seafood.',
            'zh' => '探索 IDI Seafood 提供的美味易做食谱。',
        ];
        $desired = [
            'vi' => 'Khám phá những công thức món ăn để làm mới thực đơn của bạn.',
            'en' => 'Discover delicious pangasius recipes to refresh your menu.',
            'zh' => '探索美味的巴沙鱼食谱，丰富您的菜单。',
        ];

        foreach ($desired as $locale => $value) {
            if (blank($description[$locale] ?? null) || ($description[$locale] ?? null) === $legacy[$locale]) {
                $description[$locale] = $value;
            }
        }

        DB::table('modules')->where('id', $module->id)->update([
            'description' => json_encode($description, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Preserve administrator-authored content on rollback.
    }
};
