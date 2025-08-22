<?php

namespace App\Filament\Resources\EnergyTradingOrderResource\Pages;

use App\Filament\Resources\EnergyTradingOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyTradingOrder extends EditRecord
{
    protected static string $resource = EnergyTradingOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
