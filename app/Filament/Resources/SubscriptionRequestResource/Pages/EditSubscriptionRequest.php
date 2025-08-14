<?php

namespace App\Filament\Resources\SubscriptionRequestResource\Pages;

use App\Filament\Resources\SubscriptionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubscriptionRequest extends EditRecord
{
    protected static string $resource = SubscriptionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
