<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class RecipeStep extends Model
{
    use HasTranslations;

    public array $translatable = ['instruction'];

    protected $fillable = ['recipe_id', 'media_id', 'instruction', 'sort_order'];

    protected function casts(): array
    {
        return ['instruction' => 'array'];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
