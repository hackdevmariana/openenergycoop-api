<?php

namespace App\Filament\Resources\EnergyPoolResource\Pages;

use App\Filament\Resources\EnergyPoolResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyPools extends ListRecords
{
    protected static string $resource = EnergyPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
