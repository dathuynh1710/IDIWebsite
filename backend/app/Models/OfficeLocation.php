<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class OfficeLocation extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = ['name', 'address'];

    protected $fillable = [
        'code', 'name', 'address', 'phone', 'email', 'map_embed',
        'latitude', 'longitude', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'address' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }
}
