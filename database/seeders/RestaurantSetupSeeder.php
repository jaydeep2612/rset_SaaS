<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RestaurantSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure Roles Exist (Required for foreign keys)
        $roles = [
            1 => ['name' => 'super_admin', 'label' => 'Super admin'],
            2 => ['name' => 'restaurant_admin', 'label' => 'Restaurant admin'],
            3 => ['name' => 'manager', 'label' => 'Manager'],
            4 => ['name' => 'chef', 'label' => 'Chef'],
            5 => ['name' => 'waiter', 'label' => 'Waiter'],
            6 => ['name' => 'customer', 'label' => 'Customer'],
        ];

        foreach ($roles as $id => $roleData) {
            Role::updateOrCreate(['id' => $id], $roleData);
        }

        // 2. Create a Super Admin (Restaurants need a 'created_by' user)
        // $superAdmin = User::updateOrCreate(
        //     ['email' => 'superadmin@system.com'],
        //     [
        //         'name' => 'System Super Admin',
        //         'password' => Hash::make('password'),
        //         'role_id' => 1, // super_admin
        //         'is_super_admin' => true,
        //         'is_active' => true,
        //     ]
        // );

        // 3. Create the Restaurant
        $restaurant = Restaurant::updateOrCreate(
            ['name' => 'RESTAURANT 1'],
            [
                'slug' => Str::slug('RESTAURANT 1'),
                'user_limits' => 20,
                'is_active' => true,
                'created_by' => 1,
            ]
        );

        // 4. Create the Staff Users
        $staffUsers = [
            [
                'name' => 'Admin',
                'email' => 'admin1@user.com',
                'password' => Hash::make('123'),
                'role_id' => 2, // restaurant_admin
                'restaurant_id' => $restaurant->id,
                'is_super_admin' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Manager',
                'email' => 'manager1@user.com',
                'password' => Hash::make('123'),
                'role_id' => 3, // manager
                'restaurant_id' => $restaurant->id,
                'is_super_admin' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Chef',
                'email' => 'chef1@user.com',
                'password' => Hash::make('123'),
                'role_id' => 4, // chef
                'restaurant_id' => $restaurant->id,
                'is_super_admin' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Waiter',
                'email' => 'waiter1@user.com',
                'password' => Hash::make('123'),
                'role_id' => 5, // waiter
                'restaurant_id' => $restaurant->id,
                'is_super_admin' => false,
                'is_active' => true,
            ],
        ];

        foreach ($staffUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']], // Check by email so we don't duplicate
                $userData
            );
        }
        
        $this->command->info('Restaurant 1 and all staff (Admin, Manager, Chef, Waiter) created successfully!');
    }
}