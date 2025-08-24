<?php

namespace App\Filament\Resources\PlantGroupResource\Pages;

use App\Filament\Resources\PlantGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlantGroup extends EditRecord
{
    protected static string $resource = PlantGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
