<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DashboardView;
use App\Models\User;
use Carbon\Carbon;

class DashboardViewSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📊 Creando vistas de dashboard...');

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        // Limpiar vistas existentes
        DashboardView::query()->delete();

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");

        // Crear diferentes tipos de vistas de dashboard
        $this->createDefaultDashboards($users);
        $this->createEnergyMonitoringDashboards($users);
        $this->createFinancialDashboards($users);
        $this->createOperationalDashboards($users);
        $this->createAnalyticsDashboards($users);
        $this->createPublicDashboards($users);

        $this->command->info('✅ DashboardViewSeeder completado. Se crearon ' . DashboardView::count() . ' vistas de dashboard.');
    }

    private function createDefaultDashboards($users): void
    {
        $this->command->info('🏠 Creando dashboards por defecto...');

        foreach ($users as $user) {
            DashboardView::create([
                'user_id' => $user->id,
                'name' => 'Dashboard Principal',
                'layout_json' => [
                    'grid' => [
                        'columns' => 12,
                        'rows' => 8,
                        'cellSize' => 50,
                        'margin' => 10
                    ],
                    'widgets' => [
                        [
                            'id' => 'welcome-widget',
                            'type' => 'welcome',
                            'position' => ['x' => 0, 'y' => 0, 'w' => 6, 'h' => 2],
                            'settings' => [
                                'title' => 'Bienvenido',
                                'show_time' => true,
                                'show_weather' => true
                            ]
                        ],
                        [
                            'id' => 'quick-stats',
                            'type' => 'stats',
                            'position' => ['x' => 6, 'y' => 0, 'w' => 6, 'h' => 2],
                            'settings' => [
                                'metrics' => ['total_energy', 'active_installations', 'total_customers'],
                                'display_type' => 'cards'
                            ]
                        ],
                        [
                            'id' => 'recent-activities',
                            'type' => 'activity_feed',
                            'position' => ['x' => 0, 'y' => 2, 'w' => 8, 'h' => 4],
                            'settings' => [
                                'limit' => 10,
                                'show_avatars' => true,
                                'show_timestamps' => true
                            ]
                        ],
                        [
                            'id' => 'energy-chart',
                            'type' => 'chart',
                            'position' => ['x' => 8, 'y' => 2, 'w' => 4, 'h' => 4],
                            'settings' => [
                                'chart_type' => 'line',
                                'data_source' => 'energy_consumption',
                                'time_range' => '24h',
                                'show_legend' => true
                            ]
                        ]
                    ]
                ],
                'is_default' => true,
                'theme' => 'default',
                'color_scheme' => 'light',
                'widget_settings' => [
                    'refresh_interval' => 300,
                    'auto_refresh' => true,
                    'show_loading' => true,
                    'enable_animations' => true
                ],
                'is_public' => false,
                'description' => 'Dashboard principal con información general del sistema',
                'access_permissions' => [
                    'view' => ['owner'],
                    'edit' => ['owner'],
                    'share' => ['owner']
                ],
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30))
            ]);

            $this->command->line("   ✅ Dashboard por defecto creado para: {$user->name}");
        }
    }

    private function createEnergyMonitoringDashboards($users): void
    {
        $this->command->info('⚡ Creando dashboards de monitoreo energético...');

        $energyUsers = $users->random(min(15, $users->count()));

        foreach ($energyUsers as $user) {
            DashboardView::create([
                'user_id' => $user->id,
                'name' => 'Monitoreo Energético',
                'layout_json' => [
                    'grid' => [
                        'columns' => 12,
                        'rows' => 10,
                        'cellSize' => 50,
                        'margin' => 8
                    ],
                    'widgets' => [
                        [
                            'id' => 'energy-overview',
                            'type' => 'energy_overview',
                            'position' => ['x' => 0, 'y' => 0, 'w' => 12, 'h' => 2],
                            'settings' => [
                                'show_total_consumption' => true,
                                'show_total_production' => true,
                                'show_efficiency' => true,
                                'currency' => 'EUR'
                            ]
                        ],
                        [
                            'id' => 'real-time-consumption',
                            'type' => 'real_time_chart',
                            'position' => ['x' => 0, 'y' => 2, 'w' => 6, 'h' => 4],
                            'settings' => [
                                'chart_type' => 'area',
                                'data_source' => 'real_time_consumption',
                                'update_interval' => 30,
                                'show_thresholds' => true
                            ]
                        ],
                        [
                            'id' => 'production-chart',
                            'type' => 'chart',
                            'position' => ['x' => 6, 'y' => 2, 'w' => 6, 'h' => 4],
                            'settings' => [
                                'chart_type' => 'bar',
                                'data_source' => 'energy_production',
                                'time_range' => '7d',
                                'group_by' => 'day'
                            ]
                        ],
                        [
                            'id' => 'meter-status',
                            'type' => 'meter_status',
                            'position' => ['x' => 0, 'y' => 6, 'w' => 4, 'h' => 3],
                            'settings' => [
                                'show_online_meters' => true,
                                'show_offline_meters' => true,
                                'show_maintenance' => true,
                                'refresh_interval' => 60
                            ]
                        ],
                        [
                            'id' => 'alerts-panel',
                            'type' => 'alerts',
                            'position' => ['x' => 4, 'y' => 6, 'w' => 4, 'h' => 3],
                            'settings' => [
                                'alert_types' => ['critical', 'warning', 'info'],
                                'max_alerts' => 10,
                                'auto_dismiss' => false
                            ]
                        ],
                        [
                            'id' => 'efficiency-metrics',
                            'type' => 'efficiency_metrics',
                            'position' => ['x' => 8, 'y' => 6, 'w' => 4, 'h' => 3],
                            'settings' => [
                                'show_power_factor' => true,
                                'show_load_factor' => true,
                                'show_efficiency_score' => true
                            ]
                        ]
                    ]
                ],
                'is_default' => false,
                'theme' => 'solar',
                'color_scheme' => 'light',
                'widget_settings' => [
                    'refresh_interval' => 60,
                    'auto_refresh' => true,
                    'show_loading' => true,
                    'enable_animations' => true,
                    'enable_notifications' => true
                ],
                'is_public' => false,
                'description' => 'Dashboard especializado para monitoreo de consumo y producción energética',
                'access_permissions' => [
                    'view' => ['owner', 'energy_manager'],
                    'edit' => ['owner'],
                    'share' => ['owner', 'energy_manager']
                ],
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30))
            ]);

            $this->command->line("   ✅ Dashboard de monitoreo energético creado para: {$user->name}");
        }
    }

    private function createFinancialDashboards($users): void
    {
        $this->command->info('💰 Creando dashboards financieros...');

        $financialUsers = $users->random(min(12, $users->count()));

        foreach ($financialUsers as $user) {
            DashboardView::create([
                'user_id' => $user->id,
                'name' => 'Dashboard Financiero',
                'layout_json' => [
                    'grid' => [
                        'columns' => 12,
                        'rows' => 8,
                        'cellSize' => 50,
                        'margin' => 10
                    ],
                    'widgets' => [
                        [
                            'id' => 'financial-summary',
                            'type' => 'financial_summary',
                            'position' => ['x' => 0, 'y' => 0, 'w' => 12, 'h' => 2],
                            'settings' => [
                                'show_revenue' => true,
                                'show_expenses' => true,
                                'show_profit' => true,
                                'show_margin' => true,
                                'currency' => 'EUR'
                            ]
                        ],
                        [
                            'id' => 'revenue-chart',
                            'type' => 'chart',
                            'position' => ['x' => 0, 'y' => 2, 'w' => 6, 'h' => 3],
                            'settings' => [
                                'chart_type' => 'line',
                                'data_source' => 'revenue',
                                'time_range' => '30d',
                                'show_trend' => true
                            ]
                        ],
                        [
                            'id' => 'expenses-breakdown',
                            'type' => 'pie_chart',
                            'position' => ['x' => 6, 'y' => 2, 'w' => 6, 'h' => 3],
                            'settings' => [
                                'data_source' => 'expenses_by_category',
                                'show_percentages' => true,
                                'show_values' => true
                            ]
                        ],
                        [
                            'id' => 'customer-billing',
                            'type' => 'billing_summary',
                            'position' => ['x' => 0, 'y' => 5, 'w' => 4, 'h' => 3],
                            'settings' => [
                                'show_pending_bills' => true,
                                'show_overdue_bills' => true,
                                'show_collected_amount' => true
                            ]
                        ],
                        [
                            'id' => 'payment-methods',
                            'type' => 'payment_methods',
                            'position' => ['x' => 4, 'y' => 5, 'w' => 4, 'h' => 3],
                            'settings' => [
                                'show_methods' => true,
                                'show_volumes' => true,
                                'show_trends' => true
                            ]
                        ],
                        [
                            'id' => 'financial-kpis',
                            'type' => 'kpi_cards',
                            'position' => ['x' => 8, 'y' => 5, 'w' => 4, 'h' => 3],
                            'settings' => [
                                'kpis' => ['roi', 'cash_flow', 'debt_ratio', 'profit_margin'],
                                'show_targets' => true,
                                'show_variance' => true
                            ]
                        ]
                    ]
                ],
                'is_default' => false,
                'theme' => 'default',
                'color_scheme' => 'light',
                'widget_settings' => [
                    'refresh_interval' => 600,
                    'auto_refresh' => true,
                    'show_loading' => true,
                    'enable_animations' => false,
                    'enable_notifications' => true
                ],
                'is_public' => false,
                'description' => 'Dashboard para análisis financiero y seguimiento de métricas económicas',
                'access_permissions' => [
                    'view' => ['owner', 'finance_manager'],
                    'edit' => ['owner', 'finance_manager'],
                    'share' => ['owner']
                ],
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30))
            ]);

            $this->command->line("   ✅ Dashboard financiero creado para: {$user->name}");
        }
    }

    private function createOperationalDashboards($users): void
    {
        $this->command->info('🔧 Creando dashboards operacionales...');

        $operationalUsers = $users->random(min(10, $users->count()));

        foreach ($operationalUsers as $user) {
            DashboardView::create([
                'user_id' => $user->id,
                'name' => 'Dashboard Operacional',
                'layout_json' => [
                    'grid' => [
                        'columns' => 12,
                        'rows' => 9,
                        'cellSize' => 50,
                        'margin' => 8
                    ],
                    'widgets' => [
                        [
                            'id' => 'operational-status',
                            'type' => 'operational_status',
                            'position' => ['x' => 0, 'y' => 0, 'w' => 12, 'h' => 2],
                            'settings' => [
                                'show_system_status' => true,
                                'show_maintenance_schedule' => true,
                                'show_incidents' => true
                            ]
                        ],
                        [
                            'id' => 'maintenance-tasks',
                            'type' => 'maintenance_tasks',
                            'position' => ['x' => 0, 'y' => 2, 'w' => 6, 'h' => 4],
                            'settings' => [
                                'show_pending_tasks' => true,
                                'show_completed_tasks' => true,
                                'show_overdue_tasks' => true,
                                'max_tasks' => 15
                            ]
                        ],
                        [
                            'id' => 'equipment-status',
                            'type' => 'equipment_status',
                            'position' => ['x' => 6, 'y' => 2, 'w' => 6, 'h' => 4],
                            'settings' => [
                                'show_online_equipment' => true,
                                'show_offline_equipment' => true,
                                'show_maintenance_mode' => true,
                                'show_efficiency' => true
                            ]
                        ],
                        [
                            'id' => 'work-orders',
                            'type' => 'work_orders',
                            'position' => ['x' => 0, 'y' => 6, 'w' => 4, 'h' => 3],
                            'settings' => [
                                'show_open_orders' => true,
                                'show_in_progress' => true,
                                'show_completed' => true,
                                'priority_filter' => 'all'
                            ]
                        ],
                        [
                            'id' => 'team-assignments',
                            'type' => 'team_assignments',
                            'position' => ['x' => 4, 'y' => 6, 'w' => 4, 'h' => 3],
                            'settings' => [
                                'show_available_team' => true,
                                'show_busy_team' => true,
                                'show_workload' => true
                            ]
                        ],
                        [
                            'id' => 'performance-metrics',
                            'type' => 'performance_metrics',
                            'position' => ['x' => 8, 'y' => 6, 'w' => 4, 'h' => 3],
                            'settings' => [
                                'show_uptime' => true,
                                'show_efficiency' => true,
                                'show_quality_score' => true,
                                'show_safety_metrics' => true
                            ]
                        ]
                    ]
                ],
                'is_default' => false,
                'theme' => 'default',
                'color_scheme' => 'light',
                'widget_settings' => [
                    'refresh_interval' => 120,
                    'auto_refresh' => true,
                    'show_loading' => true,
                    'enable_animations' => true,
                    'enable_notifications' => true
                ],
                'is_public' => false,
                'description' => 'Dashboard para seguimiento de operaciones y mantenimiento',
                'access_permissions' => [
                    'view' => ['owner', 'operations_manager'],
                    'edit' => ['owner', 'operations_manager'],
                    'share' => ['owner', 'operations_manager']
                ],
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30))
            ]);

            $this->command->line("   ✅ Dashboard operacional creado para: {$user->name}");
        }
    }

    private function createAnalyticsDashboards($users): void
    {
        $this->command->info('📈 Creando dashboards de analytics...');

        $analyticsUsers = $users->random(min(8, $users->count()));

        foreach ($analyticsUsers as $user) {
            DashboardView::create([
                'user_id' => $user->id,
                'name' => 'Dashboard Analytics',
                'layout_json' => [
                    'grid' => [
                        'columns' => 12,
                        'rows' => 10,
                        'cellSize' => 50,
                        'margin' => 8
                    ],
                    'widgets' => [
                        [
                            'id' => 'analytics-overview',
                            'type' => 'analytics_overview',
                            'position' => ['x' => 0, 'y' => 0, 'w' => 12, 'h' => 2],
                            'settings' => [
                                'show_key_metrics' => true,
                                'show_trends' => true,
                                'show_comparisons' => true
                            ]
                        ],
                        [
                            'id' => 'trend-analysis',
                            'type' => 'trend_analysis',
                            'position' => ['x' => 0, 'y' => 2, 'w' => 8, 'h' => 4],
                            'settings' => [
                                'chart_type' => 'multi_line',
                                'data_sources' => ['energy_consumption', 'revenue', 'customers'],
                                'time_range' => '90d',
                                'show_forecast' => true
                            ]
                        ],
                        [
                            'id' => 'correlation-matrix',
                            'type' => 'correlation_matrix',
                            'position' => ['x' => 8, 'y' => 2, 'w' => 4, 'h' => 4],
                            'settings' => [
                                'variables' => ['consumption', 'temperature', 'time', 'cost'],
                                'show_strength' => true,
                                'show_significance' => true
                            ]
                        ],
                        [
                            'id' => 'customer-segments',
                            'type' => 'customer_segments',
                            'position' => ['x' => 0, 'y' => 6, 'w' => 4, 'h' => 3],
                            'settings' => [
                                'segmentation_criteria' => ['consumption', 'location', 'type'],
                                'show_distribution' => true,
                                'show_characteristics' => true
                            ]
                        ],
                        [
                            'id' => 'predictive-models',
                            'type' => 'predictive_models',
                            'position' => ['x' => 4, 'y' => 6, 'w' => 4, 'h' => 3],
                            'settings' => [
                                'models' => ['consumption_forecast', 'demand_prediction', 'anomaly_detection'],
                                'show_accuracy' => true,
                                'show_predictions' => true
                            ]
                        ],
                        [
                            'id' => 'data-quality',
                            'type' => 'data_quality',
                            'position' => ['x' => 8, 'y' => 6, 'w' => 4, 'h' => 3],
                            'settings' => [
                                'show_completeness' => true,
                                'show_accuracy' => true,
                                'show_consistency' => true,
                                'show_timeliness' => true
                            ]
                        ]
                    ]
                ],
                'is_default' => false,
                'theme' => 'dark',
                'color_scheme' => 'dark',
                'widget_settings' => [
                    'refresh_interval' => 300,
                    'auto_refresh' => true,
                    'show_loading' => true,
                    'enable_animations' => true,
                    'enable_notifications' => false
                ],
                'is_public' => false,
                'description' => 'Dashboard avanzado para análisis de datos y business intelligence',
                'access_permissions' => [
                    'view' => ['owner', 'data_analyst'],
                    'edit' => ['owner', 'data_analyst'],
                    'share' => ['owner']
                ],
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30))
            ]);

            $this->command->line("   ✅ Dashboard analytics creado para: {$user->name}");
        }
    }

    private function createPublicDashboards($users): void
    {
        $this->command->info('🌐 Creando dashboards públicos...');

        $publicUsers = $users->random(min(5, $users->count()));

        foreach ($publicUsers as $user) {
            DashboardView::create([
                'user_id' => $user->id,
                'name' => 'Dashboard Público',
                'layout_json' => [
                    'grid' => [
                        'columns' => 12,
                        'rows' => 6,
                        'cellSize' => 50,
                        'margin' => 10
                    ],
                    'widgets' => [
                        [
                            'id' => 'public-stats',
                            'type' => 'public_stats',
                            'position' => ['x' => 0, 'y' => 0, 'w' => 12, 'h' => 2],
                            'settings' => [
                                'show_total_energy' => true,
                                'show_total_customers' => true,
                                'show_co2_saved' => true,
                                'show_renewable_percentage' => true
                            ]
                        ],
                        [
                            'id' => 'energy-map',
                            'type' => 'energy_map',
                            'position' => ['x' => 0, 'y' => 2, 'w' => 6, 'h' => 4],
                            'settings' => [
                                'show_installations' => true,
                                'show_consumption_heatmap' => true,
                                'show_renewable_sources' => true
                            ]
                        ],
                        [
                            'id' => 'sustainability-metrics',
                            'type' => 'sustainability_metrics',
                            'position' => ['x' => 6, 'y' => 2, 'w' => 6, 'h' => 4],
                            'settings' => [
                                'show_co2_reduction' => true,
                                'show_renewable_energy' => true,
                                'show_energy_efficiency' => true,
                                'show_sustainability_score' => true
                            ]
                        ]
                    ]
                ],
                'is_default' => false,
                'theme' => 'solar',
                'color_scheme' => 'light',
                'widget_settings' => [
                    'refresh_interval' => 600,
                    'auto_refresh' => true,
                    'show_loading' => true,
                    'enable_animations' => true,
                    'enable_notifications' => false
                ],
                'is_public' => true,
                'description' => 'Dashboard público con información general de la cooperativa energética',
                'access_permissions' => [
                    'view' => ['public'],
                    'edit' => ['owner'],
                    'share' => ['owner']
                ],
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30))
            ]);

            $this->command->line("   ✅ Dashboard público creado para: {$user->name}");
        }
    }
}
