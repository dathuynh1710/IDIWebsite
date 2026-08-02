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
            Gate::define(
                "pages.{$ability}",
                fn (User $user): bool => $user->getAllPermissions()->contains('name', 'pages.manage')
            );
            Gate::define(
                "recipes.{$ability}",
                fn (User $user): bool => $user->getAllPermissions()->contains('name', 'recipes.manage')
            );
            Gate::define(
                "posts.{$ability}",
                fn (User $user): bool => $user->getAllPermissions()->contains('name', 'posts.manage')
            );
            Gate::define(
                "recruitment.{$ability}",
                fn (User $user): bool => $user->getAllPermissions()->contains('name', 'recruitment.manage')
            );
            Gate::define(
                "investors.{$ability}",
                fn (User $user): bool => $user->getAllPermissions()->contains('name', 'investor-documents.manage')
            );
        }
    }
}
