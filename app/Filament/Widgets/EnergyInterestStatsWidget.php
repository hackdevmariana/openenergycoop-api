<?php

namespace App\Filament\Widgets;

use App\Models\EnergyInterest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EnergyInterestStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $summary = EnergyInterest::getSystemSummary();

        return [
            Stat::make('Total de Intereses', $summary['total_interests'])
                ->description('Intereses energéticos registrados')
                ->descriptionIcon('heroicon-m-heart')
                ->color('primary'),

            Stat::make('Consumidores', $summary['by_type']['consumer'])
                ->description('Intereses de consumo')
                ->descriptionIcon('heroicon-m-home')
                ->color('success'),

            Stat::make('Productores', $summary['by_type']['producer'])
                ->description('Intereses de producción')
                ->descriptionIcon('heroicon-m-sun')
                ->color('warning'),

            Stat::make('Mixtos', $summary['by_type']['mixed'])
                ->description('Intereses mixtos')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('info'),

            Stat::make('Pendientes', $summary['by_status']['pending'])
                ->description('Esperando aprobación')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Aprobados', $summary['by_status']['approved'])
                ->description('Intereses aprobados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Activos', $summary['by_status']['active'])
                ->description('Intereses activos')
                ->descriptionIcon('heroicon-m-play')
                ->color('info'),

            Stat::make('Con Usuario', $summary['with_user'])
                ->description('Usuarios registrados')
                ->descriptionIcon('heroicon-m-user')
                ->color('primary'),

            Stat::make('Sin Usuario', $summary['without_user'])
                ->description('Contactos externos')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('gray'),

            Stat::make('Producción Total', number_format($summary['total_production_interest'], 0) . ' kWh/día')
                ->description('Energía ofrecida')
                ->descriptionIcon('heroicon-m-battery-100')
                ->color('success'),

            Stat::make('Demanda Total', number_format($summary['total_consumption_interest'], 0) . ' kWh/día')
                ->description('Energía solicitada')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('warning'),
        ];
    }
}
