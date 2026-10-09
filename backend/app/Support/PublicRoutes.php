<?php

namespace App\Support;

use App\Models\JobPosition;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Recipe;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublicRoutes
{
    public const TYPES = [Page::class => ['about.show', 'about'], Post::class => ['news.show', 'news'], PostCategory::class => ['news.category', 'news'], Product::class => ['products.show', 'products'], ProductCategory::class => ['product-categories.show', 'products'], Recipe::class => ['recipes.show', 'recipes'], JobPosition::class => ['careers.show', 'careers']];

    public static function statics(): array
    {
        static $routes;

        return $routes ??= json_decode(file_get_contents(base_path('../shared/public-routes.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    public static function definition(Model $model): ?array
    {
        if ($model instanceof Page && ! in_array($model->template, array_keys(Page::ABOUT_TEMPLATES)) && ! str_starts_with($model->code ?? '', 'ABOUT')) {
            return null;
        }

        return self::TYPES[$model::class] ?? null;
    }

    public static function path(Model $model, string $locale): ?string
    {
        $definition = self::definition($model);
        $slug = trim((string) $model->getTranslation('slug', $locale, false));
        if (! $definition || ! $slug) {
            return null;
        }
        $prefix = $definition[1] === 'about' ? '/'.$locale.'/'.['vi' => 'gioi-thieu', 'en' => 'about', 'zh' => 'guanyu'][$locale] : self::statics()['/'.$definition[1]][$locale];

        return $prefix.'/'.$slug;
    }

    private static function translations(Model $model, string $field): array
    {
        return in_array($field, $model->translatable) ? $model->getTranslations($field) : ($model->getAttribute($field) ?? []);
    }

    public static function available(Model $model, string $locale, bool $checkModule = true): bool
    {
        if (! $model->is_active || (method_exists($model, 'trashed') && $model->trashed())) {
            return false;
        }
        $title = $model instanceof ProductCategory || $model instanceof PostCategory ? 'name' : 'title';
        if (! filled($model->getTranslation($title, $locale, false)) || ! self::path($model, $locale)) {
            return false;
        }
        $status = self::translations($model, 'translation_status')[$locale] ?? null;
        if (($status ?: ($model instanceof Page ? 'published' : 'draft')) !== 'published') {
            return false;
        }
        $date = self::translations($model, 'locale_published_at')[$locale] ?? null;
        if ($date && Carbon::parse($date)->isFuture()) {
            return false;
        }
        if ($model instanceof JobPosition && $model->expires_at?->endOfDay()->isPast()) {
            return false;
        }
        if ($model instanceof Post && $model->category && ! self::available($model->category, $locale, $checkModule)) {
            return false;
        }
        if ($model instanceof Product && $model->category && ! self::available($model->category, $locale, $checkModule)) {
            return false;
        }
        if (! $checkModule) {
            return true;
        }
        $module = self::definition($model)[1];
        $enabled = DB::table('modules')->where('code', $module)->value('is_active');

        return $enabled === null || (bool) $enabled;
    }

    public static function paths(Model $model, bool $checkModule = true): array
    {
        $paths = [];
        foreach (['vi', 'en', 'zh'] as $locale) {
            if (self::available($model, $locale, $checkModule)) {
                $paths[$locale] = self::path($model, $locale);
            }
        }

        return $paths;
    }

    public static function validate(Model $model): void
    {
        if (! self::definition($model)) {
            return;
        }
        foreach (['vi', 'en', 'zh'] as $locale) {
            $path = self::path($model, $locale);
            if (! $path) {
                continue;
            }
            $slug = $model->getTranslation('slug', $locale, false);
            if (preg_match('~[/\\\\?#\s%]~u', $slug) || in_array($slug, ['.', '..'])) {
                throw ValidationException::withMessages(["slug.$locale" => 'Invalid URL slug.']);
            }
            $collision = DB::table('localized_routes')->where('full_path', $path)->where(fn ($q) => $q->where('routeable_type', '!=', $model::class)->orWhere('routeable_id', '!=', $model->id ?? 0))->exists();
            if ($collision) {
                throw ValidationException::withMessages(["slug.$locale" => 'This URL is already used by another page.']);
            }
        }
    }

    public static function sync(Model $model): void
    {
        if (! self::definition($model)) {
            return;
        }
        self::validate($model);
        DB::transaction(function () use ($model) {
            foreach (['vi', 'en', 'zh'] as $locale) {
                $identity = ['routeable_type' => $model::class, 'routeable_id' => $model->id, 'locale' => $locale];
                $old = DB::table('localized_routes')->where($identity)->first();
                $path = self::path($model, $locale);
                if (! $path || ! DB::table('locales')->where('code', $locale)->exists()) {
                    DB::table('localized_routes')->where($identity)->delete();

                    continue;
                }
                if ($old && $old->full_path !== $path) {
                    DB::table('redirects')->where('to_path', $old->full_path)->update(['to_path' => $path]);
                    DB::table('redirects')->updateOrInsert(['from_path' => $old->full_path], ['to_path' => $path, 'status_code' => 301, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
                    $group = self::definition($model)[1];
                    $ambiguous = DB::table('localized_routes')->where('slug', $old->slug)->where('route_name', $old->route_name)->where('routeable_id', '!=', $model->id)->exists();
                    if (! $ambiguous && $group !== 'about') {
                        DB::table('redirects')->updateOrInsert(['from_path' => '/'.$group.'/'.$old->slug], ['to_path' => $path, 'status_code' => 301, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
                    }
                }
                DB::table('redirects')->where('from_path', $path)->delete();
                $status = $model->is_active ? (self::translations($model, 'translation_status')[$locale] ?? ($model instanceof Page ? 'published' : 'draft')) : 'hidden';
                DB::table('localized_routes')->updateOrInsert($identity, ['route_name' => self::definition($model)[0], 'slug' => $model->getTranslation('slug', $locale, false), 'full_path' => $path, 'status' => $status, 'published_at' => self::translations($model, 'locale_published_at')[$locale] ?? null, 'robots_index' => $status === 'published', 'robots_follow' => true, 'include_in_sitemap' => $status === 'published', 'created_at' => $old?->created_at ?? now(), 'updated_at' => now()]);
            }
        });
    }

    public static function entries(): array
    {
        $entries = [];
        $modules = DB::table('modules')->pluck('is_active', 'code');
        foreach (self::statics() as $legacy => $paths) {
            $module = explode('/', trim($legacy, '/'))[0];
            $enabled = $modules->get($module);
            if ($enabled !== null && ! $enabled) {
                continue;
            }
            $entries[] = ['key' => $legacy, 'legacy' => $legacy, 'paths' => $paths, 'name' => 'static'];
        }
        $routes = DB::table('localized_routes')->get()->keyBy(fn ($route) => $route->routeable_type.':'.$route->routeable_id.':'.$route->locale);
        foreach (self::TYPES as $class => [$name, $group]) {
            if ($modules->has($group) && ! $modules->get($group)) {
                continue;
            }
            $query = $class::query();
            if (in_array($class, [Product::class, Post::class])) {
                $query->with('category');
            }
            foreach ($query->get() as $model) {
                if (! self::definition($model)) {
                    continue;
                }
                $paths = self::paths($model, false);
                if (! $paths) {
                    continue;
                }
                $sitemapPaths = [];
                foreach ($paths as $locale => $path) {
                    $route = $routes->get($class.':'.$model->id.':'.$locale);
                    if (! $route || ($route->include_in_sitemap && $route->robots_index)) {
                        $sitemapPaths[$locale] = $path;
                    }
                }
                $entries[] = ['key' => $class.':'.$model->id, 'name' => $name, 'group' => $group, 'id' => $model->id, 'code' => $model->code, 'slugs' => array_intersect_key($model->getTranslations('slug'), $paths), 'paths' => $paths, 'sitemapPaths' => $sitemapPaths];
            }
        }

        return $entries;
    }
}
