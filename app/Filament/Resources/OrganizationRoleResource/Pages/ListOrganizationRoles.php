<?php

namespace App\Filament\Resources\OrganizationRoleResource\Pages;

use App\Filament\Resources\OrganizationRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationRoles extends ListRecords
{
    protected static string $resource = OrganizationRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
