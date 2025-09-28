<?php

namespace App\Filament\Widgets;

use App\Models\EnergyRightPreSale;
use Filament\Widgets\ChartWidget;

class EnergyRightPreSaleChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribución de Preventas de Derechos Energéticos';

    protected function getData(): array
    {
        $preSales = EnergyRightPreSale::all();

        // Datos por estado
        $byStatus = [
            'Pendientes' => $preSales->where('status', 'pending')->count(),
            'Confirmadas' => $preSales->where('status', 'confirmed')->count(),
            'Canceladas' => $preSales->where('status', 'cancelled')->count(),
        ];

        // Datos por tipo de asociación
        $byType = [
            'Con Instalación' => $preSales->whereNotNull('energy_installation_id')->count(),
            'Por Zona' => $preSales->whereNull('energy_installation_id')->count(),
        ];

        // Datos por estado de expiración
        $byExpiration = [
            'Activas' => $preSales->where('status', 'confirmed')->where('expires_at', '>', now())->count(),
            'Expiradas' => $preSales->where('expires_at', '<=', now())->count(),
            'Sin Expiración' => $preSales->whereNull('expires_at')->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Por Estado',
                    'data' => array_values($byStatus),
                    'backgroundColor' => [
                        'rgba(245, 158, 11, 0.8)',  // Naranja para pendientes
                        'rgba(34, 197, 94, 0.8)',   // Verde para confirmadas
                        'rgba(239, 68, 68, 0.8)',   // Rojo para canceladas
                    ],
                    'borderColor' => [
                        'rgba(245, 158, 11, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(239, 68, 68, 1)',
                    ],
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Por Tipo',
                    'data' => array_values($byType),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',  // Azul para instalación
                        'rgba(107, 114, 128, 0.8)', // Gris para zona
                    ],
                    'borderColor' => [
                        'rgba(59, 130, 246, 1)',
                        'rgba(107, 114, 128, 1)',
                    ],
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Por Expiración',
                    'data' => array_values($byExpiration),
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',   // Verde para activas
                        'rgba(239, 68, 68, 0.8)',   // Rojo para expiradas
                        'rgba(107, 114, 128, 0.8)', // Gris para sin expiración
                    ],
                    'borderColor' => [
                        'rgba(34, 197, 94, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(107, 114, 128, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => [
                'Pendientes', 'Confirmadas', 'Canceladas',
                'Con Instalación', 'Por Zona',
                'Activas', 'Expiradas', 'Sin Expiración'
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
                    'text' => 'Distribución de Preventas por Estado, Tipo y Expiración',
                ],
            ],
        ];
    }
}
