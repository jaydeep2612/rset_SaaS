<?php

namespace App\Filament\Resources\TableBillingResource\Pages;

use App\Filament\Resources\TableBillingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTableBillings extends ListRecords
{
    protected static string $resource = TableBillingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
