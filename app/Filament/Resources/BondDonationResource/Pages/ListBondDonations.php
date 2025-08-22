<?php

namespace App\Filament\Resources\BondDonationResource\Pages;

use App\Filament\Resources\BondDonationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBondDonations extends ListRecords
{
    protected static string $resource = BondDonationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
