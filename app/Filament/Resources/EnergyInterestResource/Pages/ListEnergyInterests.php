<?php

namespace App\Filament\Resources\EnergyInterestResource\Pages;

use App\Filament\Resources\EnergyInterestResource;
use App\Models\EnergyInterest;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEnergyInterests extends ListRecords
{
    protected static string $resource = EnergyInterestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nuevo Interés')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todos')
                ->badge(EnergyInterest::count())
                ->badgeColor('gray'),

            'pending' => Tab::make('Pendientes')
                ->badge(EnergyInterest::where('status', 'pending')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),

            'approved' => Tab::make('Aprobados')
                ->badge(EnergyInterest::where('status', 'approved')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved')),

            'active' => Tab::make('Activos')
                ->badge(EnergyInterest::where('status', 'active')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active')),

            'rejected' => Tab::make('Rechazados')
                ->badge(EnergyInterest::where('status', 'rejected')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected')),

            'consumers' => Tab::make('Consumidores')
                ->badge(EnergyInterest::where('type', 'consumer')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'consumer')),

            'producers' => Tab::make('Productores')
                ->badge(EnergyInterest::where('type', 'producer')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'producer')),

            'mixed' => Tab::make('Mixtos')
                ->badge(EnergyInterest::where('type', 'mixed')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'mixed')),

            'with_user' => Tab::make('Con Usuario')
                ->badge(EnergyInterest::whereNotNull('user_id')->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('user_id')),

            'without_user' => Tab::make('Sin Usuario')
                ->badge(EnergyInterest::whereNull('user_id')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('user_id')),
        ];
    }
}
