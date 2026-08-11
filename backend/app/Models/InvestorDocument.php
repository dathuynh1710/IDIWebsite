<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class InvestorDocument extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = ['title', 'summary'];

    protected $fillable = [
        'document_category_id', 'title', 'summary', 'document_number', 'year',
        'quarter', 'published_on', 'sort_order', 'is_featured', 'is_active',
        'slug', 'seo_title', 'meta_description', 'meta_keywords',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'summary' => 'array',
            'published_on' => 'date',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(InvestorDocumentFile::class)->orderBy('sort_order');
    }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        $locale = $filters['locale'] ?? 'vi';

        return $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) use ($locale): void {
                $q->where(fn (Builder $nested) => $nested
                    ->where("title->{$locale}", 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%"));
            })
            ->when($filters['category'] ?? null, fn (Builder $q, $id) => $q->where('document_category_id', $id))
            ->when($filters['year'] ?? null, fn (Builder $q, $year) => $q->where('year', $year))
            ->when(($filters['active'] ?? '') !== '', fn (Builder $q) => $q->where('is_active', $filters['active']))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('published_on', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $date) => $q->whereDate('published_on', '<=', $date));
    }
}
