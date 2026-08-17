<?php

use App\Http\Controllers\Api\AboutPagesController;
use App\Http\Controllers\Api\CareersController;
use App\Http\Controllers\Api\ContactsController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ProductsController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'application' => config('app.name'),
        'environment' => app()->environment(),
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::get('/about', [AboutPagesController::class, 'index']);
Route::get('/about/{identifier}', [AboutPagesController::class, 'show']);
Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{slug}', [NewsController::class, 'show']);
Route::get('/products', [ProductsController::class, 'index']);
Route::get('/products/{slug}', [ProductsController::class, 'show']);
Route::get('/careers', [CareersController::class, 'index']);
Route::get('/careers/{slug}', [CareersController::class, 'show']);
Route::post('/careers/applications', [CareersController::class, 'store']);
Route::post('/contacts', [ContactsController::class, 'store'])->middleware('throttle:10,1');
