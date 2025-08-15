<?php

namespace App\Filament\Resources\TeamMembershipResource\Pages;

use App\Filament\Resources\TeamMembershipResource;
use App\Models\TeamMembership;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class CreateTeamMembership extends CreateRecord
{
    protected static string $resource = TeamMembershipResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Verificar si ya existe una membresía activa
        $existingMembership = TeamMembership::where('team_id', $data['team_id'])
            ->where('user_id', $data['user_id'])
            ->whereNull('left_at')
            ->first();

        if ($existingMembership) {
            Notification::make()
                ->title('Error')
                ->body('El usuario ya es miembro activo de este equipo.')
                ->danger()
                ->send();
                
            $this->halt();
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Verificar si hay una membresía inactiva que se puede reactivar
        $inactiveMembership = TeamMembership::where('team_id', $data['team_id'])
            ->where('user_id', $data['user_id'])
            ->whereNotNull('left_at')
            ->latest('left_at')
            ->first();

        if ($inactiveMembership) {
            // Reactivar la membresía existente
            $inactiveMembership->update([
                'role' => $data['role'],
                'joined_at' => $data['joined_at'],
                'left_at' => null,
            ]);

            Notification::make()
                ->title('Membresía Reactivada')
                ->body('Se reactivó una membresía anterior del usuario.')
                ->success()
                ->send();

            return $inactiveMembership;
        }

        // Crear nueva membresía
        return TeamMembership::create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}