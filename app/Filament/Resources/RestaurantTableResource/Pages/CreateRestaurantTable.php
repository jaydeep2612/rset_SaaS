<?php

namespace App\Filament\Resources\RestaurantTableResource\Pages;

use App\Filament\Resources\RestaurantTableResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use App\Services\Restaurant\QrCodeService;

class CreateRestaurantTable extends CreateRecord
{
    protected static string $resource = RestaurantTableResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $restaurant = auth()->user()->restaurant;

        $data['restaurant_id'] = $restaurant->id;
        $data['qr_token'] = (string) Str::uuid();

        return $data;
    }

    protected function afterCreate(): void
    {
        $restaurant = auth()->user()->restaurant;

        QrCodeService::generateTableQr(
            $restaurant->slug,
            $this->record->table_number,
            $this->record->qr_token
        );
    }
}
