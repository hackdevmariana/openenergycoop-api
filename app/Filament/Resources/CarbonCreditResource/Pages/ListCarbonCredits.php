<?php

namespace App\Filament\Resources\CarbonCreditResource\Pages;

use App\Filament\Resources\CarbonCreditResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCarbonCredits extends ListRecords
{
    protected static string $resource = CarbonCreditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
