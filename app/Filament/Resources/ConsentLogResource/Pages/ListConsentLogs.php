<?php

namespace App\Filament\Resources\ConsentLogResource\Pages;

use App\Filament\Resources\ConsentLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConsentLogs extends ListRecords
{
    protected static string $resource = ConsentLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
