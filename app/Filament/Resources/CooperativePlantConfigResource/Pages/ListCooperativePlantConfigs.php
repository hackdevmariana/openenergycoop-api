<?php

namespace App\Filament\Resources\CooperativePlantConfigResource\Pages;

use App\Filament\Resources\CooperativePlantConfigResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCooperativePlantConfigs extends ListRecords
{
    protected static string $resource = CooperativePlantConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
