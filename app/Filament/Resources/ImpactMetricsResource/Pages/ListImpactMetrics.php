<?php

namespace App\Filament\Resources\ImpactMetricsResource\Pages;

use App\Filament\Resources\ImpactMetricsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImpactMetrics extends ListRecords
{
    protected static string $resource = ImpactMetricsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
