<?php

namespace App\Filament\Resources\ProductionProjectResource\Pages;

use App\Filament\Resources\ProductionProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductionProjects extends ListRecords
{
    protected static string $resource = ProductionProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
