<?php

namespace App\Filament\Resources\EnergySourceResource\Pages;

use App\Filament\Resources\EnergySourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergySources extends ListRecords
{
    protected static string $resource = EnergySourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
