<?php

namespace App\Filament\Resources\EnergyCooperativeResource\Pages;

use App\Filament\Resources\EnergyCooperativeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyCooperatives extends ListRecords
{
    protected static string $resource = EnergyCooperativeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
