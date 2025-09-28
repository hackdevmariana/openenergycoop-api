<?php

namespace App\Filament\Resources\EnergyRightPreSaleResource\Pages;

use App\Filament\Resources\EnergyRightPreSaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyRightPreSale extends EditRecord
{
    protected static string $resource = EnergyRightPreSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
