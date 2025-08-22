<?php

namespace App\Filament\Resources\EnergyMeterResource\Pages;

use App\Filament\Resources\EnergyMeterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyMeters extends ListRecords
{
    protected static string $resource = EnergyMeterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
