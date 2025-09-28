<?php

namespace App\Filament\Resources\EnergyInterestResource\Pages;

use App\Filament\Resources\EnergyInterestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyInterest extends EditRecord
{
    protected static string $resource = EnergyInterestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
