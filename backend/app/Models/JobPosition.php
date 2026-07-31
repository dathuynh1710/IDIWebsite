<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class JobPosition extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = [
        'title', 'slug', 'location', 'summary', 'description', 'requirements',
        'benefits', 'seo_title', 'meta_description', 'translation_status',
        'locale_published_at',
    ];

    protected $fillable = [
        'code', 'department', 'title', 'slug', 'location', 'summary',
        'description', 'requirements', 'benefits', 'seo_title',
        'meta_description', 'quantity', 'expires_at', 'translation_status',
        'locale_published_at', 'sort_order', 'is_active', 'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array', 'slug' => 'array', 'location' => 'array',
            'summary' => 'array', 'description' => 'array',
            'requirements' => 'array', 'benefits' => 'array',
            'seo_title' => 'array', 'meta_description' => 'array',
            'translation_status' => 'array', 'locale_published_at' => 'array',
            'expires_at' => 'datetime', 'is_active' => 'boolean',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        $locale = $filters['locale'] ?? 'vi';

        return $query
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(
                fn (Builder $nested) => $nested
                    ->where("title->{$locale}", 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
            ))
            ->when(($filters['active'] ?? '') !== '', fn (Builder $q) => $q->where('is_active', $filters['active']))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $date) => $q->whereDate('created_at', '<=', $date));
    }
}
