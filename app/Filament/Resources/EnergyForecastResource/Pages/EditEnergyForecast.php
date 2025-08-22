<?php

namespace App\Filament\Resources\EnergyForecastResource\Pages;

use App\Filament\Resources\EnergyForecastResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyForecast extends EditRecord
{
    protected static string $resource = EnergyForecastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
