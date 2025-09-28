<?php

namespace App\Filament\Widgets;

use App\Models\EnergyZoneSummary;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EnergyZoneStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $summary = EnergyZoneSummary::getSystemSummary();
        
        return [
            Stat::make('Total de Zonas', $summary['total_zones'])
                ->description('Zonas energéticas activas')
                ->descriptionIcon('heroicon-m-map')
                ->color('primary'),
            
            Stat::make('Producción Total', number_format($summary['total_production'], 0) . ' kWh/día')
                ->description('Energía estimada diaria')
                ->descriptionIcon('heroicon-m-sun')
                ->color('success'),
            
            Stat::make('Energía Disponible', number_format($summary['total_available'], 0) . ' kWh/día')
                ->description('Energía sin reservar')
                ->descriptionIcon('heroicon-m-battery-100')
                ->color('info'),
            
            Stat::make('Utilización', number_format($summary['utilization_percentage'], 1) . '%')
                ->description('Porcentaje de energía reservada')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($summary['utilization_percentage'] >= 90 ? 'danger' : ($summary['utilization_percentage'] >= 70 ? 'warning' : 'success')),
            
            Stat::make('Zonas Verdes', $summary['zones_by_status']['verde'] ?? 0)
                ->description('Zonas con buen estado')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('Zonas Naranjas', $summary['zones_by_status']['naranja'] ?? 0)
                ->description('Zonas con atención')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
            
            Stat::make('Zonas Rojas', $summary['zones_by_status']['rojo'] ?? 0)
                ->description('Zonas críticas')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
