<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\PublicMenuController;
use App\Http\Controllers\Public\QrSessionController;
use App\Http\Controllers\Api\PlaceOrderController;
use App\Http\Controllers\Api\WaiterAppController;
use App\Models\QrSession;
use Laravel\Sanctum\PersonalAccessToken;
use Pusher\Pusher;

/*
|--------------------------------------------------------------------------
| REAL-TIME WEBSOCKET AUTHORIZATION (DUAL AUTH & TENANT SECURE)
|--------------------------------------------------------------------------
*/
Route::post('/pusher/auth', function (Request $request) {
    $rawChannelName = $request->input('channel_name');
    
    // Strict Regex Validation for Channel Name to prevent injection
    if (!$rawChannelName || !preg_match('/^(private|presence)-[a-zA-Z0-9\.\-_]+$/', $rawChannelName)) {
        return response()->json(['message' => 'Invalid channel name'], 400);
    }

    $channelName = str_replace('private-', '', $rawChannelName);
    $socketId = $request->input('socket_id');

    if (!$socketId) {
        return response()->json(['message' => 'Missing socket ID'], 400);
    }

    // Safer token extraction and normalization
    $token = $request->bearerToken();
    if (!$token && $request->hasHeader('Authorization')) {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
    }

    if (!$token) {
        return response()->json(['message' => 'Missing token'], 403);
    }

    $authorized = false;
    $user = null;
    $session = null;

    // 1. Customer QR Session Validation
    // 🛡️ Fix: Safest DB optimization. Sanctum tokens use '|', QR tokens do not.
    if (str_contains((string)$token, '|') === false) {
        $session = QrSession::where('session_token', $token)->first();
    }

    // Exact match or strict sub-channel
    if ($session && (str_starts_with($channelName, "session.".$session->id.".") || $channelName === "session.".$session->id)) {
        $authorized = true;
    }

    // 2. Waiter Auth (Sanctum) Validation
    if (!$authorized) {
        $user = PersonalAccessToken::findToken($token)?->tokenable;

        // Exact match or strict sub-channel
        if ($user && (str_starts_with($channelName, "restaurant.".$user->restaurant_id.".") || $channelName === "restaurant.".$user->restaurant_id)) {
            $authorized = true;
        }
    }

    if (!$authorized) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    try {
        // Uses optimized singleton if registered, otherwise falls back to new instance
        $pusher = app()->bound('pusher') ? app('pusher') : new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            [
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                'useTLS' => true
            ]
        );
        
        // Safe presence channel check (verifies $user is NOT NULL first)
        if ($user && str_starts_with($rawChannelName, 'presence-restaurant.'.$user->restaurant_id)) {
            $presenceData = ['name' => $user->name, 'staff_id' => $user->staff_id ?? 'Unknown'];
            $authString = $pusher->presence_auth($rawChannelName, $socketId, $user->id, $presenceData);
        } else {
            $authString = method_exists($pusher, 'authorizeChannel') 
                ? $pusher->authorizeChannel($rawChannelName, $socketId)
                : $pusher->socket_auth($rawChannelName, $socketId);
        }

        return response($authString)->header('Content-Type', 'application/json');

    } catch (\Exception $e) {
        // Advanced logging context with token length
        Log::error('Pusher Auth Error', [
            'error' => $e->getMessage(),
            'channel' => $rawChannelName,
            'socket' => $socketId,
            'token_prefix' => substr((string)$token, 0, 8),
            'token_length' => strlen((string)$token)
        ]);
        
        return response()->json([
            'message' => 'Pusher error.', 
            'error' => $e->getMessage()
        ], 500);
    }
});


/*
|--------------------------------------------------------------------------
| WAITER APP ROUTES (Secured)
|--------------------------------------------------------------------------
*/
// Throttled to 3 attempts per minute to stop brute force
Route::post('/waiter/login', [WaiterAppController::class, 'login'])->middleware('throttle:3,1');

// Protected by Sanctum AND the Tenant middleware
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Order Management (Grouped)
    Route::prefix('waiter/orders')->group(function () {
        Route::get('/ready', [WaiterAppController::class, 'getReadyOrders']);
        Route::post('/{id}/serve', [WaiterAppController::class, 'markAsServed']);
        Route::post('/{id}/acknowledge', [WaiterAppController::class, 'acknowledgeOrder']);
    });
    
    // Table Management (Grouped)
    Route::prefix('waiter/tables')->group(function () {
        Route::get('/', [WaiterAppController::class, 'getTables']);
        Route::post('/{id}/status', [WaiterAppController::class, 'updateTableStatus']);
    });
});


/*
|--------------------------------------------------------------------------
| CUSTOMER APP ROUTES (QR System)
|--------------------------------------------------------------------------
*/
// Throttle order placement (30 per min) to prevent spam flooding
Route::post('/orders', [PlaceOrderController::class, 'store'])->middleware('throttle:30,1');
Route::get('/orders/session/{token}', [PlaceOrderController::class, 'getSessionOrders']);

// Throttle "Call Waiter" to prevent spamming the staff (2 per min)
Route::post('/session/call-waiter', [QrSessionController::class, 'callWaiter'])->middleware('throttle:2,1');

// Throttle pending requests query to prevent ID enumeration/spam
Route::get('/table/{tableId}/pending-requests', [QrSessionController::class, 'getPendingRequests'])->middleware('throttle:20,1');

// Throttle Join Requests to prevent spam approvals/rejections (10 per min)
Route::post('/session/{sessionId}/respond', [QrSessionController::class, 'respondToJoin'])->middleware('throttle:10,1');

Route::prefix('qr')->group(function () {
    Route::get('/validate/{restaurant}/{table}/{token}', [QrSessionController::class, 'validateQr']);
    
    // Throttle leaving session
    Route::post('/session/leave', [QrSessionController::class, 'leaveSession'])->middleware('throttle:10,1');
    
    // Missing Rate Limit added to session start to prevent abuse
    Route::post('/session/start/{restaurant}/{table}/{token}', [QrSessionController::class, 'startSession'])->middleware('throttle:10,1');
});

// 🛡️ Fix: Corrected syntax typo at the end of the line
Route::get('/menu/{restaurant}/{table}/{token}', [PublicMenuController::class, 'show'])->name('menu.view');