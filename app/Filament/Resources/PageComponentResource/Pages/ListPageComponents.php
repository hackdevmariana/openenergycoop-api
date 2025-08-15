<?php

namespace App\Filament\Resources\PageComponentResource\Pages;

use App\Filament\Resources\PageComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPageComponents extends ListRecords
{
    protected static string $resource = PageComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
