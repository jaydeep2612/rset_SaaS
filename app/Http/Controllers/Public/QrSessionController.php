<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Services\Restaurant\QrSessionService;
use Illuminate\Http\Request;
use App\Models\QrSession;
use Illuminate\Support\Str;

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
    public function startSession(Restaurant $restaurant, RestaurantTable $table, ?string $customerName)
{
    
    // Check existing active session
    $existing = QrSession::where('restaurant_table_id', $table->id)
        ->where('is_active', true)
        ->where('expires_at', '>', now())
        ->first();

    if ($existing) {
        return $existing;
    }

    return QrSession::create([
        'restaurant_id' => $restaurant->id,
        'restaurant_table_id' => $table->id,
        'customer_name' => $customerName,
        'session_token' => \Str::uuid(),
        'is_active' => true,
        'expires_at' => now()->addHours(3),
    ]);
}

}
