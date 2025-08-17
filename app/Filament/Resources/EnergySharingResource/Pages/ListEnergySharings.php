<?php

namespace App\Filament\Resources\EnergySharingResource\Pages;

use App\Filament\Resources\EnergySharingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergySharings extends ListRecords
{
    protected static string $resource = EnergySharingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
