<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Facades\DB;

class AboutPageRoutes
{
    private const PREFIXES = [
        'vi' => 'gioi-thieu',
        'en' => 'about',
        'zh' => 'guanyu',
    ];

    public static function sync(Page $page): void
    {
        $slugs = $page->getTranslations('slug');
        $keptLocales = [];

        foreach (self::PREFIXES as $locale => $prefix) {
            $slug = trim((string) ($slugs[$locale] ?? ''));
            if ($slug === '') {
                continue;
            }

            $keptLocales[] = $locale;
            $status = $page->is_active ? 'published' : 'hidden';
            DB::table('localized_routes')->updateOrInsert([
                'routeable_type' => Page::class,
                'routeable_id' => $page->id,
                'locale' => $locale,
            ], [
                'route_name' => 'about.show',
                'slug' => $slug,
                'full_path' => "/{$locale}/{$prefix}/{$slug}",
                'status' => $status,
                'published_at' => null,
                'robots_index' => $status === 'published',
                'robots_follow' => true,
                'include_in_sitemap' => $status === 'published',
                'updated_by' => auth()->id(),
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }

        DB::table('localized_routes')
            ->where('routeable_type', Page::class)
            ->where('routeable_id', $page->id)
            ->when($keptLocales !== [], fn ($query) => $query->whereNotIn('locale', $keptLocales))
            ->delete();
    }
}
