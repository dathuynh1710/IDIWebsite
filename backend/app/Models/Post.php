<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Post extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = [
        'title', 'slug', 'excerpt', 'content', 'seo_title', 'meta_description',
        'og_title', 'og_description', 'translation_status', 'locale_published_at',
    ];

    protected $fillable = [
        'post_category_id', 'featured_media_id', 'author_id', 'code', 'title',
        'slug', 'excerpt', 'content', 'seo_title', 'meta_description', 'og_title',
        'og_description', 'schema_extra', 'translation_status',
        'locale_published_at', 'sort_order', 'is_featured', 'is_active',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array', 'slug' => 'array', 'excerpt' => 'array',
            'content' => 'array', 'seo_title' => 'array',
            'meta_description' => 'array', 'og_title' => 'array',
            'og_description' => 'array', 'schema_extra' => 'array',
            'translation_status' => 'array', 'locale_published_at' => 'array',
            'is_featured' => 'boolean', 'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function scopePublished(Builder $query, string $locale): Builder
    {
        $locale = in_array($locale, ['vi', 'en', 'zh'], true) ? $locale : 'vi';
        $publishedBefore = now()->toIso8601String();

        return $query
            ->where('is_active', true)
            ->where("translation_status->{$locale}", 'published')
            ->where(function (Builder $query) use ($locale, $publishedBefore): void {
                $query->whereNull("locale_published_at->{$locale}")
                    ->orWhereRaw(
                        "REPLACE({$this->localizedPublishedAtExpression($query, $locale)}, ' ', 'T') <= ?",
                        [$publishedBefore]
                    );
            })
            ->where(function (Builder $query) use ($locale): void {
                $query->whereNull('post_category_id')
                    ->orWhereHas('category', fn (Builder $category) => $category->published($locale));
            });
    }

    private function localizedPublishedAtExpression(Builder $query, string $locale): string
    {
        $column = $query->getModel()->qualifyColumn('locale_published_at');
        $path = '$."'.$locale.'"';

        return match ($query->getConnection()->getDriverName()) {
            'mysql', 'mariadb' => "JSON_UNQUOTE(JSON_EXTRACT({$column}, '{$path}'))",
            'pgsql' => "{$column}->>'{$locale}'",
            'sqlsrv' => "JSON_VALUE({$column}, '$.{$locale}')",
            default => "json_extract({$column}, '{$path}')",
        };
    }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        $locale = $filters['locale'] ?? 'vi';

        return $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) use ($locale): void {
                $q->where(fn (Builder $nested) => $nested
                    ->where("title->{$locale}", 'like', "%{$search}%")
                    ->orWhere("slug->{$locale}", 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%"));
            })
            ->when($filters['category'] ?? null, fn (Builder $q, $id) => $q->where('post_category_id', $id))
            ->when(($filters['active'] ?? '') !== '', fn (Builder $q) => $q->where('is_active', $filters['active']))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '<=', $date));
    }
}
