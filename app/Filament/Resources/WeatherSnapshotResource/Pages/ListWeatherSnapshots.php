<?php

namespace App\Filament\Resources\WeatherSnapshotResource\Pages;

use App\Filament\Resources\WeatherSnapshotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWeatherSnapshots extends ListRecords
{
    protected static string $resource = WeatherSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
