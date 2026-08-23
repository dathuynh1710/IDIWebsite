<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use App\Models\InvestorDocument;
use App\Models\InvestorDocumentFile;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvestorRelationsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->ensureModuleEnabled();
        $locale = $this->locale($request);
        $category = trim($request->string('category')->toString());
        $limit = min(100, max(1, $request->integer('limit', 20)));
        $sort = $request->string('sort', 'newest')->toString() === 'oldest' ? 'oldest' : 'newest';

        $query = $this->publicDocuments()->with(['category', 'files.media']);
        $this->applyFilters($query, $request, $locale, $category);

        $direction = $sort === 'oldest' ? 'asc' : 'desc';
        $documents = $query
            ->orderBy('published_on', $direction)
            ->orderBy('sort_order', 'desc')
            ->orderBy('id', $direction)
            ->paginate($limit);

        return response()->json([
            'items' => collect($documents->items())
                ->map(fn (InvestorDocument $document): array => $this->document($document, $locale))
                ->values(),
            'categories' => $this->categories($locale),
            'years' => $this->years($category, $locale),
            'pageConfig' => $this->pageConfig($locale),
            'total' => $documents->total(),
            'page' => $documents->currentPage(),
            'limit' => $documents->perPage(),
            'lastPage' => $documents->lastPage(),
        ]);
    }

    private function publicDocuments(): Builder
    {
        return InvestorDocument::query()
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('document_category_id')
                    ->orWhereHas('category', fn (Builder $category) => $category->where('is_active', true));
            });
    }

    private function applyFilters(
        Builder $query,
        Request $request,
        string $locale,
        string $requestedCategory
    ): void {
        if ($requestedCategory !== '') {
            $this->applyCategoryFilter($query, $requestedCategory, $locale);
        }

        $search = trim($request->string('search')->toString());
        if ($search !== '') {
            $query->where(function (Builder $match) use ($search, $locale): void {
                $match->where("title->{$locale}", 'like', "%{$search}%")
                    ->orWhere('title->vi', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%");
            });
        }

        $year = $request->integer('year');
        if ($year >= 1900 && $year <= 2100) {
            $query->where('year', $year);
        }
    }

    private function categories(string $locale): array
    {
        return DocumentCategory::query()
            ->where('is_active', true)
            ->withCount([
                'documents as public_documents_count' => fn (Builder $query) => $query->where('is_active', true),
            ])
            ->orderByDesc('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (DocumentCategory $category): array => [
                'id' => $category->id,
                'name' => $this->translation($category, 'name', $locale),
                'slug' => $this->translation($category, 'slug', $locale),
                'description' => $this->translation($category, 'description', $locale),
                'count' => $category->public_documents_count,
            ])
            ->values()
            ->all();
    }

    private function years(string $category, string $locale): array
    {
        $query = $this->publicDocuments()->whereNotNull('year');
        if ($category !== '') {
            $this->applyCategoryFilter($query, $category, $locale);
        }

        return $query->distinct()->orderByDesc('year')->pluck('year')->map(fn ($year): int => (int) $year)->all();
    }

    private function applyCategoryFilter(Builder $query, string $requestedCategory, string $locale): void
    {
        $query->whereHas('category', function (Builder $category) use ($requestedCategory, $locale): void {
            $category->where(function (Builder $match) use ($requestedCategory, $locale): void {
                $match->where("slug->{$locale}", $requestedCategory)
                    ->orWhere('slug->vi', $requestedCategory);

                if (ctype_digit($requestedCategory)) {
                    $match->orWhereKey((int) $requestedCategory);
                }
            });
        });
    }

    private function document(InvestorDocument $document, string $locale): array
    {
        $category = $document->category;
        $file = $document->files->firstWhere('locale', $locale)
            ?? $document->files->firstWhere('locale', 'vi')
            ?? $document->files->first();

        return [
            'id' => $document->id,
            'slug' => $document->slug,
            'locale' => filled($document->getTranslation('title', $locale, false)) ? $locale : 'vi',
            'title' => $this->translation($document, 'title', $locale),
            'summary' => $this->translation($document, 'summary', $locale),
            'documentNumber' => $document->document_number,
            'year' => $document->year,
            'quarter' => $document->quarter,
            'publishedOn' => $document->published_on?->toDateString(),
            'updatedAt' => $document->updated_at?->toIso8601String(),
            'isFeatured' => $document->is_featured,
            'category' => $category ? [
                'id' => $category->id,
                'name' => $this->translation($category, 'name', $locale),
                'slug' => $this->translation($category, 'slug', $locale),
            ] : null,
            'file' => $file ? $this->file($file, $locale) : null,
        ];
    }

    private function file(InvestorDocumentFile $file, string $locale): array
    {
        $media = $file->media;
        $downloadUrl = $media?->external_url
            ?: route('investors.documents.download', $file, absolute: false);

        return [
            'id' => $file->id,
            'name' => $this->translation($file, 'display_name', $locale)
                ?: $media?->original_name,
            'locale' => $file->locale,
            'url' => $downloadUrl,
            'mimeType' => $media?->mime_type,
            'extension' => strtolower((string) $media?->extension),
            'size' => $media?->file_size,
        ];
    }

    private function pageConfig(string $locale): array
    {
        $module = DB::table('modules')->where('code', 'investors')->first();
        $title = $this->localizedValue($module?->page_title, $locale)
            ?: ['vi' => 'Quan hệ cổ đông', 'en' => 'Investor Relations', 'zh' => '投资者关系'][$locale];
        $description = $this->localizedValue($module?->description, $locale);

        return [
            'title' => $title,
            'description' => $description,
            'seo' => [
                'title' => $this->localizedValue($module?->seo_title, $locale) ?: $title,
                'description' => $this->localizedValue($module?->meta_description, $locale) ?: $description,
            ],
            'updatedAt' => $this->publicDocuments()->max('updated_at'),
        ];
    }

    private function translation(object $model, string $field, string $locale): ?string
    {
        $value = $model->getTranslation($field, $locale, false)
            ?: $model->getTranslation($field, 'vi', false);

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    private function localizedValue(mixed $value, string $locale): ?string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (! is_array($decoded)) {
                return trim($value) !== '' ? $value : null;
            }
            $value = $decoded;
        }

        if (! is_array($value)) {
            return null;
        }

        $localized = $value[$locale] ?? $value['vi'] ?? null;

        return is_scalar($localized) && trim((string) $localized) !== '' ? (string) $localized : null;
    }

    private function locale(Request $request): string
    {
        return Locale::fromRequest($request);
    }

    private function ensureModuleEnabled(): void
    {
        $enabled = DB::table('modules')->where('code', 'investors')->value('is_active');
        abort_if($enabled !== null && ! $enabled, 404);
    }
}
