<?php

namespace App\Filament\Resources\EnergyForecastResource\Pages;

use App\Filament\Resources\EnergyForecastResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyForecasts extends ListRecords
{
    protected static string $resource = EnergyForecastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
