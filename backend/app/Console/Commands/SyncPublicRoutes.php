<?php

namespace App\Console\Commands;

use App\Support\PublicRoutes;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPublicRoutes extends Command
{
    protected $signature = 'routes:sync-public';

    protected $description = 'Idempotently backfill public routes; roll back on URL collisions';

    public function handle(): int
    {
        DB::transaction(function () {
            foreach (array_keys(PublicRoutes::TYPES) as $class) {
                $class::query()->each(fn ($model) => PublicRoutes::sync($model));
            }
        });
        $this->info('Public routes synchronized.');

        return self::SUCCESS;
    }
}
