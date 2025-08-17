<?php

namespace App\Filament\Resources\EnergyProductionResource\Pages;

use App\Filament\Resources\EnergyProductionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyProduction extends EditRecord
{
    protected static string $resource = EnergyProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
