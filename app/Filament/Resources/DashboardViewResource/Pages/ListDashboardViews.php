<?php

namespace App\Filament\Resources\DashboardViewResource\Pages;

use App\Filament\Resources\DashboardViewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDashboardViews extends ListRecords
{
    protected static string $resource = DashboardViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
