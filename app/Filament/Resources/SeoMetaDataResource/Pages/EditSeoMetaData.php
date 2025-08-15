<?php

namespace App\Filament\Resources\SeoMetaDataResource\Pages;

use App\Filament\Resources\SeoMetaDataResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSeoMetaData extends EditRecord
{
    protected static string $resource = SeoMetaDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
