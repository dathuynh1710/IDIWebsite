<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ProductCategory extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = ['name', 'slug', 'description', 'seo_title', 'meta_description'];

    protected $fillable = [
        'parent_id',
        'featured_media_id',
        'code',
        'name',
        'slug',
        'description',
        'seo_title',
        'meta_description',
        'translation_status',
        'locale_published_at',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'slug' => 'array',
            'description' => 'array',
            'seo_title' => 'array',
            'meta_description' => 'array',
            'translation_status' => 'array',
            'locale_published_at' => 'array',
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

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }
}
