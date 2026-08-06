<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    public array $translatable = [
        'title',
        'slug',
        'short_description',
        'description',
        'content',
        'seo_title',
        'meta_description',
        'og_title',
        'og_description',
        'translation_status',
        'locale_published_at',
    ];

    protected $fillable = [
        'product_category_id',
        'featured_media_id',
        'scientific_name',
        'sku',
        'title',
        'slug',
        'short_description',
        'description',
        'content',
        'seo_title',
        'meta_description',
        'translation_status',
        'locale_published_at',
        'sort_order',
        'is_featured',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'slug' => 'array',
            'short_description' => 'array',
            'description' => 'array',
            'content' => 'array',
            'seo_title' => 'array',
            'meta_description' => 'array',
            'translation_status' => 'array',
            'locale_published_at' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('sku', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, fn (Builder $query, mixed $category) => $query->where('product_category_id', $category))
            ->when(isset($filters['active']) && $filters['active'] !== '', fn (Builder $query) => $query->where('is_active', $filters['active']))
            ->when(isset($filters['featured']) && $filters['featured'] !== '', fn (Builder $query) => $query->where('is_featured', $filters['featured']))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('updated_at', '>=', $date));
    }
}
