<?php

namespace App\Filament\Resources\UserOrganizationRoleResource\Pages;

use App\Filament\Resources\UserOrganizationRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserOrganizationRoles extends ListRecords
{
    protected static string $resource = UserOrganizationRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
