<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WasteController;

Route::post('/predict', [WasteController::class, 'predict']);
Route::post('/telemetry/capacity', [WasteController::class, 'recordCapacity']);
Route::get('/dashboard/stats', [WasteController::class, 'stats']);
