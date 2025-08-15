<?php

namespace App\Filament\Resources\TeamChallengeProgressResource\Pages;

use App\Filament\Resources\TeamChallengeProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeamChallengeProgress extends ListRecords
{
    protected static string $resource = TeamChallengeProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
