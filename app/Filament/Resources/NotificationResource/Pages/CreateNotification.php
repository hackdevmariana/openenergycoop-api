<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification as FilamentNotification;

class CreateNotification extends CreateRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        
        // Marcar como entregada automáticamente
        $record->markAsDelivered();
        
        // Notificar al usuario
        FilamentNotification::make()
            ->title('Notificación Creada')
            ->body("La notificación '{$record->title}' ha sido creada y enviada exitosamente.")
            ->success()
            ->send();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si no se especifica delivered_at, establecerlo automáticamente
        if (!isset($data['delivered_at'])) {
            $data['delivered_at'] = now();
        }
        
        return $data;
    }
}
