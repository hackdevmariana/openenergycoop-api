<?php

namespace App\Filament\Resources\NotificationSettingResource\Pages;

use App\Filament\Resources\NotificationSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification as FilamentNotification;

class EditNotificationSetting extends EditRecord
{
    protected static string $resource = NotificationSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Eliminar'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        
        // Notificar al usuario
        FilamentNotification::make()
            ->title('Configuración Actualizada')
            ->body("La configuración de notificación para {$record->user->name} ha sido actualizada exitosamente.")
            ->success()
            ->send();
    }
}
