<?php

namespace App\Filament\Resources\CustomerProfileContactInfoResource\Pages;

use App\Filament\Resources\CustomerProfileContactInfoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerProfileContactInfo extends EditRecord
{
    protected static string $resource = CustomerProfileContactInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
