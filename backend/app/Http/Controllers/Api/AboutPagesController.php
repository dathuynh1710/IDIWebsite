<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AboutPagesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->ensureModuleEnabled();
        $requestedLocale = $this->locale($request);

        $pages = Page::query()
            ->about()
            ->with('featuredMedia')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Page $page): array => $this->page($page, $requestedLocale))
            ->values();

        return response()->json([
            'items' => $pages,
            'total' => $pages->count(),
            'module' => $this->module($requestedLocale),
        ]);
    }

    public function show(Request $request, string $identifier): JsonResponse
    {
        $this->ensureModuleEnabled();
        $requestedLocale = $this->locale($request);
        $normalizedCode = strtoupper(str_replace('-', '_', $identifier));

        $query = Page::query()
            ->about()
            ->with('featuredMedia')
            ->where('is_active', true);

        $page = (clone $query)->where('code', $normalizedCode)->first();
        $page ??= (clone $query)
            ->where(function (Builder $query) use ($identifier, $requestedLocale): void {
                $query->where("slug->{$requestedLocale}", $identifier)
                    ->orWhere('slug->vi', $identifier);
            })
            ->first();

        $template = match ($normalizedCode) {
            'ABOUT_MESSAGE' => 'about',
            'ABOUT_HISTORY' => 'about-history',
            'ABOUT_VALUES' => 'about-values',
            default => null,
        };
        $page ??= $template
            ? (clone $query)->where('template', $template)->orderBy('sort_order')->first()
            : null;

        abort_if(! $page, 404);

        return response()->json(['data' => $this->page($page, $requestedLocale)]);
    }

    private function page(Page $page, string $requestedLocale): array
    {
        $locale = filled($page->getTranslation('title', $requestedLocale, false))
            ? $requestedLocale
            : 'vi';
        $media = $page->featuredMedia;
        $mediaAlt = is_array($media?->alt_text)
            ? ($media->alt_text[$locale] ?? $media->alt_text['vi'] ?? null)
            : null;

        return [
            'id' => $page->id,
            'code' => $page->code,
            'template' => $page->template,
            'locale' => $locale,
            'requestedLocale' => $requestedLocale,
            'slug' => $page->getTranslation('slug', $locale, false),
            'title' => $page->getTranslation('title', $locale, false),
            'summary' => $page->getTranslation('summary', $locale, false),
            'content' => $page->getTranslation('content', $locale, false),
            'image' => $media ? [
                'url' => $media->url,
                'alt' => $mediaAlt ?: $page->getTranslation('title', $locale, false),
            ] : null,
            'seo' => [
                'title' => $page->getTranslation('seo_title', $locale, false)
                    ?: $page->getTranslation('title', $locale, false),
                'description' => $page->getTranslation('meta_description', $locale, false)
                    ?: $page->getTranslation('summary', $locale, false),
                'keywords' => $page->getTranslation('meta_keywords', $locale, false),
            ],
            'updatedAt' => $page->updated_at?->toIso8601String(),
        ];
    }

    private function module(string $requestedLocale): ?array
    {
        $module = DB::table('modules')->where('code', 'about')->first();
        if (! $module) {
            return null;
        }

        $localized = function (?string $value) use ($requestedLocale): ?string {
            $translations = json_decode($value ?: '[]', true) ?: [];

            return $translations[$requestedLocale] ?? $translations['vi'] ?? null;
        };

        return [
            'title' => $localized($module->page_title),
            'description' => $localized($module->description),
            'seo' => [
                'title' => $localized($module->seo_title),
                'description' => $localized($module->meta_description),
            ],
        ];
    }

    private function locale(Request $request): string
    {
        $locale = $request->string('locale', $request->string('lang', 'vi')->toString())->toString();

        return in_array($locale, ['vi', 'en', 'zh'], true) ? $locale : 'vi';
    }

    private function ensureModuleEnabled(): void
    {
        $enabled = DB::table('modules')->where('code', 'about')->value('is_active');
        abort_if($enabled !== null && ! $enabled, 404);
    }
}
