<?php

namespace App\Filament\Resources\EnergyContractResource\Pages;

use App\Filament\Resources\EnergyContractResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyContract extends EditRecord
{
    protected static string $resource = EnergyContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
