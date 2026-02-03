<?php

namespace App\Filament\Resources\KitchenQueueResource\Pages;

use App\Filament\Resources\KitchenQueueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKitchenQueues extends ListRecords
{
    protected static string $resource = KitchenQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
