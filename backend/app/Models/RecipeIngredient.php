<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class RecipeIngredient extends Model
{
    use HasTranslations;

    public array $translatable = ['name', 'unit', 'note'];

    protected $fillable = ['recipe_id', 'name', 'quantity', 'unit', 'note', 'sort_order'];

    protected function casts(): array
    {
        return ['name' => 'array', 'unit' => 'array', 'note' => 'array'];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
