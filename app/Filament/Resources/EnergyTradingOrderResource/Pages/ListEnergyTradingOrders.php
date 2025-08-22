<?php

namespace App\Filament\Resources\EnergyTradingOrderResource\Pages;

use App\Filament\Resources\EnergyTradingOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyTradingOrders extends ListRecords
{
    protected static string $resource = EnergyTradingOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
