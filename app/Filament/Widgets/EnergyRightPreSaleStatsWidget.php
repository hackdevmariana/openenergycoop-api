<?php

namespace App\Filament\Widgets;

use App\Models\EnergyRightPreSale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EnergyRightPreSaleStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $summary = EnergyRightPreSale::getSystemSummary();

        return [
            Stat::make('Total de Preventas', $summary['total_presales'])
                ->description('Preventas de derechos energéticos')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make('Pendientes', $summary['by_status']['pending'])
                ->description('Esperando confirmación')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Confirmadas', $summary['by_status']['confirmed'])
                ->description('Preventas confirmadas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Canceladas', $summary['by_status']['cancelled'])
                ->description('Preventas canceladas')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Activas', $summary['active_presales'])
                ->description('Preventas activas')
                ->descriptionIcon('heroicon-m-play')
                ->color('info'),

            Stat::make('Expiradas', $summary['expired_presales'])
                ->description('Preventas expiradas')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Expiran Pronto', $summary['expiring_soon'])
                ->description('Expiran en 30 días')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Con Instalación', $summary['with_installation'])
                ->description('Asociadas a instalación')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),

            Stat::make('Por Zona', $summary['without_installation'])
                ->description('Asociadas a zona')
                ->descriptionIcon('heroicon-m-map')
                ->color('gray'),

            Stat::make('Total kWh Reservados', number_format($summary['total_kwh_reserved'], 0) . ' kWh/mes')
                ->description('Energía reservada mensualmente')
                ->descriptionIcon('heroicon-m-battery-100')
                ->color('success'),

            Stat::make('Valor Total Reservado', number_format($summary['total_value_reserved'], 2) . ' €/mes')
                ->description('Valor económico reservado')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('info'),

            Stat::make('Precio Promedio', number_format($summary['average_price_per_kwh'], 4) . ' €/kWh')
                ->description('Precio promedio por kWh')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
        ];
    }
}
