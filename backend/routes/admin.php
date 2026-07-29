<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('media/editor-image', [ProductController::class, 'uploadEditorImage'])->name('media.editor-image');
        Route::get('products/{product}/preview', [ProductController::class, 'preview'])->name('products.preview');
        Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
        Route::resource('products', ProductController::class)->except('show');
        Route::get('product-categories', [ProductCategoryController::class, 'index'])
            ->name('product-categories.index');
    });
