<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderStatusLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class WaiterAppController extends Controller
{
    // 1. Waiter Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
                    ->where('is_active', true)
                    ->with('role')
                    ->first();

        // Ensure user exists, password is correct, and role is waiter (or manager)
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }

        if (! in_array($user->role->name, ['waiter', 'manager', 'restaurant_admin'])) {
            throw ValidationException::withMessages(['email' => ['Unauthorized access.']]);
        }

        // Generate Sanctum Token
        $token = $user->createToken('waiter-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
                'restaurant_id' => $user->restaurant_id,
            ]
        ]);
    }

    // 2. Get Ready Orders
    public function getReadyOrders(Request $request)
    {
        $user = $request->user();

        $orders = Order::with(['items', 'table', 'session'])
            ->where('restaurant_id', $user->restaurant_id)
            ->where('status', 'ready') // Only fetch orders ready to be served
            ->orderBy('updated_at', 'asc') // Oldest ready first
            ->get()
            ->map(function ($order) {
                // Calculate total items
                $totalItems = $order->items->sum('quantity');
                
                // Extract unique notes for the kitchen note section
                $notes = $order->items->whereNotNull('notes')->pluck('notes')->filter()->implode(', ');

                return [
                    'id' => $order->id,
                    'table_number' => $order->table ? $order->table->table_number : 'Takeaway',
                    'customer_name' => $order->customer_name ?? 'Guest',
                    'total_items' => $totalItems,
                    'notes' => $notes ?: null,
                    'wait_time' => $order->updated_at->diffInMinutes(now()), // Minutes since it was marked ready
                    'ready_since' => $order->updated_at->format('H:i'),
                ];
            });

        return response()->json($orders);
    }

    // 3. Mark Order as Served
    public function markAsServed(Request $request, $id)
    {
        $user = $request->user();

        $order = Order::where('restaurant_id', $user->restaurant_id)
            ->where('id', $id)
            ->firstOrFail();

        if ($order->status !== 'ready') {
            return response()->json(['message' => 'Order is not ready.'], 400);
        }

        $order->update(['status' => 'served']);

        // Log the status change
        OrderStatusLog::create([
            'order_id' => $order->id,
            'from_status' => 'ready',
            'to_status' => 'served',
            'changed_by' => $user->id, // Who served it
        ]);

        return response()->json(['message' => 'Order marked as served successfully.']);
    }
}