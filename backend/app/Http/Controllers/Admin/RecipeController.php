<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RecipeController extends Controller
{
    public function preview(Request $request, Recipe $recipe): View
    {
        Gate::authorize('recipes.view');
        $locale = in_array($request->query('locale'), ['vi', 'en', 'zh'], true) ? $request->query('locale') : 'vi';

        return view('admin.recipes.preview', [
            'recipe' => $recipe->load(['featuredMedia', 'videoMedia']),
            'locale' => $locale,
        ]);
    }
}
