<?php

namespace App\Filament\Resources\SubscriptionRequestResource\Pages;

use App\Filament\Resources\SubscriptionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptionRequests extends ListRecords
{
    protected static string $resource = SubscriptionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
