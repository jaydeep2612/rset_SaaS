<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Services\Restaurant\QrSessionService;
use Illuminate\Http\Request;

class QrSessionController extends Controller
{
    public function validateQr(
        Restaurant $restaurant,
        RestaurantTable $table,
        string $token
    ) {
        abort_unless($table->restaurant_id === $restaurant->id, 404);
        abort_unless($table->qr_token === $token, 403);
        abort_unless($table->is_active, 403);
        abort_unless($restaurant->is_active, 403);

        return response()->json([
            'valid' => true,
            'restaurant' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'logo' => $restaurant->logo_path,
            ],
            'table' => [
                'id' => $table->id,
                'number' => $table->table_number,
            ],
        ]);
    }

    /**
     * Start / Resume session
     */
    public function startSession(
        Request $request,
        Restaurant $restaurant,
        RestaurantTable $table,
        string $token,
        QrSessionService $service
    ) {
        $request->validate([
            'customer_name' => ['nullable', 'string', 'max:100'],
        ]);

        abort_unless($table->restaurant_id === $restaurant->id, 404);
        abort_unless($table->qr_token === $token, 403);

        $session = $service->startSession(
            $restaurant,
            $table,
            $request->customer_name
        );

        return response()->json([
            'session_token' => $session->session_token,
            'expires_at' => $session->expires_at,
        ]);
    }
}
