<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->ensureModuleEnabled();
        $locale = $this->locale($request);
        $settings = $this->moduleSettings();
        $requestedCategory = trim($request->string('category')->toString());
        $defaultLimit = $requestedCategory !== ''
            ? $this->integerSetting($settings, 'category_items_per_page', 10)
            : $this->integerSetting($settings, 'items_per_page', 12);
        $limit = min(100, max(1, $request->integer('limit', $defaultLimit)));
        $sort = in_array($request->string('sort', 'newest')->toString(), ['newest', 'oldest'], true)
            ? $request->string('sort', 'newest')->toString()
            : 'newest';

        $query = $this->publishedPosts($locale);
        $this->applyFilters($query, $request, $locale, $requestedCategory);
        $posts = $this->orderPosts($query, $locale, $sort)->paginate($limit);

        $featuredLimit = $this->integerSetting($settings, 'featured_limit', 3);
        $featured = $this->orderPosts(
            $this->publishedPosts($locale)
                ->where('is_featured', true)
                ->orderByDesc('sort_order'),
            $locale,
            'newest'
        )->limit(max(0, $featuredLimit))->get();

        return response()->json([
            'items' => collect($posts->items())
                ->map(fn (Post $post): array => $this->article($post, $locale))
                ->values(),
            'featured' => $featured
                ->map(fn (Post $post): array => $this->article($post, $locale))
                ->values(),
            'categories' => $this->categories($locale),
            'pageConfig' => $this->pageConfig($locale, $settings),
            'total' => $posts->total(),
            'page' => $posts->currentPage(),
            'limit' => $posts->perPage(),
            'lastPage' => $posts->lastPage(),
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $this->ensureModuleEnabled();
        $locale = $this->locale($request);
        $settings = $this->moduleSettings();
        $post = $this->publishedPosts($locale)
            ->where("slug->{$locale}", $slug)
            ->firstOrFail();

        return response()->json([
            'data' => $this->article($post, $locale, true),
            'pageConfig' => $this->pageConfig($locale, $settings),
        ]);
    }

    private function publishedPosts(string $locale): Builder
    {
        return Post::query()
            ->published($locale)
            ->with([
                'category',
                'featuredMedia',
                'author',
                'tags' => fn ($query) => $query->active()->orderBy('tags.id'),
            ]);
    }

    private function applyFilters(
        Builder $query,
        Request $request,
        string $locale,
        string $requestedCategory
    ): void {
        if ($requestedCategory !== '') {
            $query->whereHas('category', function (Builder $category) use ($requestedCategory, $locale): void {
                $category->where(function (Builder $match) use ($requestedCategory, $locale): void {
                    $match->where('code', $requestedCategory)
                        ->orWhere("slug->{$locale}", $requestedCategory)
                        ->orWhere("name->{$locale}", $requestedCategory);

                    if (ctype_digit($requestedCategory)) {
                        $match->orWhere('id', (int) $requestedCategory);
                    }
                });
            });
        }

        $search = trim($request->string('search')->toString());
        if ($search !== '') {
            $query->where(function (Builder $match) use ($search, $locale): void {
                $match->where("title->{$locale}", 'like', "%{$search}%")
                    ->orWhere("excerpt->{$locale}", 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('category', fn (Builder $category) => $category
                        ->where("name->{$locale}", 'like', "%{$search}%"));
            });
        }

        if ($request->has('featured')) {
            $featured = filter_var($request->input('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) {
                $query->where('is_featured', $featured);
            }
        }

        $exclude = trim($request->string('exclude')->toString());
        if ($exclude !== '') {
            if (ctype_digit($exclude)) {
                $query->whereKeyNot((int) $exclude);
            }
            $query->where(function (Builder $match) use ($exclude, $locale): void {
                $match->whereNull("slug->{$locale}")
                    ->orWhere("slug->{$locale}", '!=', $exclude);
            });
        }
    }

    private function orderPosts(Builder $query, string $locale, string $sort): Builder
    {
        $direction = $sort === 'oldest' ? 'asc' : 'desc';

        return $query
            ->orderBy("locale_published_at->{$locale}", $direction)
            ->orderByDesc('sort_order')
            ->orderBy('created_at', $direction)
            ->orderBy('id', $direction);
    }

    private function categories(string $locale): array
    {
        return PostCategory::query()
            ->published($locale)
            ->withCount([
                'posts as published_posts_count' => fn ($query) => $query->published($locale),
            ])
            ->orderByDesc('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (PostCategory $category): array => [
                'id' => $category->id,
                'code' => $category->code,
                'slug' => $category->getTranslation('slug', $locale, false),
                'name' => $category->getTranslation('name', $locale, false),
                'description' => $category->getTranslation('description', $locale, false),
                'count' => $category->published_posts_count,
            ])
            ->values()
            ->all();
    }

    private function article(Post $post, string $locale, bool $detail = false): array
    {
        $title = $post->getTranslation('title', $locale, false);
        $excerpt = $post->getTranslation('excerpt', $locale, false);
        $content = $post->getTranslation('content', $locale, false) ?: '';
        $publishedAt = $post->getTranslation('locale_published_at', $locale, false)
            ?: $post->created_at?->toIso8601String();
        $schema = is_array($post->schema_extra) ? $post->schema_extra : [];
        $media = $post->featuredMedia;
        $category = $post->category;
        $seoTitle = $post->getTranslation('seo_title', $locale, false) ?: $title;
        $seoDescription = $post->getTranslation('meta_description', $locale, false) ?: $excerpt;
        $readTime = max(1, (int) ($schema['read_time'] ?? $this->estimatedReadTime($content)));
        $viewCount = array_key_exists('view_count', $schema) && $schema['view_count'] !== null
            ? max(0, (int) $schema['view_count'])
            : null;

        $article = [
            'id' => $post->id,
            'code' => $post->code,
            'locale' => $locale,
            'slug' => $post->getTranslation('slug', $locale, false),
            'title' => $title,
            'excerpt' => $excerpt,
            'category' => $category ? [
                'id' => $category->id,
                'code' => $category->code,
                'name' => $category->getTranslation('name', $locale, false),
                'slug' => $category->getTranslation('slug', $locale, false),
            ] : null,
            'image' => $media ? [
                'url' => $media->url,
                'alt' => $this->localizedValue($media->alt_text, $locale) ?: $title,
                'caption' => $this->localizedValue($media->caption, $locale),
            ] : null,
            'author' => [
                'name' => $post->author?->name ?: 'Ban Truyền thông IDI',
                'role' => (string) ($schema['author_role'] ?? 'IDI Seafood'),
            ],
            'publishedAt' => $publishedAt,
            'date' => $publishedAt,
            'updatedAt' => $post->updated_at?->toIso8601String(),
            'isFeatured' => $post->is_featured,
            'readTime' => $readTime,
            'tags' => $post->tags
                ->map(fn ($tag): string => $tag->getTranslation('name', $locale, false)
                    ?: $tag->getTranslation('name', 'vi', false))
                ->filter()
                ->values()
                ->all(),
            'sourceUrl' => $schema['source_url'] ?? null,
            'viewCount' => $viewCount,
            'seo' => [
                'title' => $seoTitle,
                'description' => $seoDescription,
                'ogTitle' => $post->getTranslation('og_title', $locale, false) ?: $seoTitle,
                'ogDescription' => $post->getTranslation('og_description', $locale, false) ?: $seoDescription,
            ],
        ];

        if ($detail) {
            $article['contentHtml'] = $content;
        }

        return $article;
    }

    private function estimatedReadTime(string $content): int
    {
        $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags($content)) ?? '');
        if ($plainText === '') {
            return 1;
        }

        $words = preg_split('/\s+/u', $plainText, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return max(1, (int) ceil(count($words) / 200));
    }

    private function pageConfig(string $locale, array $settings): array
    {
        $module = DB::table('modules')->where('code', 'news')->first();
        $defaultTitle = ['vi' => 'Tin tức', 'en' => 'News', 'zh' => '新闻'][$locale] ?? 'Tin tức';
        $title = $this->localizedValue($module?->page_title, $locale) ?: $defaultTitle;
        $description = $this->localizedValue($module?->description, $locale);
        $seoTitle = $this->localizedValue($module?->seo_title, $locale) ?: $title;
        $seoDescription = $this->localizedValue($module?->meta_description, $locale) ?: $description;
        $ogTitle = $this->localizedValue($module?->og_title, $locale) ?: $seoTitle;
        $ogDescription = $this->localizedValue($module?->og_description, $locale) ?: $seoDescription;

        $presentationDefaults = [
            'show_featured_section' => true,
            'show_category_navigation' => true,
            'show_related_articles' => true,
            'show_author' => true,
            'show_published_date' => true,
            'show_view_count' => true,
            'show_reading_time' => true,
            'show_tags' => true,
            'show_article_source' => true,
            'show_breadcrumb' => true,
            'show_social_share' => true,
            'show_previous_next' => true,
            'show_placeholder_image' => true,
            'allow_print' => true,
            'lazy_load_images' => true,
        ];
        $presentation = [];
        foreach ($presentationDefaults as $key => $default) {
            $presentation[$this->camelCase($key)] = (bool) ($settings[$key] ?? $default);
        }

        return [
            'title' => $title,
            'description' => $description,
            'seo' => [
                'title' => $seoTitle,
                'description' => $seoDescription,
                'keywords' => $this->localizedValue($settings['meta_keywords'] ?? null, $locale),
                'ogTitle' => $ogTitle,
                'ogDescription' => $ogDescription,
            ],
            'itemsPerPage' => $this->integerSetting($settings, 'items_per_page', 12),
            'categoryItemsPerPage' => $this->integerSetting($settings, 'category_items_per_page', 10),
            'featuredLimit' => $this->integerSetting($settings, 'featured_limit', 3),
            'relatedLimit' => $this->integerSetting($settings, 'related_limit', 6),
            'presentation' => $presentation,
        ];
    }

    private function moduleSettings(): array
    {
        $moduleId = DB::table('modules')->where('code', 'news')->value('id');
        if (! $moduleId) {
            return [];
        }

        return DB::table('module_settings')
            ->where('module_id', $moduleId)
            ->pluck('setting_value', 'setting_key')
            ->map(fn ($value) => json_decode($value, true))
            ->all();
    }

    private function integerSetting(array $settings, string $key, int $default): int
    {
        return max(0, (int) ($settings[$key] ?? $default));
    }

    private function localizedValue(mixed $value, string $locale): ?string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                return trim($value) !== '' ? $value : null;
            }
        }

        if (! is_array($value)) {
            return null;
        }

        $localized = $value[$locale] ?? $value['vi'] ?? null;

        return is_scalar($localized) && trim((string) $localized) !== '' ? (string) $localized : null;
    }

    private function camelCase(string $value): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $value))));
    }

    private function locale(Request $request): string
    {
        $locale = $request->string('locale', $request->string('lang', 'vi')->toString())->toString();

        return in_array($locale, ['vi', 'en', 'zh'], true) ? $locale : 'vi';
    }

    private function ensureModuleEnabled(): void
    {
        $enabled = DB::table('modules')->where('code', 'news')->value('is_active');
        abort_if($enabled !== null && ! $enabled, 404);
    }
}
