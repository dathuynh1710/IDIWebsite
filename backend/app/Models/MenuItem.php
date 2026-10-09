<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class MenuItem extends Model
{
    use HasTranslations;

    public array $translatable = ['label'];

    protected $attributes = ['link_type' => 'internal', 'sort_order' => 0, 'is_active' => true];

    protected $fillable = ['menu_id', 'parent_id', 'label', 'link_type', 'url', 'page_id', 'product_category_id', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['label' => 'array', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }
}
