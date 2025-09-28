<?php

namespace App\Filament\Widgets;

use App\Models\EnergyParticipation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EnergyParticipationStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $summary = EnergyParticipation::getSystemSummary();

        return [
            Stat::make('Total Participaciones', $summary['total_participations'])
                ->description('Participaciones registradas')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Participaciones Activas', $summary['by_status']['active'])
                ->description('En curso')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Participaciones Suspendidas', $summary['by_status']['suspended'])
                ->description('Temporalmente pausadas')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('warning'),

            Stat::make('Participaciones Canceladas', $summary['by_status']['cancelled'])
                ->description('Canceladas por el usuario')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Participaciones Completadas', $summary['by_status']['completed'])
                ->description('Finalizadas exitosamente')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info'),

            Stat::make('Participaciones Mensuales', $summary['monthly_participations'])
                ->description('Con cuotas mensuales')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Aportaciones Únicas', $summary['one_time_participations'])
                ->description('Pago único')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('secondary'),

            Stat::make('Expiran Pronto', $summary['expiring_soon'])
                ->description('En los próximos 30 días')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Expiradas', $summary['expired'])
                ->description('Ya finalizadas')
                ->descriptionIcon('heroicon-m-calendar-x-mark')
                ->color('danger'),

            Stat::make('Ingresos Mensuales', '€' . number_format($summary['total_monthly_amount'], 2))
                ->description('Total de cuotas mensuales')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),

            Stat::make('Aportaciones Únicas', '€' . number_format($summary['total_one_time_amount'], 2))
                ->description('Total de aportaciones únicas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Derechos Energéticos', number_format($summary['total_energy_rights_kwh'], 2) . ' kWh')
                ->description('Total de derechos acumulados')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('warning'),

            Stat::make('Promedio Fidelidad', number_format($summary['average_fidelity_years'], 1) . ' años')
                ->description('Años promedio de fidelidad')
                ->descriptionIcon('heroicon-m-heart')
                ->color('success'),
        ];
    }
}