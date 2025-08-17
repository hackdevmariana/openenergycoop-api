<?php

namespace App\Filament\Resources\EnergyStorageResource\Pages;

use App\Filament\Resources\EnergyStorageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyStorage extends EditRecord
{
    protected static string $resource = EnergyStorageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
