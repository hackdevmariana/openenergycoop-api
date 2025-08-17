<?php

namespace App\Filament\Resources\EnergyStorageResource\Pages;

use App\Filament\Resources\EnergyStorageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyStorages extends ListRecords
{
    protected static string $resource = EnergyStorageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
