<?php

namespace App\Support;

use App\Models\Recipe;

class RecipeRoutes
{
    public static function sync(Recipe $recipe): void
    {
        PublicRoutes::sync($recipe);
    }
}
