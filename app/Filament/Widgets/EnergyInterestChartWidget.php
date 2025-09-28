<?php

namespace App\Filament\Widgets;

use App\Models\EnergyInterest;
use Filament\Widgets\ChartWidget;

class EnergyInterestChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribución de Intereses Energéticos';

    protected function getData(): array
    {
        $interests = EnergyInterest::all();

        // Datos por tipo
        $byType = [
            'Consumidores' => $interests->where('type', 'consumer')->count(),
            'Productores' => $interests->where('type', 'producer')->count(),
            'Mixtos' => $interests->where('type', 'mixed')->count(),
        ];

        // Datos por estado
        $byStatus = [
            'Pendientes' => $interests->where('status', 'pending')->count(),
            'Aprobados' => $interests->where('status', 'approved')->count(),
            'Activos' => $interests->where('status', 'active')->count(),
            'Rechazados' => $interests->where('status', 'rejected')->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Por Tipo',
                    'data' => array_values($byType),
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',   // Verde para consumidores
                        'rgba(245, 158, 11, 0.8)',  // Naranja para productores
                        'rgba(59, 130, 246, 0.8)',  // Azul para mixtos
                    ],
                    'borderColor' => [
                        'rgba(34, 197, 94, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(59, 130, 246, 1)',
                    ],
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Por Estado',
                    'data' => array_values($byStatus),
                    'backgroundColor' => [
                        'rgba(245, 158, 11, 0.8)',  // Naranja para pendientes
                        'rgba(34, 197, 94, 0.8)',   // Verde para aprobados
                        'rgba(59, 130, 246, 0.8)',  // Azul para activos
                        'rgba(239, 68, 68, 0.8)',   // Rojo para rechazados
                    ],
                    'borderColor' => [
                        'rgba(245, 158, 11, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(239, 68, 68, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => [
                'Consumidores', 'Productores', 'Mixtos',
                'Pendientes', 'Aprobados', 'Activos', 'Rechazados'
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Distribución de Intereses por Tipo y Estado',
                ],
            ],
        ];
    }
}
