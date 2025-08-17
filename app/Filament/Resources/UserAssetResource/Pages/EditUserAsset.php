<?php

namespace App\Filament\Resources\UserAssetResource\Pages;

use App\Filament\Resources\UserAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserAsset extends EditRecord
{
    protected static string $resource = UserAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
