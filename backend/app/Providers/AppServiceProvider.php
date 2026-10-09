<?php

namespace App\Providers;

use App\Models\User;
use App\Support\AdminAudit;
use Illuminate\Database\Eloquent\Model;
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
        foreach (array_keys(\App\Support\PublicRoutes::TYPES) as $routeModel) {
            $routeModel::saving(fn (Model $model) => \App\Support\PublicRoutes::validate($model));
            $routeModel::saved(fn (Model $model) => \App\Support\PublicRoutes::sync($model));
            $routeModel::deleted(fn (Model $model) => \Illuminate\Support\Facades\DB::table('localized_routes')->where('routeable_type', $model::class)->where('routeable_id', $model->id)->delete());
            $routeModel::restored(fn (Model $model) => \App\Support\PublicRoutes::sync($model));
        }

        Gate::before(function (User $user): ?bool {
            return $user->hasRole('super-admin') ? true : null;
        });

        foreach ([
            'products' => 'products',
            'pages' => 'pages',
            'recipes' => 'recipes',
            'posts' => 'posts',
            'recruitment' => 'recruitment',
            'investors' => 'investor-documents',
        ] as $gatePrefix => $permissionPrefix) {
            foreach (['view', 'create', 'update', 'delete'] as $ability) {
                Gate::define(
                    "{$gatePrefix}.{$ability}",
                    fn (User $user): bool => $user->hasAnyPermission(["{$permissionPrefix}.{$ability}", "{$permissionPrefix}.manage"])
                );
            }
        }

        foreach (array_keys(AdminAudit::MODULES) as $modelClass) {
            /** @var class-string<Model> $modelClass */
            $modelClass::created(fn (Model $model) => AdminAudit::logModelEvent($model, 'created'));
            $modelClass::updated(fn (Model $model) => AdminAudit::logModelEvent($model, 'updated'));
            $modelClass::deleted(fn (Model $model) => AdminAudit::logModelEvent($model, 'deleted'));
        }
    }
}
