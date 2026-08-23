<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->ensureModuleEnabled();
        $locale = $this->locale($request);
        $requestedCategory = $request->string('category')->toString();

        $categories = ProductCategory::query()
            ->with([
                'featuredMedia',
                'products' => fn ($query) => $this->publishedProducts($query, $locale),
            ])
            ->where('is_active', true)
            ->where("translation_status->{$locale}", 'published')
            ->when($requestedCategory !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($requestedCategory, $locale): void {
                $query->where('id', $requestedCategory)
                    ->orWhere('code', $requestedCategory)
                    ->orWhere("slug->{$locale}", $requestedCategory);
            }))
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'categories' => $categories->map(fn (ProductCategory $category) => $this->category($category, $locale))->values(),
            'total' => $categories->sum(fn (ProductCategory $category): int => $category->products->count()),
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $this->ensureModuleEnabled();
        $locale = $this->locale($request);

        $product = $this->publishedProducts(Product::query(), $locale)
            ->where("slug->{$locale}", $slug)
            ->firstOrFail();

        return response()->json(['data' => $this->product($product, $locale)]);
    }

    private function publishedProducts(Builder|Relation $query, string $locale): Builder|Relation
    {
        return $query
            ->with(['category', 'featuredMedia', 'productAttributes.attribute'])
            ->where('is_active', true)
            ->where("translation_status->{$locale}", 'published')
            ->orderByDesc('sort_order')
            ->latest('created_at');
    }

    private function category(ProductCategory $category, string $locale): array
    {
        return [
            'id' => $category->id,
            'code' => $category->code,
            'slug' => $category->getTranslation('slug', $locale, false),
            'name' => $category->getTranslation('name', $locale, false),
            'description' => $category->getTranslation('description', $locale, false),
            'image' => $category->featuredMedia?->url,
            'products' => $category->products
                ->map(fn (Product $product) => $this->product($product, $locale))
                ->values(),
        ];
    }

    private function product(Product $product, string $locale): array
    {
        $attributes = $product->productAttributes->keyBy(fn ($item) => $item->attribute?->code);
        $specs = $product->schema_extra ?? [];

        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'sortOrder' => $product->sort_order,
            'slug' => $product->getTranslation('slug', $locale, false),
            'name' => $product->getTranslation('title', $locale, false),
            'description' => $product->getTranslation('short_description', $locale, false),
            'image' => $product->featuredMedia?->url,
            'sizes' => $attributes->get('SIZE')?->value ?? [],
            'freezingMethod' => $specs['freezing_method'] ?? 'IQF / Block Frozen',
            'storageTemperature' => $specs['storage_temperature'] ?? '≤ -18°C',
            'packaging' => $specs['packaging'] ?? 'Theo yêu cầu khách hàng',
            'certifications' => $specs['certifications'] ?? ['ASC', 'BRC AA', 'HACCP'],
            'origin' => $specs['origin'] ?? 'Đồng Tháp, Việt Nam',
            'shelfLife' => $specs['shelf_life'] ?? '24 tháng',
            'sourceUrl' => $specs['source_url'] ?? null,
            'isFeatured' => $product->is_featured,
        ];
    }

    private function locale(Request $request): string
    {
        return Locale::fromRequest($request);
    }

    private function ensureModuleEnabled(): void
    {
        $enabled = DB::table('modules')->where('code', 'products')->value('is_active');
        abort_if($enabled !== null && ! $enabled, 404);
    }
}
