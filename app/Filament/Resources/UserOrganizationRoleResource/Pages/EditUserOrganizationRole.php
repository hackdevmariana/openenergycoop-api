<?php

namespace App\Filament\Resources\UserOrganizationRoleResource\Pages;

use App\Filament\Resources\UserOrganizationRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserOrganizationRole extends EditRecord
{
    protected static string $resource = UserOrganizationRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
