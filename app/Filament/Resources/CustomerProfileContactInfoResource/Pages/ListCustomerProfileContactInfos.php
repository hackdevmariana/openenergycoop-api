<?php

namespace App\Filament\Resources\CustomerProfileContactInfoResource\Pages;

use App\Filament\Resources\CustomerProfileContactInfoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomerProfileContactInfos extends ListRecords
{
    protected static string $resource = CustomerProfileContactInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
