<?php

namespace App\Filament\Resources\EnergyChallengeResource\Pages;

use App\Filament\Resources\EnergyChallengeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEnergyChallenge extends EditRecord
{
    protected static string $resource = EnergyChallengeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
