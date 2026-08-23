<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Recipe extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = [
        'title', 'slug', 'summary', 'content_left', 'content_right', 'seo_title', 'meta_description',
        'translation_status', 'locale_published_at',
    ];

    protected $fillable = [
        'featured_media_id', 'video_media_id', 'code', 'title', 'slug', 'summary',
        'content_left', 'content_right',
        'seo_title', 'meta_description', 'translation_status', 'locale_published_at',
        'sort_order', 'is_featured', 'is_active',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'slug' => 'array',
            'summary' => 'array',
            'content_left' => 'array',
            'content_right' => 'array',
            'seo_title' => 'array',
            'meta_description' => 'array',
            'translation_status' => 'array',
            'locale_published_at' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
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

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function scopePublished(Builder $query, string $locale): Builder
    {
        $locale = in_array($locale, ['vi', 'en', 'zh'], true) ? $locale : 'vi';

        return $query
            ->where('is_active', true)
            ->where("translation_status->{$locale}", 'published')
            ->where(function (Builder $query) use ($locale): void {
                $query->whereNull("locale_published_at->{$locale}")
                    ->orWhere("locale_published_at->{$locale}", '<=', now()->toIso8601String());
            });
    }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        $locale = in_array($filters['locale'] ?? null, ['vi', 'en', 'zh'], true) ? $filters['locale'] : 'vi';

        return $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search) use ($filters): void {
                $locale = $filters['locale'] ?? 'vi';
                $query->where(fn (Builder $builder) => $builder
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere("title->{$locale}", 'like', "%{$search}%")
                    ->orWhere("slug->{$locale}", 'like', "%{$search}%"));
            })
            ->when(($filters['active'] ?? '') !== '', fn (Builder $query) => $query->where('is_active', $filters['active']))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->where("locale_published_at->{$locale}", '>=', $date.'T00:00:00'))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->where("locale_published_at->{$locale}", '<=', $date.'T23:59:59+99:99'));
    }
}
