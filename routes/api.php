<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AppSettingController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('app-settings', AppSettingController::class)->only(['index', 'show']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
