<?php

use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\CareersController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'application' => config('app.name'),
        'environment' => app()->environment(),
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{slug}', [NewsController::class, 'show']);
Route::get('/careers', [CareersController::class, 'index']);
Route::get('/careers/{slug}', [CareersController::class, 'show']);
Route::post('/careers/applications', [CareersController::class, 'store']);
