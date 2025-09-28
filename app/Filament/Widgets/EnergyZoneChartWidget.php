<?php

namespace App\Filament\Widgets;

use App\Models\EnergyZoneSummary;
use Filament\Widgets\ChartWidget;

class EnergyZoneChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribución de Energía por Zona';

    protected function getData(): array
    {
        $zones = EnergyZoneSummary::with('municipality')
            ->orderBy('estimated_production_kwh_day', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Producción Estimada (kWh/día)',
                    'data' => $zones->pluck('estimated_production_kwh_day')->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Energía Reservada (kWh/día)',
                    'data' => $zones->pluck('reserved_kwh_day')->toArray(),
                    'backgroundColor' => 'rgba(245, 158, 11, 0.8)',
                    'borderColor' => 'rgba(245, 158, 11, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Energía Disponible (kWh/día)',
                    'data' => $zones->pluck('available_kwh_day')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $zones->map(function ($zone) {
                return $zone->zone_name . ' (' . $zone->postal_code . ')';
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Energía (kWh/día)',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Zonas Energéticas',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Top 10 Zonas por Producción Estimada',
                ],
            ],
        ];
    }
}
