<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class WaiterCalled implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $restaurantId;
    public $tableId;
    public $tableNumber;
    public $customerName; // 🔥 NEW
    public $eventId;

    public function __construct($restaurantId, $tableId, $tableNumber, $customerName = 'Guest')
    {
        $this->restaurantId = $restaurantId;
        $this->tableId = $tableId;
        $this->tableNumber = $tableNumber;
        $this->customerName = $customerName; // 🔥 NEW
        $this->eventId = Str::uuid()->toString(); 
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('restaurant.' . $this->restaurantId . '.alerts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'WaiterCalled';
    }

    public function broadcastWith(): array
    {
        return [
            'restaurant_id' => $this->restaurantId,
            'table_id' => $this->tableId,
            'table_number' => $this->tableNumber,
            'customer_name' => $this->customerName, // 🔥 NEW
            'event_id' => $this->eventId,
            'timestamp' => now()->timestamp,
        ];
    }
}