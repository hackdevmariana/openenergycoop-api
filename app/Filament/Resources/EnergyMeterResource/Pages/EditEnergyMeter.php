<?php

namespace App\Filament\Resources\EnergyMeterResource\Pages;

use App\Filament\Resources\EnergyMeterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyMeter extends EditRecord
{
    protected static string $resource = EnergyMeterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
