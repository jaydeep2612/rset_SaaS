<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitchenQueue extends Model
{
    protected $table = 'kitchen_queue';

    protected $fillable = ['order_id', 'current_status', 'priority'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
