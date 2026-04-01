<?php

use App\Http\Controllers\Api\GeoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('geo')->group(function (): void {
    Route::get('/tree', [GeoController::class, 'tree']);
    Route::get('/units/{id}', [GeoController::class, 'unit']);
    Route::get('/map-features', [GeoController::class, 'mapFeatures']);
});
