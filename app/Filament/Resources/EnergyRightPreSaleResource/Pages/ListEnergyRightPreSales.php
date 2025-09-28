<?php

namespace App\Filament\Resources\EnergyRightPreSaleResource\Pages;

use App\Filament\Resources\EnergyRightPreSaleResource;
use App\Models\EnergyRightPreSale;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEnergyRightPreSales extends ListRecords
{
    protected static string $resource = EnergyRightPreSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nueva Preventa')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todas')
                ->badge(EnergyRightPreSale::count())
                ->badgeColor('gray'),

            'pending' => Tab::make('Pendientes')
                ->badge(EnergyRightPreSale::where('status', 'pending')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),

            'confirmed' => Tab::make('Confirmadas')
                ->badge(EnergyRightPreSale::where('status', 'confirmed')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'confirmed')),

            'cancelled' => Tab::make('Canceladas')
                ->badge(EnergyRightPreSale::where('status', 'cancelled')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),

            'active' => Tab::make('Activas')
                ->badge(EnergyRightPreSale::active()->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->active()),

            'expired' => Tab::make('Expiradas')
                ->badge(EnergyRightPreSale::expired()->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->expired()),

            'expiring_soon' => Tab::make('Expiran Pronto')
                ->badge(EnergyRightPreSale::expiringSoon()->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->expiringSoon()),

            'with_installation' => Tab::make('Con Instalación')
                ->badge(EnergyRightPreSale::withInstallation()->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->withInstallation()),

            'by_zone' => Tab::make('Por Zona')
                ->badge(EnergyRightPreSale::withoutInstallation()->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutInstallation()),
        ];
    }
}
