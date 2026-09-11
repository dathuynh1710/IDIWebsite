<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const POST_CODES = [
        'IDI_NEWS_20260427_AGM',
        'IDI_NEWS_20240729_PATAGONIA_PARTNERSHIP',
        'IDI_NEWS_20240606_THAIFEX',
    ];

    public function up(): void
    {
        $activityCategoryId = DB::table('post_categories')
            ->where('code', 'ACTIVITY_NEWS')
            ->whereNull('deleted_at')
            ->value('id');

        if (! $activityCategoryId) {
            return;
        }

        DB::table('posts')
            ->whereIn('code', self::POST_CODES)
            ->whereNull('post_category_id')
            ->update([
                'post_category_id' => $activityCategoryId,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('posts')
            ->whereIn('code', self::POST_CODES)
            ->where('post_category_id', DB::table('post_categories')
                ->where('code', 'ACTIVITY_NEWS')
                ->value('id'))
            ->update([
                'post_category_id' => null,
                'updated_at' => now(),
            ]);
    }
};
