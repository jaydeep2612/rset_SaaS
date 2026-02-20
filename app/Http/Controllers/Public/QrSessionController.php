<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use App\Models\QrSession;
use Illuminate\Support\Str;

class QrSessionController extends Controller
{
    public function validateQr(Restaurant $restaurant, RestaurantTable $table, string $token) {
        abort_unless($table->restaurant_id === $restaurant->id, 404);
        abort_unless($table->qr_token === $token, 403);
        abort_unless($table->is_active, 403);
        abort_unless($restaurant->is_active, 403);

        // Check if ANY active primary session exists on this table
        $existingHost = QrSession::where('restaurant_table_id', $table->id)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->where('expires_at', '>', now()) // Ensure host hasn't expired
            ->latest()
            ->first();

        return response()->json([
            'valid' => true,
            'has_active_host' => $existingHost ? true : false,
            'host_name' => $existingHost ? $existingHost->customer_name : null,
            'restaurant' => ['id' => $restaurant->id, 'name' => $restaurant->name, 'logo' => $restaurant->logo_path],
            'table' => ['id' => $table->id, 'number' => $table->table_number],
        ]);
    }

    public function startSession(Request $request, Restaurant $restaurant, RestaurantTable $table, string $token) {
        abort_unless($table->restaurant_id === $restaurant->id, 404);
        abort_unless($table->qr_token === $token, 403);

        $customerName = $request->input('customer_name');
        $mode = $request->input('mode'); // 'new' (Split) or 'join' (Guest)

        $existingHost = QrSession::where('restaurant_table_id', $table->id)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        // 1. Split Table OR No Host exists -> Create NEW Primary
        if (!$existingHost || $mode === 'new') {
            return QrSession::create([
                'restaurant_id' => $restaurant->id,
                'restaurant_table_id' => $table->id,
                'customer_name' => $customerName,
                'session_token' => Str::uuid(),
                'is_primary' => true, 
                'join_status' => 'active',
                'is_active' => true,
                'host_session_id' => null,
                'expires_at' => now()->addHours(3),
            ]);
        }

        // 2. Joining Table -> Create GUEST tied to Host
        return QrSession::create([
            'restaurant_id' => $restaurant->id,
            'restaurant_table_id' => $table->id,
            'customer_name' => $customerName,
            'session_token' => Str::uuid(),
            'is_primary' => false,
            'join_status' => 'pending', // Waiting for host approval
            'is_active' => true,
            'host_session_id' => $existingHost->id, // LINK TO HOST
            'expires_at' => now()->addHours(3),
        ]);
    }

    // 🔥 FIX: Now returns both Pending Requests AND Approved Guests
    public function getPendingRequests($tableId)
    {
        $pending = QrSession::where('restaurant_table_id', $tableId)
            ->where('join_status', 'pending')
            ->where('is_active', true)
            ->get();

        $guests = QrSession::where('restaurant_table_id', $tableId)
            ->where('join_status', 'approved')
            ->where('is_active', true)
            ->whereNotNull('host_session_id')
            ->get();

        return response()->json([
            'pending' => $pending,
            'guests' => $guests
        ]);
    }

    public function respondToJoin(Request $request, $sessionId)
    {
        $action = $request->input('action'); 

        $session = QrSession::findOrFail($sessionId);

        if ($action === 'approve') {
            $session->update(['join_status' => 'approved']);
        } else {
            $session->update([
                'join_status' => 'rejected',
                'is_active' => false
            ]);
        }

        return response()->json(['message' => 'Join request updated']);
    }

    public function leaveSession(Request $request)
    {
        $request->validate(['session_token' => 'required|string']);
        $session = QrSession::where('session_token', $request->session_token)->first();

        if ($session) {
            $session->update(['is_active' => false]);

            // If Host leaves, deactivate their pending/approved guests to prevent ghost sessions
            if ($session->is_primary) {
                QrSession::where('host_session_id', $session->id)->update(['is_active' => false]);
            }
        }
        return response()->json(['message' => 'Session ended']);
    }
}