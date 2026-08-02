<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function preview(Request $request, Post $post)
    {
        Gate::authorize('posts.view');
        $locale = in_array($request->string('locale')->toString(), ['vi', 'en', 'zh'], true)
            ? $request->string('locale')->toString() : 'vi';

        return view('admin.posts.preview', compact('post', 'locale'));
    }
}
