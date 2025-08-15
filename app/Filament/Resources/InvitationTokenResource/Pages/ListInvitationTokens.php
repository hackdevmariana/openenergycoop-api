<?php

namespace App\Filament\Resources\InvitationTokenResource\Pages;

use App\Filament\Resources\InvitationTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvitationTokens extends ListRecords
{
    protected static string $resource = InvitationTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
