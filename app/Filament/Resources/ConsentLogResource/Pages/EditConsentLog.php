<?php

namespace App\Filament\Resources\ConsentLogResource\Pages;

use App\Filament\Resources\ConsentLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConsentLog extends EditRecord
{
    protected static string $resource = ConsentLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
