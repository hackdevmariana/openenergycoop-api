<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_all_read')
                ->label('Marcar Todas como Leídas')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    // Marcar todas las notificaciones no leídas como leídas
                    $updated = \App\Models\Notification::unread()->update(['read_at' => now()]);
                    Notification::make()
                        ->title('Éxito')
                        ->body("Se han marcado {$updated} notificaciones como leídas.")
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Confirmar Acción')
                ->modalDescription('¿Estás seguro de que quieres marcar todas las notificaciones como leídas?')
                ->modalSubmitActionLabel('Sí, Marcar Todas'),

            Action::make('cleanup_old')
                ->label('Limpiar Antiguas')
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->action(function () {
                    $deleted = \App\Models\Notification::cleanupOld(30);
                    Notification::make()
                        ->title('Limpieza Completada')
                        ->body("Se han eliminado {$deleted} notificaciones antiguas.")
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Confirmar Limpieza')
                ->modalDescription('¿Estás seguro de que quieres eliminar las notificaciones de más de 30 días?')
                ->modalSubmitActionLabel('Sí, Limpiar'),

            Actions\CreateAction::make()
                ->label('Nueva Notificación'),
        ];
    }
}
