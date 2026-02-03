<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'super_admin'],
            ['label' => 'Super Administrator']
        );

        Role::firstOrCreate(
            ['name' => 'restaurant_admin'],
            ['label' => 'Restaurant Admin']
        );

        Role::firstOrCreate(
            ['name' => 'staff'],
            ['label' => 'Staff']
        );

        Role::firstOrCreate(
            ['name' => 'chef'],
            ['label' => 'Kitchen Staff']
        );
    }
}
