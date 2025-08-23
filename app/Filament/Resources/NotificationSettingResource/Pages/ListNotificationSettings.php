<?php

namespace App\Filament\Resources\NotificationSettingResource\Pages;

use App\Filament\Resources\NotificationSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListNotificationSettings extends ListRecords
{
    protected static string $resource = NotificationSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_defaults')
                ->label('Crear Configuraciones por Defecto')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->action(function () {
                    $users = \App\Models\User::all();
                    $created = 0;
                    
                    foreach ($users as $user) {
                        $created += \App\Models\NotificationSetting::createDefaultSettings($user->id);
                    }
                    
                    $this->notify('success', "Se han creado {$created} configuraciones por defecto para todos los usuarios.");
                })
                ->requiresConfirmation()
                ->modalHeading('Confirmar Creación')
                ->modalDescription('¿Estás seguro de que quieres crear configuraciones por defecto para todos los usuarios?')
                ->modalSubmitActionLabel('Sí, Crear'),

            Action::make('enable_all')
                ->label('Habilitar Todas')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    $updated = \App\Models\NotificationSetting::query()->update(['enabled' => true]);
                    $this->notify('success', "Se han habilitado {$updated} configuraciones.");
                })
                ->requiresConfirmation()
                ->modalHeading('Confirmar Habilitación')
                ->modalDescription('¿Estás seguro de que quieres habilitar todas las configuraciones?')
                ->modalSubmitActionLabel('Sí, Habilitar Todas'),

            Actions\CreateAction::make()
                ->label('Nueva Configuración'),
        ];
    }
}
