<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\KitchenQueue;
use App\Models\OrderStatusLog;
use App\Models\QrSession;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlaceOrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'table_id' => 'required|exists:restaurant_tables,id',
            'session_token' => 'required|string',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        $restaurant = Restaurant::findOrFail($validated['restaurant_id']);
        $table = RestaurantTable::findOrFail($validated['table_id']);

        // Ensure table belongs to restaurant
        if ($table->restaurant_id !== $restaurant->id) {
            throw ValidationException::withMessages([
                'table_id' => ['Table does not belong to this restaurant.']
            ]);
        }

        // Validate session
        $session = QrSession::where('session_token', $validated['session_token'])
            ->where('restaurant_table_id', $table->id)
            ->where('is_active', true)
            ->first();

        if (!$session || $session->expires_at < now()) {
            throw ValidationException::withMessages([
                'session_token' => ['Session expired or invalid.']
            ]);
        }

        // Validate and prepare items
        $subtotal = 0;
        $preparedItems = [];

        foreach ($validated['items'] as $item) {
            $menuItem = MenuItem::where('id', $item['menu_item_id'])
                ->where('restaurant_id', $restaurant->id)
                ->where('is_available', true)
                ->first();

            if (!$menuItem) {
                throw ValidationException::withMessages([
                    'items' => ['One or more items are invalid or unavailable.']
                ]);
            }

            $totalPrice = $menuItem->price * $item['quantity'];
            $subtotal += $totalPrice;

            $preparedItems[] = [
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name,
                'unit_price' => $menuItem->price,
                'quantity' => $item['quantity'],
                'total_price' => $totalPrice,
                'notes' => $item['notes'] ?? null,
            ];
        }

        $totalAmount = $subtotal; // tax = 0

        DB::transaction(function () use (
            $restaurant,
            $table,
            $session,
            $validated,
            $preparedItems,
            $totalAmount
        ) {

            $order = Order::create([
                'restaurant_id' => $restaurant->id,
                'restaurant_table_id' => $table->id,
                'qr_session_id' => $session->id,
                'customer_name' => $session->customer_name,
                'status' => 'placed',
                'subtotal' => $totalAmount,
                'tax_amount' => 0,
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($preparedItems as $itemData) {
                $order->items()->create($itemData);
            }

            KitchenQueue::create([
                'order_id' => $order->id,
                'current_status' => 'placed',
                'priority' => 0,
            ]);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'placed',
                'changed_by_type' => 'customer',
                'changed_by_id' => null,
            ]);
        });
        return response()->json([
            'message' => 'Order placed successfully.',
            'total_amount' => $totalAmount
        ], 201);
    }
    
        public function getSessionOrders($token)
        {
            $session = QrSession::where('session_token', $token)
                ->where('is_active', true)
                ->first();

            if (!$session) {
                return response()->json(['message' => 'Invalid session'], 404);
            }

            $orders = Order::with('items')
                ->where('qr_session_id', $session->id)
                ->latest()
                ->get();

            return response()->json($orders);
        }

}
