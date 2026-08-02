<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class OfficeLocation extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = ['name', 'company', 'address'];

    protected $fillable = [
        'code', 'name', 'company', 'address', 'phone', 'fax', 'email', 'map_type', 'map_embed',
        'map_url', 'map_image',
        'latitude', 'longitude', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'company' => 'array',
            'address' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }
}
