<?php

namespace App\Filament\Resources\EnergyContractResource\Pages;

use App\Filament\Resources\EnergyContractResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyContracts extends ListRecords
{
    protected static string $resource = EnergyContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
