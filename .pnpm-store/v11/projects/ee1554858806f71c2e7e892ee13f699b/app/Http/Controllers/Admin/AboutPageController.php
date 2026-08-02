<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AboutPageController extends Controller
{
    public function preview(Request $request, Page $page): View
    {
        Gate::authorize('pages.view');
        abort_unless(Page::query()->about()->whereKey($page)->exists(), 404);
        $locale = $request->validate(['locale' => ['nullable', 'in:vi,en,zh']])['locale'] ?? 'vi';

        return view('admin.about-pages.preview', compact('page', 'locale'));
    }
}
