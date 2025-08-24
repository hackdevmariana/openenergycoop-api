<?php

namespace App\Filament\Resources\DashboardViewResource\Pages;

use App\Filament\Resources\DashboardViewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDashboardView extends EditRecord
{
    protected static string $resource = DashboardViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
