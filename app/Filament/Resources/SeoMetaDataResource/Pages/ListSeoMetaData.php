<?php

namespace App\Filament\Resources\SeoMetaDataResource\Pages;

use App\Filament\Resources\SeoMetaDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSeoMetaData extends ListRecords
{
    protected static string $resource = SeoMetaDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
