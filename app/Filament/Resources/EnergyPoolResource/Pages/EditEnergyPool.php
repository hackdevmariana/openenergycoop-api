<?php

namespace App\Filament\Resources\EnergyPoolResource\Pages;

use App\Filament\Resources\EnergyPoolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyPool extends EditRecord
{
    protected static string $resource = EnergyPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
