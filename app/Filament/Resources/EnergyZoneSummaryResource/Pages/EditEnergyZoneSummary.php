<?php

namespace App\Filament\Resources\EnergyZoneSummaryResource\Pages;

use App\Filament\Resources\EnergyZoneSummaryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyZoneSummary extends EditRecord
{
    protected static string $resource = EnergyZoneSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
