<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QrSession extends Model
{
    protected $fillable = [
        'restaurant_id',
        'restaurant_table_id',
        'session_token',
        'customer_name',
        'is_primary',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
