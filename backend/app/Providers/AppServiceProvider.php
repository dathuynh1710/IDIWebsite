<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user): ?bool {
            return $user->hasRole('super-admin') ? true : null;
        });

        foreach (['view', 'create', 'update', 'delete'] as $ability) {
            Gate::define(
                "products.{$ability}",
                fn (User $user): bool => $user->getAllPermissions()->contains('name', 'products.manage')
            );
        }
    }
}
