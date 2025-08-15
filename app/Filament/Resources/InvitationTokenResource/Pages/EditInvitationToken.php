<?php

namespace App\Filament\Resources\InvitationTokenResource\Pages;

use App\Filament\Resources\InvitationTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvitationToken extends EditRecord
{
    protected static string $resource = InvitationTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
