<?php

namespace App\Filament\Resources\UserChallengeProgressResource\Pages;

use App\Filament\Resources\UserChallengeProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserChallengeProgress extends ListRecords
{
    protected static string $resource = UserChallengeProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
