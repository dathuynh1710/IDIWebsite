<?php

namespace App\Support;

use App\Models\Page;

class AboutPageRoutes
{
    public static function paths(Page $page): array
    {
        return PublicRoutes::paths($page, false);
    }

    public static function sync(Page $page): void
    {
        PublicRoutes::sync($page);
    }
}
