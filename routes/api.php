<?php

use App\Http\Controllers\Api\V1\Admin\TourController as AdminTourController;
use App\Http\Controllers\Api\V1\Admin\TravelController as AdminTravelController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\TourController;
use App\Http\Controllers\Api\V1\TravelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//Route::apiResource('v1/travels', TravelController::class);
Route::get('v1/travels', [TravelController::class, 'index']);
Route::get('v1/travels/{travel:slug}/tours', [TourController::class, 'index']);

Route::prefix('v1/admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('travels', [AdminTravelController::class, 'store']);
    Route::post('travels/{travel}/tours', [AdminTourController::class, 'store']);
});

Route::post('v1/login', LoginController::class);
