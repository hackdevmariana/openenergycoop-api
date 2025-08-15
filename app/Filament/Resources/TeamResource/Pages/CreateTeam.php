<?php

namespace App\Filament\Resources\TeamResource\Pages;

use App\Filament\Resources\TeamResource;
use App\Models\Team;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTeam extends CreateRecord
{
    protected static string $resource = TeamResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asegurar que se establezca el usuario creador si no está definido
        if (empty($data['created_by_user_id'])) {
            $data['created_by_user_id'] = auth()->id();
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Crear el equipo
        $team = Team::create($data);

        // Agregar automáticamente al creador como admin del equipo
        $team->addMember(auth()->user(), 'admin');

        return $team;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
