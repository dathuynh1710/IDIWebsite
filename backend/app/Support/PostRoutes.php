<?php

namespace App\Support;

use App\Models\Post;
use App\Models\PostCategory;

class PostRoutes
{
    public static function syncPost(Post $post): void
    {
        PublicRoutes::sync($post);
    }

    public static function syncCategory(PostCategory $category): void
    {
        PublicRoutes::sync($category);
    }
}
