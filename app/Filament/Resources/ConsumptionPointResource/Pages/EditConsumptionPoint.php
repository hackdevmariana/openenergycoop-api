<?php

namespace App\Filament\Resources\ConsumptionPointResource\Pages;

use App\Filament\Resources\ConsumptionPointResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConsumptionPoint extends EditRecord
{
    protected static string $resource = ConsumptionPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
