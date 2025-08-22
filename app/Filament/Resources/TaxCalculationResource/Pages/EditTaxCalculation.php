<?php

namespace App\Filament\Resources\TaxCalculationResource\Pages;

use App\Filament\Resources\TaxCalculationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaxCalculation extends EditRecord
{
    protected static string $resource = TaxCalculationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
