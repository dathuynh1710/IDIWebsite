<?php

namespace App\Support;

use App\Models\JobPosition;

class JobPositionRoutes
{
    public static function sync(JobPosition $position): void
    {
        PublicRoutes::sync($position);
    }
}
