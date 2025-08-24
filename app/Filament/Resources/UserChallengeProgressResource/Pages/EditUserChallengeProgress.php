<?php

namespace App\Filament\Resources\UserChallengeProgressResource\Pages;

use App\Filament\Resources\UserChallengeProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserChallengeProgress extends EditRecord
{
    protected static string $resource = UserChallengeProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
