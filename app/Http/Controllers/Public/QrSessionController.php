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
    public function startSession(
    Request $request,
    Restaurant $restaurant,
    RestaurantTable $table,
    string $token
    ) {
        abort_unless($table->restaurant_id === $restaurant->id, 404);
        abort_unless($table->qr_token === $token, 403);

        $customerName = $request->input('customer_name');

        // Check if primary session exists
        $primary = QrSession::where('restaurant_table_id', $table->id)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();

        if (!$primary) {
            // No active session → create PRIMARY
            return QrSession::create([
                'restaurant_id' => $restaurant->id,
                'restaurant_table_id' => $table->id,
                'customer_name' => $customerName,
                'session_token' => Str::uuid(),
                'is_primary' => true,
                'join_status' => 'active',
                'is_active' => true,
                'expires_at' => now()->addHours(3),
            ]);
        }

        // Primary exists → create pending request
        return QrSession::create([
            'restaurant_id' => $restaurant->id,
            'restaurant_table_id' => $table->id,
            'customer_name' => $customerName,
            'session_token' => Str::uuid(),
            'is_primary' => false,
            'join_status' => 'pending',
            'is_active' => true,
            'expires_at' => now()->addHours(3),
        ]);
    }
public function getPendingRequests($tableId)
{
    return QrSession::where('restaurant_table_id', $tableId)
        ->where('join_status', 'pending')
        ->where('is_active', true)
        ->get();
}
public function respondToJoin(Request $request, $sessionId)
{
    $action = $request->input('action'); // approve or reject

    $session = QrSession::findOrFail($sessionId);

    if ($action === 'approve') {
        $session->update([
            'join_status' => 'approved'
        ]);
    } else {
        $session->update([
            'join_status' => 'rejected',
            'is_active' => false
        ]);
    }

    return response()->json([
        'message' => 'Join request updated'
    ]);
}
/**
     * End the session (User clicked "Leave")
     */
    public function leaveSession(Request $request)
    {
        $request->validate([
            'session_token' => 'required|string'
        ]);

        $session = QrSession::where('session_token', $request->session_token)->first();

        if ($session) {
            // 1. Deactivate this user
            $session->update(['is_active' => false]);

            // 2. OPTIONAL: If the HOST (Primary) leaves, kick everyone else out?
            // This prevents guests from being stuck at a table with no host.
            if ($session->is_primary) {
                QrSession::where('restaurant_table_id', $session->restaurant_table_id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        }

        return response()->json(['message' => 'Session ended']);
    }


}
