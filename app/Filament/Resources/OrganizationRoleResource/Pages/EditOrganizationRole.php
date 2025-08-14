<?php

namespace App\Filament\Resources\OrganizationRoleResource\Pages;

use App\Filament\Resources\OrganizationRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrganizationRole extends EditRecord
{
    protected static string $resource = OrganizationRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
