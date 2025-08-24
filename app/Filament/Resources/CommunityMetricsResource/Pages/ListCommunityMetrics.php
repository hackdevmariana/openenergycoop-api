<?php

namespace App\Filament\Resources\CommunityMetricsResource\Pages;

use App\Filament\Resources\CommunityMetricsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCommunityMetrics extends ListRecords
{
    protected static string $resource = CommunityMetricsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
