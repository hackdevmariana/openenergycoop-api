<?php

namespace App\Filament\Resources\EnergyReportResource\Pages;

use App\Filament\Resources\EnergyReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyReports extends ListRecords
{
    protected static string $resource = EnergyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
