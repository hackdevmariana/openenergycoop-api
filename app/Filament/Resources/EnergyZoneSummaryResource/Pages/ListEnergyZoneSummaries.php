<?php

namespace App\Filament\Resources\EnergyZoneSummaryResource\Pages;

use App\Filament\Resources\EnergyZoneSummaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyZoneSummaries extends ListRecords
{
    protected static string $resource = EnergyZoneSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
