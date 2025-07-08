<?php

namespace App\Filament\Resources\OrganizationFeatureResource\Pages;

use App\Filament\Resources\OrganizationFeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationFeatures extends ListRecords
{
    protected static string $resource = OrganizationFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
