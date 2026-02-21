<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PublicMenuController;
use App\Http\Controllers\Public\QrSessionController;
use App\Http\Controllers\Api\PlaceOrderController;
// routes/api.php
use App\Http\Controllers\Api\WaiterAppController;

// Waiter App Auth
Route::post('/waiter/login', [WaiterAppController::class, 'login']);

// Protected Waiter Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/waiter/orders/ready', [WaiterAppController::class, 'getReadyOrders']);
    Route::post('/waiter/orders/{id}/serve', [WaiterAppController::class, 'markAsServed']);
});
Route::post('/orders', [PlaceOrderController::class, 'store']);
Route::get('/orders/session/{token}', [PlaceOrderController::class, 'getSessionOrders']);
Route::get(
    '/table/{tableId}/pending-requests',
    [QrSessionController::class, 'getPendingRequests']
);
Route::post(
    '/session/{sessionId}/respond',
    [QrSessionController::class, 'respondToJoin']
);

Route::prefix('qr')->group(function () {
    Route::get(
        '/validate/{restaurant}/{table}/{token}',
        [QrSessionController::class, 'validateQr']
    );
    Route::post('/session/leave', [QrSessionController::class, 'leaveSession']);
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
