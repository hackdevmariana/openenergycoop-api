<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DashboardWidget;
use App\Models\DashboardView;
use App\Models\User;
use Carbon\Carbon;

class DashboardWidgetSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎯 Creando widgets de dashboard...');

        $users = User::all();
        $dashboardViews = DashboardView::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        if ($dashboardViews->isEmpty()) {
            $this->command->error('❌ No hay vistas de dashboard disponibles.');
            return;
        }

        // Limpiar widgets existentes
        DashboardWidget::query()->delete();

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("📊 Vistas de dashboard disponibles: {$dashboardViews->count()}");

        // Crear diferentes tipos de widgets
        $this->createEnergyWidgets($users, $dashboardViews);
        $this->createFinancialWidgets($users, $dashboardViews);
        $this->createOperationalWidgets($users, $dashboardViews);
        $this->createAnalyticsWidgets($users, $dashboardViews);
        $this->createUtilityWidgets($users, $dashboardViews);
        $this->createMonitoringWidgets($users, $dashboardViews);

        $this->command->info('✅ DashboardWidgetSeeder completado. Se crearon ' . DashboardWidget::count() . ' widgets.');
    }

    private function createEnergyWidgets($users, $dashboardViews): void
    {
        $this->command->info('⚡ Creando widgets de energía...');

        $energyViews = $dashboardViews->where('name', 'Monitoreo Energético');
        $generalViews = $dashboardViews->where('name', 'Dashboard Principal');

        // Widgets para vistas de monitoreo energético
        foreach ($energyViews as $view) {
            $this->createWidget($view, 'energy_production', 'Producción de Energía', [
                'chart_type' => 'area',
                'data_source' => 'energy_production',
                'time_range' => '24h',
                'show_legend' => true,
                'show_tooltip' => true,
                'refresh_interval' => 300,
                'unit' => 'kWh',
                'color_scheme' => 'green'
            ], 'large', [0, 0, 6, 4]);

            $this->createWidget($view, 'energy_consumption', 'Consumo de Energía', [
                'chart_type' => 'line',
                'data_source' => 'energy_consumption',
                'time_range' => '24h',
                'show_legend' => true,
                'show_tooltip' => true,
                'refresh_interval' => 300,
                'unit' => 'kWh',
                'color_scheme' => 'blue'
            ], 'large', [6, 0, 6, 4]);

            $this->createWidget($view, 'devices', 'Estado de Dispositivos', [
                'show_online_devices' => true,
                'show_offline_devices' => true,
                'show_maintenance_devices' => true,
                'max_devices' => 10,
                'refresh_interval' => 60,
                'show_status_icons' => true
            ], 'medium', [0, 4, 4, 3]);

            $this->createWidget($view, 'metrics', 'Métricas de Eficiencia', [
                'show_power_factor' => true,
                'show_load_factor' => true,
                'show_efficiency_score' => true,
                'show_co2_saved' => true,
                'refresh_interval' => 600
            ], 'medium', [4, 4, 4, 3]);

            $this->createWidget($view, 'events', 'Eventos del Sistema', [
                'event_types' => ['critical', 'warning', 'info'],
                'max_events' => 15,
                'show_timestamps' => true,
                'auto_dismiss' => false,
                'refresh_interval' => 30
            ], 'medium', [8, 4, 4, 3]);
        }

        // Widgets de energía para vistas generales
        foreach ($generalViews as $view) {
            $this->createWidget($view, 'energy_consumption', 'Consumo Energético', [
                'chart_type' => 'bar',
                'data_source' => 'energy_consumption',
                'time_range' => '7d',
                'show_legend' => false,
                'show_tooltip' => true,
                'refresh_interval' => 600,
                'unit' => 'kWh',
                'color_scheme' => 'blue'
            ], 'medium', [0, 0, 6, 3]);

            $this->createWidget($view, 'devices', 'Dispositivos Activos', [
                'show_online_devices' => true,
                'show_total_devices' => true,
                'max_devices' => 5,
                'refresh_interval' => 300,
                'show_status_icons' => true
            ], 'small', [6, 0, 6, 3]);
        }

        $this->command->info("   ✅ Widgets de energía creados");
    }

    private function createFinancialWidgets($users, $dashboardViews): void
    {
        $this->command->info('💰 Creando widgets financieros...');

        $financialViews = $dashboardViews->where('name', 'Dashboard Financiero');

        foreach ($financialViews as $view) {
            $this->createWidget($view, 'wallet', 'Balance General', [
                'show_balance' => true,
                'show_income' => true,
                'show_expenses' => true,
                'show_profit' => true,
                'currency' => 'EUR',
                'refresh_interval' => 600,
                'show_trends' => true
            ], 'large', [0, 0, 12, 2]);

            $this->createWidget($view, 'charts', 'Ingresos vs Gastos', [
                'chart_type' => 'line',
                'data_source' => 'financial_data',
                'time_range' => '30d',
                'show_legend' => true,
                'show_tooltip' => true,
                'refresh_interval' => 1800,
                'currency' => 'EUR',
                'show_comparison' => true
            ], 'large', [0, 2, 6, 3]);

            $this->createWidget($view, 'charts', 'Distribución de Gastos', [
                'chart_type' => 'pie',
                'data_source' => 'expenses_by_category',
                'show_percentages' => true,
                'show_values' => true,
                'refresh_interval' => 1800,
                'currency' => 'EUR',
                'max_categories' => 8
            ], 'medium', [6, 2, 6, 3]);

            $this->createWidget($view, 'billing', 'Facturación Pendiente', [
                'show_pending_bills' => true,
                'show_overdue_bills' => true,
                'show_collected_amount' => true,
                'max_bills' => 10,
                'refresh_interval' => 900,
                'currency' => 'EUR'
            ], 'medium', [0, 5, 4, 3]);

            $this->createWidget($view, 'metrics', 'KPIs Financieros', [
                'show_roi' => true,
                'show_cash_flow' => true,
                'show_debt_ratio' => true,
                'show_profit_margin' => true,
                'refresh_interval' => 1800,
                'show_targets' => true
            ], 'medium', [4, 5, 4, 3]);

            $this->createWidget($view, 'notifications', 'Alertas Financieras', [
                'notification_types' => ['payment_due', 'overdue', 'budget_exceeded'],
                'max_notifications' => 8,
                'show_timestamps' => true,
                'auto_dismiss' => false,
                'refresh_interval' => 300
            ], 'medium', [8, 5, 4, 3]);
        }

        $this->command->info("   ✅ Widgets financieros creados");
    }

    private function createOperationalWidgets($users, $dashboardViews): void
    {
        $this->command->info('🔧 Creando widgets operacionales...');

        $operationalViews = $dashboardViews->where('name', 'Dashboard Operacional');

        foreach ($operationalViews as $view) {
            $this->createWidget($view, 'maintenance', 'Tareas de Mantenimiento', [
                'show_pending_tasks' => true,
                'show_completed_tasks' => true,
                'show_overdue_tasks' => true,
                'max_tasks' => 15,
                'refresh_interval' => 300,
                'show_priority' => true,
                'show_assigned_to' => true
            ], 'large', [0, 0, 6, 4]);

            $this->createWidget($view, 'devices', 'Estado del Equipo', [
                'show_online_equipment' => true,
                'show_offline_equipment' => true,
                'show_maintenance_mode' => true,
                'show_efficiency' => true,
                'max_equipment' => 12,
                'refresh_interval' => 120
            ], 'large', [6, 0, 6, 4]);

            $this->createWidget($view, 'events', 'Órdenes de Trabajo', [
                'show_open_orders' => true,
                'show_in_progress' => true,
                'show_completed' => true,
                'priority_filter' => 'all',
                'max_orders' => 10,
                'refresh_interval' => 300
            ], 'medium', [0, 4, 4, 3]);

            $this->createWidget($view, 'metrics', 'Métricas de Rendimiento', [
                'show_uptime' => true,
                'show_efficiency' => true,
                'show_quality_score' => true,
                'show_safety_metrics' => true,
                'refresh_interval' => 600,
                'show_targets' => true
            ], 'medium', [4, 4, 4, 3]);

            $this->createWidget($view, 'notifications', 'Alertas Operacionales', [
                'notification_types' => ['maintenance_due', 'equipment_failure', 'safety_alert'],
                'max_notifications' => 10,
                'show_timestamps' => true,
                'auto_dismiss' => false,
                'refresh_interval' => 60
            ], 'medium', [8, 4, 4, 3]);
        }

        $this->command->info("   ✅ Widgets operacionales creados");
    }

    private function createAnalyticsWidgets($users, $dashboardViews): void
    {
        $this->command->info('📈 Creando widgets de analytics...');

        $analyticsViews = $dashboardViews->where('name', 'Dashboard Analytics');

        foreach ($analyticsViews as $view) {
            $this->createWidget($view, 'analytics', 'Análisis de Tendencias', [
                'chart_type' => 'multi_line',
                'data_sources' => ['energy_consumption', 'revenue', 'customers'],
                'time_range' => '90d',
                'show_forecast' => true,
                'show_correlation' => true,
                'refresh_interval' => 1800
            ], 'large', [0, 0, 8, 4]);

            $this->createWidget($view, 'analytics', 'Matriz de Correlación', [
                'variables' => ['consumption', 'temperature', 'time', 'cost'],
                'show_strength' => true,
                'show_significance' => true,
                'refresh_interval' => 3600
            ], 'medium', [8, 0, 4, 4]);

            $this->createWidget($view, 'analytics', 'Segmentación de Clientes', [
                'segmentation_criteria' => ['consumption', 'location', 'type'],
                'show_distribution' => true,
                'show_characteristics' => true,
                'max_segments' => 6,
                'refresh_interval' => 3600
            ], 'medium', [0, 4, 4, 3]);

            $this->createWidget($view, 'analytics', 'Modelos Predictivos', [
                'models' => ['consumption_forecast', 'demand_prediction', 'anomaly_detection'],
                'show_accuracy' => true,
                'show_predictions' => true,
                'refresh_interval' => 3600
            ], 'medium', [4, 4, 4, 3]);

            $this->createWidget($view, 'analytics', 'Calidad de Datos', [
                'show_completeness' => true,
                'show_accuracy' => true,
                'show_consistency' => true,
                'show_timeliness' => true,
                'refresh_interval' => 1800
            ], 'medium', [8, 4, 4, 3]);
        }

        $this->command->info("   ✅ Widgets de analytics creados");
    }

    private function createUtilityWidgets($users, $dashboardViews): void
    {
        $this->command->info('🔧 Creando widgets de utilidad...');

        $generalViews = $dashboardViews->where('name', 'Dashboard Principal');

        foreach ($generalViews as $view) {
            $this->createWidget($view, 'weather', 'Clima Local', [
                'location' => 'Madrid, España',
                'show_temperature' => true,
                'show_humidity' => true,
                'show_wind' => true,
                'show_forecast' => true,
                'refresh_interval' => 1800,
                'units' => 'metric'
            ], 'small', [0, 3, 3, 2]);

            $this->createWidget($view, 'calendar', 'Calendario', [
                'show_today' => true,
                'show_upcoming' => true,
                'max_events' => 5,
                'show_reminders' => true,
                'refresh_interval' => 300
            ], 'small', [3, 3, 3, 2]);

            $this->createWidget($view, 'notifications', 'Notificaciones', [
                'notification_types' => ['system', 'user', 'alert'],
                'max_notifications' => 8,
                'show_timestamps' => true,
                'auto_dismiss' => true,
                'refresh_interval' => 60
            ], 'small', [6, 3, 3, 2]);

            $this->createWidget($view, 'news', 'Noticias', [
                'news_source' => 'energy_news',
                'max_news' => 5,
                'show_headlines' => true,
                'show_summaries' => false,
                'refresh_interval' => 3600
            ], 'small', [9, 3, 3, 2]);
        }

        $this->command->info("   ✅ Widgets de utilidad creados");
    }

    private function createMonitoringWidgets($users, $dashboardViews): void
    {
        $this->command->info('📊 Creando widgets de monitoreo...');

        $monitoringViews = $dashboardViews->where('name', 'Monitoreo Energético');

        foreach ($monitoringViews as $view) {
            $this->createWidget($view, 'metrics', 'Métricas en Tiempo Real', [
                'show_current_power' => true,
                'show_voltage' => true,
                'show_current' => true,
                'show_frequency' => true,
                'refresh_interval' => 30,
                'show_alerts' => true
            ], 'medium', [0, 7, 4, 2]);

            $this->createWidget($view, 'events', 'Log de Eventos', [
                'event_types' => ['system', 'user', 'error'],
                'max_events' => 20,
                'show_timestamps' => true,
                'show_severity' => true,
                'refresh_interval' => 60
            ], 'medium', [4, 7, 4, 2]);

            $this->createWidget($view, 'notifications', 'Alertas del Sistema', [
                'notification_types' => ['critical', 'warning', 'info'],
                'max_notifications' => 10,
                'show_timestamps' => true,
                'auto_dismiss' => false,
                'refresh_interval' => 30
            ], 'medium', [8, 7, 4, 2]);
        }

        $this->command->info("   ✅ Widgets de monitoreo creados");
    }

    private function createWidget($dashboardView, $type, $title, $settings, $size, $gridPosition): void
    {
        DashboardWidget::create([
            'user_id' => $dashboardView->user_id,
            'dashboard_view_id' => $dashboardView->id,
            'type' => $type,
            'title' => $title,
            'position' => rand(0, 100),
            'settings_json' => $settings,
            'visible' => true,
            'collapsible' => fake()->boolean(30),
            'collapsed' => false,
            'size' => $size,
            'grid_position' => [
                'x' => $gridPosition[0],
                'y' => $gridPosition[1],
                'w' => $gridPosition[2],
                'h' => $gridPosition[3]
            ],
            'refresh_interval' => $settings['refresh_interval'] ?? 300,
            'last_refresh' => Carbon::now()->subMinutes(rand(1, 60)),
            'data_source' => $settings['data_source'] ?? null,
            'filters' => [
                'time_range' => $settings['time_range'] ?? '24h',
                'user_id' => $dashboardView->user_id,
                'dashboard_id' => $dashboardView->id
            ],
            'permissions' => [
                'view' => ['owner'],
                'edit' => ['owner'],
                'delete' => ['owner']
            ],
            'created_at' => Carbon::now()->subDays(rand(0, 30)),
            'updated_at' => Carbon::now()->subDays(rand(0, 30))
        ]);
    }
}
