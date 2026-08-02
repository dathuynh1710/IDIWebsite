<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasTranslations, SoftDeletes;

    public const ABOUT_TEMPLATES = [
        'about' => 'Giới thiệu',
        'about-history' => 'Lịch sử hình thành',
        'about-values' => 'Giá trị cốt lõi',
        'about-leadership' => 'Ban lãnh đạo',
    ];

    public array $translatable = [
        'title',
        'slug',
        'summary',
        'content',
        'seo_title',
        'meta_description',
        'og_title',
        'og_description',
        'translation_status',
        'locale_published_at',
    ];

    protected $fillable = [
        'parent_id',
        'featured_media_id',
        'template',
        'code',
        'title',
        'slug',
        'summary',
        'content',
        'seo_title',
        'meta_description',
        'og_title',
        'og_description',
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
            'title' => 'array',
            'slug' => 'array',
            'summary' => 'array',
            'content' => 'array',
            'seo_title' => 'array',
            'meta_description' => 'array',
            'og_title' => 'array',
            'og_description' => 'array',
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

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function scopeAbout(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereIn('template', array_keys(self::ABOUT_TEMPLATES))
                ->orWhere('code', 'ABOUT')
                ->orWhere('code', 'like', 'ABOUT_%');
        });
    }
}
