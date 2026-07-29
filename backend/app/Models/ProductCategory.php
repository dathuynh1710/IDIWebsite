<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ProductCategory extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = ['name', 'slug', 'description', 'seo_title', 'meta_description'];

    protected $fillable = ['code', 'name', 'slug', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['name' => 'array', 'slug' => 'array', 'is_active' => 'boolean'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
