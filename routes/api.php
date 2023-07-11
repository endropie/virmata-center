<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:api'])->group(function() {
    Route::prefix('auth')->group(function() {
        Route::get('user', [\App\Http\Api\AuthController::class, 'user']);
    });

    Route::prefix('tenants')->group(function() {
        Route::get('{id}/invites', [\App\Http\Api\TenantInviteController::class, 'index']);
        Route::post('{id}/invites', [\App\Http\Api\TenantInviteController::class, 'store']);
        Route::get('/', [\App\Http\Api\TenantController::class, 'index']);
        Route::post('/', [\App\Http\Api\TenantController::class, 'store']);
    });
});
