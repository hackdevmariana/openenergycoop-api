<?php

namespace App\Filament\Resources\EnergySharingResource\Pages;

use App\Filament\Resources\EnergySharingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergySharing extends EditRecord
{
    protected static string $resource = EnergySharingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
