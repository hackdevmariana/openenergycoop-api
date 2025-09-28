<?php

namespace App\Filament\Resources\EnergyParticipationResource\Pages;

use App\Filament\Resources\EnergyParticipationResource;
use App\Models\EnergyParticipation;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEnergyParticipations extends ListRecords
{
    protected static string $resource = EnergyParticipationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todas')
                ->badge(EnergyParticipation::count())
                ->badgeColor('gray'),

            'active' => Tab::make('Activas')
                ->badge(EnergyParticipation::active()->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->active()),

            'suspended' => Tab::make('Suspendidas')
                ->badge(EnergyParticipation::suspended()->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->suspended()),

            'cancelled' => Tab::make('Canceladas')
                ->badge(EnergyParticipation::cancelled()->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->cancelled()),

            'completed' => Tab::make('Completadas')
                ->badge(EnergyParticipation::completed()->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->completed()),

            'monthly' => Tab::make('Mensuales')
                ->badge(EnergyParticipation::monthly()->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->monthly()),

            'one_time' => Tab::make('Aportaciones Únicas')
                ->badge(EnergyParticipation::oneTime()->count())
                ->badgeColor('secondary')
                ->modifyQueryUsing(fn (Builder $query) => $query->oneTime()),

            'expiring_soon' => Tab::make('Expiran Pronto')
                ->badge(EnergyParticipation::expiringSoon()->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->expiringSoon()),

            'expired' => Tab::make('Expiradas')
                ->badge(EnergyParticipation::expired()->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->expired()),

            'by_plan' => Tab::make('Por Plan')
                ->badge(EnergyParticipation::count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('plan_code')),
        ];
    }
}