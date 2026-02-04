<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use LogicException;
use App\Services\Restaurant\UserLimitService;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
     protected function mutateFormDataBeforeCreate(array $data): array
    {
        $authUser = auth()->user();

        // 🟢 Super Admin: no limits
        if ($authUser->isSuperAdmin()) {
            return $data;
        }

        $restaurant = $authUser->restaurant;

        // 🛑 Limit reached
        if ($restaurant->users()->count() >= $restaurant->user_limits) {

            Notification::make()
                ->title('User limit reached')
                ->body("This restaurant has reached its user limit ({$restaurant->user_limits}).")
                ->danger()
                ->send();

            // ⛔ Stop creation + redirect
            $this->redirect(UserResource::getUrl('index'));

            //abort(403); // hard stop
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
