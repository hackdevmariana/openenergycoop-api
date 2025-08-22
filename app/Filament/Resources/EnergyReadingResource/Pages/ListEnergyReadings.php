<?php

namespace App\Filament\Resources\EnergyReadingResource\Pages;

use App\Filament\Resources\EnergyReadingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyReadings extends ListRecords
{
    protected static string $resource = EnergyReadingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
