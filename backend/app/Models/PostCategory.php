<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class PostCategory extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = [
        'name', 'slug', 'description', 'seo_title', 'meta_description',
        'translation_status', 'locale_published_at',
    ];

    protected $fillable = [
        'parent_id', 'featured_media_id', 'code', 'name', 'slug', 'description',
        'seo_title', 'meta_description', 'translation_status', 'locale_published_at',
        'sort_order', 'is_active', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array', 'slug' => 'array', 'description' => 'array',
            'seo_title' => 'array', 'meta_description' => 'array',
            'translation_status' => 'array', 'locale_published_at' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
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

    public function scopeFiltered(Builder $query, string $search, string $active, string $locale): Builder
    {
        return $query
            ->when($search !== '', fn (Builder $q) => $q->where("name->{$locale}", 'like', "%{$search}%"))
            ->when($active !== '', fn (Builder $q) => $q->where('is_active', $active));
    }
}
