<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Recipe extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = [
        'title', 'slug', 'summary', 'content', 'seo_title', 'meta_description',
        'translation_status', 'locale_published_at',
    ];

    protected $fillable = [
        'featured_media_id', 'video_media_id', 'code', 'title', 'slug', 'summary',
        'content', 'servings', 'preparation_time', 'cooking_time', 'difficulty',
        'seo_title', 'meta_description', 'translation_status', 'locale_published_at',
        'sort_order', 'is_featured', 'is_active', 'show_ingredients', 'show_steps',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'slug' => 'array',
            'summary' => 'array',
            'content' => 'array',
            'seo_title' => 'array',
            'meta_description' => 'array',
            'translation_status' => 'array',
            'locale_published_at' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'show_ingredients' => 'boolean',
            'show_steps' => 'boolean',
        ];
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function videoMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'video_media_id');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class)->orderBy('sort_order');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(RecipeStep::class)->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search) use ($filters): void {
                $locale = $filters['locale'] ?? 'vi';
                $query->where(fn (Builder $builder) => $builder
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere("title->{$locale}", 'like', "%{$search}%")
                    ->orWhere("slug->{$locale}", 'like', "%{$search}%"));
            })
            ->when(($filters['difficulty'] ?? '') !== '', fn (Builder $query) => $query->where('difficulty', $filters['difficulty']))
            ->when(($filters['active'] ?? '') !== '', fn (Builder $query) => $query->where('is_active', $filters['active']))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('updated_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('updated_at', '<=', $date));
    }
}
