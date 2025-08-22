<?php

namespace App\Filament\Resources\BondDonationResource\Pages;

use App\Filament\Resources\BondDonationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBondDonation extends EditRecord
{
    protected static string $resource = BondDonationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
