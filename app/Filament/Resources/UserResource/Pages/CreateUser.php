<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use LogicException;
use App\Services\Restaurant\UserLimitService;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
     protected function mutateFormDataBeforeCreate(array $data): array
    {
        $actor = auth()->user();

        // 🧠 FORCE restaurant for non-super users
        if (! $actor->isSuperAdmin()) {
            $data['restaurant_id'] = $actor->restaurant_id;
        }

        // 🛑 HARD GUARD (this is what you asked about)
        if (! $actor->isSuperAdmin() && empty($data['restaurant_id'])) {
            throw new LogicException(
                'Non-super admin users must belong to a restaurant.'
            );
        }

        return $data;
    }

    protected function beforeCreate(): void
    {
        $actor = auth()->user();

        // 🚦 User-limit enforcement
        if (! $actor->isSuperAdmin()) {
            app(UserLimitService::class)
                ->enforce($actor->restaurant);
        }
    }
}
