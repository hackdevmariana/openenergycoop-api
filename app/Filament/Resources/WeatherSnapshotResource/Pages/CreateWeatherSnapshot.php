<?php

namespace App\Filament\Resources\WeatherSnapshotResource\Pages;

use App\Filament\Resources\WeatherSnapshotResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWeatherSnapshot extends CreateRecord
{
    protected static string $resource = WeatherSnapshotResource::class;
}
