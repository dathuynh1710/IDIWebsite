<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class DocumentCategory extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = ['name', 'slug', 'description'];

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'sort_order', 'is_active',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'name' => 'array',
            'slug' => 'array',
            'description' => 'array',
            'sort_order' => 'integer',
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

    public function documents(): HasMany
    {
        return $this->hasMany(InvestorDocument::class);
    }

    public function scopeFiltered(Builder $query, string $search, string $active, string $locale): Builder
    {
        return $query
            ->when($search !== '', fn (Builder $q) => $q->where("name->{$locale}", 'like', "%{$search}%"))
            ->when($active !== '', fn (Builder $q) => $q->where('is_active', $active));
    }
}
