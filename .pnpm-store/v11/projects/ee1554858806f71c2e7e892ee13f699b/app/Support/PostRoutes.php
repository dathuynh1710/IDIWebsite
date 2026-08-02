<?php

namespace App\Support;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PostRoutes
{
    private const POST_PREFIXES = ['vi' => 'tin-tuc', 'en' => 'news', 'zh' => 'xinwen'];
    private const CATEGORY_PREFIXES = ['vi' => 'tin-tuc', 'en' => 'news', 'zh' => 'xinwen'];

    public static function syncPost(Post $post): void
    {
        self::sync($post, 'news.show', self::POST_PREFIXES);
    }

    public static function syncCategory(PostCategory $category): void
    {
        self::sync($category, 'news.category', self::CATEGORY_PREFIXES);
    }

    private static function sync(Model $model, string $routeName, array $prefixes): void
    {
        $slugs = $model->getTranslations('slug');
        $statuses = $model->getTranslations('translation_status');
        $published = $model->getTranslations('locale_published_at');
        $kept = [];

        foreach ($prefixes as $locale => $prefix) {
            $slug = trim((string) ($slugs[$locale] ?? ''));
            if ($slug === '') {
                continue;
            }
            $kept[] = $locale;
            $status = $model->is_active ? ($statuses[$locale] ?? 'draft') : 'hidden';
            DB::table('localized_routes')->updateOrInsert([
                'routeable_type' => $model::class,
                'routeable_id' => $model->id,
                'locale' => $locale,
            ], [
                'route_name' => $routeName,
                'slug' => $slug,
                'full_path' => "/{$locale}/{$prefix}/{$slug}",
                'status' => $status,
                'published_at' => $published[$locale] ?? null,
                'robots_index' => $status === 'published',
                'robots_follow' => true,
                'include_in_sitemap' => $status === 'published',
                'updated_by' => auth()->id(),
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }

        DB::table('localized_routes')
            ->where('routeable_type', $model::class)->where('routeable_id', $model->id)
            ->when($kept !== [], fn ($q) => $q->whereNotIn('locale', $kept))->delete();
    }
}
