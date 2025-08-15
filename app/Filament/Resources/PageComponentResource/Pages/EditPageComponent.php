<?php

namespace App\Filament\Resources\PageComponentResource\Pages;

use App\Filament\Resources\PageComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPageComponent extends EditRecord
{
    protected static string $resource = PageComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
