<?php

namespace App\Support;

use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

class RecipeRoutes
{
    private const PREFIXES = ['vi' => 'cong-thuc', 'en' => 'recipes', 'zh' => 'shipu'];

    public static function sync(Recipe $recipe): void
    {
        $slugs = $recipe->getTranslations('slug');
        $statuses = $recipe->getTranslations('translation_status');
        $publishedDates = $recipe->getTranslations('locale_published_at');
        $keptLocales = [];

        foreach (self::PREFIXES as $locale => $prefix) {
            $slug = trim((string) ($slugs[$locale] ?? ''));
            if ($slug === '') {
                continue;
            }

            $keptLocales[] = $locale;
            $status = $recipe->is_active ? ($statuses[$locale] ?? 'draft') : 'hidden';
            DB::table('localized_routes')->updateOrInsert([
                'routeable_type' => Recipe::class,
                'routeable_id' => $recipe->id,
                'locale' => $locale,
            ], [
                'route_name' => 'recipes.show',
                'slug' => $slug,
                'full_path' => "/{$locale}/{$prefix}/{$slug}",
                'status' => $status,
                'published_at' => $publishedDates[$locale] ?? null,
                'robots_index' => $status === 'published',
                'robots_follow' => true,
                'include_in_sitemap' => $status === 'published',
                'updated_by' => auth()->id(),
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }

        DB::table('localized_routes')
            ->where('routeable_type', Recipe::class)
            ->where('routeable_id', $recipe->id)
            ->when($keptLocales !== [], fn ($query) => $query->whereNotIn('locale', $keptLocales))
            ->delete();
    }
}
