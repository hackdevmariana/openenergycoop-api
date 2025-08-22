<?php

namespace App\Filament\Resources\EnergyTransferResource\Pages;

use App\Filament\Resources\EnergyTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyTransfer extends EditRecord
{
    protected static string $resource = EnergyTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
