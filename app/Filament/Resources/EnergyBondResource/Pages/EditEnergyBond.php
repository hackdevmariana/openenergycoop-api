<?php

namespace App\Filament\Resources\EnergyBondResource\Pages;

use App\Filament\Resources\EnergyBondResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyBond extends EditRecord
{
    protected static string $resource = EnergyBondResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
