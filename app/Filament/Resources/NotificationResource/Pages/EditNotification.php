<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification as FilamentNotification;

class EditNotification extends EditRecord
{
    protected static string $resource = NotificationResource::class;

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
            ->title('Notificación Actualizada')
            ->body("La notificación '{$record->title}' ha sido actualizada exitosamente.")
            ->success()
            ->send();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si se marca como leída pero no tiene read_at, establecerlo
        if (isset($data['is_read']) && $data['is_read'] && !isset($data['read_at'])) {
            $data['read_at'] = now();
        }
        
        // Si se desmarca como leída, limpiar read_at
        if (isset($data['is_read']) && !$data['is_read']) {
            $data['read_at'] = null;
        }
        
        return $data;
    }
}
