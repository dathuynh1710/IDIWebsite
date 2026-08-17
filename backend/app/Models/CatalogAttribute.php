<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class CatalogAttribute extends Model
{
    use HasTranslations;

    protected $table = 'attributes';

    public array $translatable = ['name', 'unit'];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'unit' => 'array',
            'options' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class, 'attribute_id');
    }
}
