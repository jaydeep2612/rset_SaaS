<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Support\Facades\Storage;


class Restaurant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'user_limits',
        'is_active',
        'created_by',
    ];

    protected static function booted()
    {
        static::creating(function ($restaurant) {
            $restaurant->created_by = auth()->id();
        });
        
        static::deleted(function (Restaurant $restaurant) {
            if ($restaurant->slug) {
                Storage::disk('public')->deleteDirectory(
                    'restaurants/' . $restaurant->slug
                );
            }
        });
    }
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}

