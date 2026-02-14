<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PublicMenuController;
use App\Http\Controllers\Public\QrSessionController;
use App\Http\Controllers\Api\PlaceOrderController;
// routes/api.php
Route::post('/orders', [PlaceOrderController::class, 'store']);
Route::get('/orders/session/{token}', [PlaceOrderController::class, 'getSessionOrders']);

Route::prefix('qr')->group(function () {
    Route::get(
        '/validate/{restaurant}/{table}/{token}',
        [QrSessionController::class, 'validateQr']
    );

    Route::post(
        '/session/start/{restaurant}/{table}/{token}',
        [QrSessionController::class, 'startSession']
    );
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get(
    '/menu/{restaurant}/{table}/{token}',
    [PublicMenuController::class, 'show']
)->name('menu.view');
