<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'IDI Seafood API',
        'health' => url('/api/health'),
    ]);
});
