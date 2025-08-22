<?php

namespace App\Filament\Resources\EnergyTransferResource\Pages;

use App\Filament\Resources\EnergyTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEnergyTransfers extends ListRecords
{
    protected static string $resource = EnergyTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
