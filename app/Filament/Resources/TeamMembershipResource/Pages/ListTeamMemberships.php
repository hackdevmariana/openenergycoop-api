<?php

namespace App\Filament\Resources\TeamMembershipResource\Pages;

use App\Filament\Resources\TeamMembershipResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeamMemberships extends ListRecords
{
    protected static string $resource = TeamMembershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
