<?php

namespace App\Filament\Resources\PreSaleOfferResource\Pages;

use App\Filament\Resources\PreSaleOfferResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPreSaleOffers extends ListRecords
{
    protected static string $resource = PreSaleOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
