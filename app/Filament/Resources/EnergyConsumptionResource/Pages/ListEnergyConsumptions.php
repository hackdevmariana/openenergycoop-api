<?php

namespace App\Filament\Resources\EnergyConsumptionResource\Pages;

use App\Filament\Resources\EnergyConsumptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyConsumptions extends ListRecords
{
    protected static string $resource = EnergyConsumptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
