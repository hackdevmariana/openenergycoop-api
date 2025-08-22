<?php

namespace App\Filament\Resources\EnergyInstallationResource\Pages;

use App\Filament\Resources\EnergyInstallationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyInstallation extends EditRecord
{
    protected static string $resource = EnergyInstallationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
