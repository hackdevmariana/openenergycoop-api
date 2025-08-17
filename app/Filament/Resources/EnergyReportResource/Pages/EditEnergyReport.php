<?php

namespace App\Filament\Resources\EnergyReportResource\Pages;

use App\Filament\Resources\EnergyReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyReport extends EditRecord
{
    protected static string $resource = EnergyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
