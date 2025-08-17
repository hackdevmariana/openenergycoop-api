<?php

namespace App\Filament\Resources\EnergyProductionResource\Pages;

use App\Filament\Resources\EnergyProductionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyProductions extends ListRecords
{
    protected static string $resource = EnergyProductionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
