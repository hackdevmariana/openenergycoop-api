<?php

namespace App\Filament\Resources\PlantGroupResource\Pages;

use App\Filament\Resources\PlantGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlantGroups extends ListRecords
{
    protected static string $resource = PlantGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
