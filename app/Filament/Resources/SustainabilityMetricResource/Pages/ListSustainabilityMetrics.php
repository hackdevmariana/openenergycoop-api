<?php

namespace App\Filament\Resources\SustainabilityMetricResource\Pages;

use App\Filament\Resources\SustainabilityMetricResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSustainabilityMetrics extends ListRecords
{
    protected static string $resource = SustainabilityMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
