<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\InvestorDocumentDownloadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\Api\PublicRoutesController::class, 'document']);

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/investor-documents/{file}/download', InvestorDocumentDownloadController::class)
    ->name('investors.documents.download');

Route::get('/sitemap.xml', [\App\Http\Controllers\Api\PublicRoutesController::class, 'sitemap']);
Route::get('/{publicPath}', [\App\Http\Controllers\Api\PublicRoutesController::class, 'document'])->where('publicPath', '(?:vi|en|zh|products|about|quality|sustainability|investors|news|recipes|careers|contact)(?:/.*)?');
