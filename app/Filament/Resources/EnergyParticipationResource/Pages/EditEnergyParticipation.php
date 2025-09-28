<?php

namespace App\Filament\Resources\EnergyParticipationResource\Pages;

use App\Filament\Resources\EnergyParticipationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyParticipation extends EditRecord
{
    protected static string $resource = EnergyParticipationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
