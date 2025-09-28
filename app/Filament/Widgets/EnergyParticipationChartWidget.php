<?php

namespace App\Filament\Widgets;

use App\Models\EnergyParticipation;
use Filament\Widgets\ChartWidget;

class EnergyParticipationChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribución de Participaciones Energéticas';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $participations = EnergyParticipation::all();

        // Datos por estado
        $statusData = [
            'Activas' => $participations->where('status', EnergyParticipation::STATUS_ACTIVE)->count(),
            'Suspendidas' => $participations->where('status', EnergyParticipation::STATUS_SUSPENDED)->count(),
            'Canceladas' => $participations->where('status', EnergyParticipation::STATUS_CANCELLED)->count(),
            'Completadas' => $participations->where('status', EnergyParticipation::STATUS_COMPLETED)->count(),
        ];

        // Datos por plan
        $planData = [
            'Raíz' => $participations->where('plan_code', EnergyParticipation::PLAN_RAIZ)->count(),
            'Hogar' => $participations->where('plan_code', EnergyParticipation::PLAN_HOGAR)->count(),
            'Independencia 22' => $participations->where('plan_code', EnergyParticipation::PLAN_INDEPENDENCIA_22)->count(),
            'Independencia 25' => $participations->where('plan_code', EnergyParticipation::PLAN_INDEPENDENCIA_25)->count(),
            'Independencia 30' => $participations->where('plan_code', EnergyParticipation::PLAN_INDEPENDENCIA_30)->count(),
        ];

        // Datos por tipo de pago
        $paymentTypeData = [
            'Mensuales' => $participations->whereNotNull('monthly_amount')->count(),
            'Aportaciones Únicas' => $participations->whereNotNull('one_time_amount')->count(),
        ];

        // Datos por expiración
        $expirationData = [
            'Con Fecha de Fin' => $participations->whereNotNull('end_date')->count(),
            'Sin Fecha de Fin' => $participations->whereNull('end_date')->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Por Estado',
                    'data' => array_values($statusData),
                    'backgroundColor' => [
                        '#10B981', // Verde para activas
                        '#F59E0B', // Naranja para suspendidas
                        '#EF4444', // Rojo para canceladas
                        '#3B82F6', // Azul para completadas
                    ],
                    'borderColor' => [
                        '#059669',
                        '#D97706',
                        '#DC2626',
                        '#2563EB',
                    ],
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Por Plan',
                    'data' => array_values($planData),
                    'backgroundColor' => [
                        '#10B981', // Raíz
                        '#3B82F6', // Hogar
                        '#F59E0B', // Independencia 22
                        '#8B5CF6', // Independencia 25
                        '#EF4444', // Independencia 30
                    ],
                    'borderColor' => [
                        '#059669',
                        '#2563EB',
                        '#D97706',
                        '#7C3AED',
                        '#DC2626',
                    ],
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Por Tipo de Pago',
                    'data' => array_values($paymentTypeData),
                    'backgroundColor' => [
                        '#3B82F6', // Mensuales
                        '#8B5CF6', // Aportaciones únicas
                    ],
                    'borderColor' => [
                        '#2563EB',
                        '#7C3AED',
                    ],
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Por Expiración',
                    'data' => array_values($expirationData),
                    'backgroundColor' => [
                        '#F59E0B', // Con fecha de fin
                        '#6B7280', // Sin fecha de fin
                    ],
                    'borderColor' => [
                        '#D97706',
                        '#4B5563',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                'Por Estado' => array_keys($statusData),
                'Por Plan' => array_keys($planData),
                'Por Tipo de Pago' => array_keys($paymentTypeData),
                'Por Expiración' => array_keys($expirationData),
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
                    'labels' => [
                        'padding' => 20,
                        'usePointStyle' => true,
                    ],
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Distribución de Participaciones Energéticas',
                    'font' => [
                        'size' => 16,
                        'weight' => 'bold',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}