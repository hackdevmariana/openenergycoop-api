<?php

namespace App\Filament\Resources\TextContentResource\Pages;

use App\Filament\Resources\TextContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTextContent extends EditRecord
{
    protected static string $resource = TextContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
