<?php

namespace App\Support;

use App\Models\JobPosition;
use Illuminate\Support\Facades\DB;

class JobPositionRoutes
{
    public static function sync(JobPosition $position): void
    {
        foreach (['vi' => 'tuyen-dung', 'en' => 'careers', 'zh' => 'zhaopin'] as $locale => $prefix) {
            $slug = $position->getTranslation('slug', $locale, false);
            if (! $slug) {
                DB::table('localized_routes')->where([
                    'routeable_type' => JobPosition::class,
                    'routeable_id' => $position->id,
                    'locale' => $locale,
                ])->delete();
                continue;
            }

            $status = $position->getTranslation('translation_status', $locale, false) ?: 'draft';
            DB::table('localized_routes')->updateOrInsert([
                'routeable_type' => JobPosition::class,
                'routeable_id' => $position->id,
                'locale' => $locale,
            ], [
                'route_name' => 'careers.show',
                'slug' => $slug,
                'full_path' => "/{$locale}/{$prefix}/{$slug}",
                'status' => $position->is_active ? $status : 'hidden',
                'published_at' => $status === 'published' ? now() : null,
                'robots_index' => true,
                'robots_follow' => true,
                'include_in_sitemap' => true,
                'created_by' => $position->created_by,
                'updated_by' => $position->updated_by,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
