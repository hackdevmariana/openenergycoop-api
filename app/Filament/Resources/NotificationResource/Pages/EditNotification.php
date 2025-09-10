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
        
        // Determinar el mensaje basado en el estado de lectura
        $isRead = $record->isRead();
        $statusText = $isRead ? 'marcada como leída' : 'marcada como no leída';
        
        // Notificar al usuario
        FilamentNotification::make()
            ->title('Notificación Actualizada')
            ->body("La notificación '{$record->title}' ha sido {$statusText}. El badge de navegación se actualizará al refrescar la página.")
            ->success()
            ->send();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Asegurar que is_read se hidrate correctamente
        $data['is_read'] = $this->record->isRead();
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Sincronizar el estado de lectura con read_at
        if (isset($data['is_read'])) {
            if ($data['is_read']) {
                // Si se marca como leída, establecer read_at si no existe
                if (!isset($data['read_at']) || is_null($data['read_at'])) {
                    $data['read_at'] = now();
                }
            } else {
                // Si se desmarca como leída, limpiar read_at
                $data['read_at'] = null;
            }
        }
        
        // Remover is_read del array ya que no es un campo de la base de datos
        unset($data['is_read']);
        
        return $data;
    }
}
