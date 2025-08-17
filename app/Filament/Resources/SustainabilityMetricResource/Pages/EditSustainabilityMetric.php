<?php

namespace App\Filament\Resources\SustainabilityMetricResource\Pages;

use App\Filament\Resources\SustainabilityMetricResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSustainabilityMetric extends EditRecord
{
    protected static string $resource = SustainabilityMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
