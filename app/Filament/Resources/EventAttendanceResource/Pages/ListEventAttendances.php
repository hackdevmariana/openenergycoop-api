<?php

namespace App\Filament\Resources\EventAttendanceResource\Pages;

use App\Filament\Resources\EventAttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEventAttendances extends ListRecords
{
    protected static string $resource = EventAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
