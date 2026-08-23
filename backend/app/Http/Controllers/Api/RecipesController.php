<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Support\Locale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->ensureModuleEnabled();
        $locale = $this->locale($request);
        $settings = $this->moduleSettings();
        $limit = min(100, max(1, $request->integer('limit', (int) ($settings['items_per_page'] ?? 12))));

        $recipes = Recipe::query()
            ->published($locale)
            ->with(['featuredMedia'])
            ->when($request->boolean('featured'), fn ($query) => $query->where('is_featured', true))
            ->orderByDesc('is_featured')
            ->orderByDesc('sort_order')
            ->orderBy('id')
            ->paginate($limit);

        return response()->json([
            'items' => collect($recipes->items())->map(fn (Recipe $recipe) => $this->recipe($recipe, $locale))->values(),
            'pageConfig' => $this->pageConfig($locale, $settings),
            'total' => $recipes->total(),
            'page' => $recipes->currentPage(),
            'limit' => $recipes->perPage(),
            'lastPage' => $recipes->lastPage(),
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $this->ensureModuleEnabled();
        $locale = $this->locale($request);
        $settings = $this->moduleSettings();
        $recipe = Recipe::query()
            ->published($locale)
            ->with(['featuredMedia', 'videoMedia'])
            ->where("slug->{$locale}", $slug)
            ->firstOrFail();

        return response()->json([
            'data' => $this->recipe($recipe, $locale, true),
            'pageConfig' => $this->pageConfig($locale, $settings),
        ]);
    }

    private function recipe(Recipe $recipe, string $locale, bool $detail = false): array
    {
        $title = $recipe->getTranslation('title', $locale, false);
        $summary = $recipe->getTranslation('summary', $locale, false);
        $media = $recipe->featuredMedia;

        $data = [
            'id' => $recipe->id,
            'code' => $recipe->code,
            'slug' => $recipe->getTranslation('slug', $locale, false),
            'title' => $title,
            'summary' => $summary,
            'image' => $media ? [
                'url' => $media->url,
                'alt' => $this->localizedValue($media->alt_text, $locale) ?: $title,
            ] : null,
            'isFeatured' => $recipe->is_featured,
            'seo' => [
                'title' => $recipe->getTranslation('seo_title', $locale, false) ?: $title,
                'description' => $recipe->getTranslation('meta_description', $locale, false) ?: $summary,
            ],
        ];

        if ($detail) {
            $data['contentLeftHtml'] = $recipe->getTranslation('content_left', $locale, false) ?: '';
            $data['contentRightHtml'] = $recipe->getTranslation('content_right', $locale, false) ?: '';
            $data['videoUrl'] = $recipe->videoMedia?->url;
        }

        return $data;
    }

    private function pageConfig(string $locale, array $settings): array
    {
        $module = DB::table('modules')->where('code', 'recipes')->first();
        $defaults = [
            'vi' => ['title' => 'Công thức bạn có thể thử', 'description' => 'Khám phá những công thức cá tra thơm ngon để làm mới thực đơn của bạn.'],
            'en' => ['title' => 'Recipes you can try', 'description' => 'Discover delicious pangasius recipes to refresh your menu.'],
            'zh' => ['title' => '您可以尝试的食谱', 'description' => '探索美味的巴沙鱼食谱，丰富您的菜单。'],
        ];
        $title = $this->localizedValue($module?->page_title, $locale) ?: $defaults[$locale]['title'];
        $description = $this->localizedValue($module?->description, $locale) ?: $defaults[$locale]['description'];

        return [
            'title' => $title,
            'description' => $description,
            'itemsPerPage' => (int) ($settings['items_per_page'] ?? 12),
            'showPlaceholderImage' => (bool) ($settings['show_placeholder_image'] ?? true),
            'seo' => [
                'title' => $this->localizedValue($module?->seo_title, $locale) ?: $title,
                'description' => $this->localizedValue($module?->meta_description, $locale) ?: $description,
            ],
        ];
    }

    private function moduleSettings(): array
    {
        $moduleId = DB::table('modules')->where('code', 'recipes')->value('id');
        if (! $moduleId) {
            return [];
        }

        return DB::table('module_settings')->where('module_id', $moduleId)
            ->pluck('setting_value', 'setting_key')
            ->map(fn ($value) => json_decode($value, true))
            ->all();
    }

    private function localizedValue(mixed $value, string $locale): ?string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return trim($value) ?: null;
            }
            $value = $decoded;
        }

        $localized = is_array($value) ? ($value[$locale] ?? null) : null;

        return is_string($localized) && trim($localized) !== '' ? $localized : null;
    }

    private function locale(Request $request): string
    {
        return Locale::fromRequest($request);
    }

    private function ensureModuleEnabled(): void
    {
        $enabled = DB::table('modules')->where('code', 'recipes')->value('is_active');
        abort_if($enabled !== null && ! $enabled, 404);
    }
}
