<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'restaurant.create',
            'restaurant.update',
            'restaurant.view',

            'menu.manage',
            'order.view',
            'order.update_status',
            'payment.collect',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['label' => ucfirst(str_replace('.', ' ', $permission))]
            );
        }
    }
}
