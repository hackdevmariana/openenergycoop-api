<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('market_prices', function (Blueprint $table) {
            $table->id();
            
            // Información del mercado
            $table->string('market_name'); // Nombre del mercado (MIBEL, EPEX, etc.)
            $table->string('market_code')->nullable(); // Código del mercado
            $table->string('country'); // País del mercado
            $table->string('region')->nullable(); // Región específica
            $table->string('zone')->nullable(); // Zona del mercado
            
            // Tipo de producto energético
            $table->enum('commodity_type', [
                'electricity',      // Electricidad
                'natural_gas',      // Gas natural
                'carbon_credits',   // Créditos de carbono
                'renewable_certificates', // Certificados renovables
                'capacity',         // Capacidad
                'balancing'         // Servicios de balance
            ]);
            
            $table->string('product_name'); // Nombre específico del producto
            $table->text('product_description')->nullable();
            
            // Información temporal
            $table->datetime('price_datetime'); // Momento del precio
            $table->date('price_date'); // Fecha del precio (para índices)
            $table->time('price_time'); // Hora del precio
            $table->enum('period_type', [
                'real_time',        // Tiempo real
                'hourly',          // Precio horario
                'daily',           // Precio diario
                'weekly',          // Precio semanal
                'monthly',         // Precio mensual
                'quarterly',       // Precio trimestral
                'annual'           // Precio anual
            ]);
            
            // Período de entrega/delivery
            $table->date('delivery_start_date'); // Inicio entrega
            $table->date('delivery_end_date'); // Fin entrega
            $table->enum('delivery_period', [
                'spot',            // Mercado spot
                'next_day',        // Día siguiente
                'current_week',    // Semana actual
                'next_week',       // Semana siguiente
                'current_month',   // Mes actual
                'next_month',      // Mes siguiente
                'current_quarter', // Trimestre actual
                'next_quarter',    // Trimestre siguiente
                'current_year',    // Año actual
                'next_year'        // Año siguiente
            ]);
            
            // Precios
            $table->decimal('price', 12, 5); // Precio principal
            $table->string('currency', 3)->default('EUR');
            $table->string('unit'); // Unidad (EUR/MWh, EUR/tCO2, etc.)
            
            // Variaciones de precio en el período
            $table->decimal('opening_price', 12, 5)->nullable();
            $table->decimal('closing_price', 12, 5)->nullable();
            $table->decimal('high_price', 12, 5)->nullable(); // Precio máximo
            $table->decimal('low_price', 12, 5)->nullable(); // Precio mínimo
            $table->decimal('weighted_average_price', 12, 5)->nullable(); // Precio promedio ponderado
            
            // Volumen y liquidez
            $table->decimal('volume', 15, 4)->nullable(); // Volumen negociado
            $table->string('volume_unit')->nullable(); // Unidad del volumen (MWh, tCO2, etc.)
            $table->integer('number_of_transactions')->nullable(); // Número de transacciones
            $table->decimal('bid_price', 12, 5)->nullable(); // Precio de compra
            $table->decimal('ask_price', 12, 5)->nullable(); // Precio de venta
            $table->decimal('spread', 12, 5)->nullable(); // Diferencial bid-ask
            
            // Análisis del precio
            $table->decimal('price_change_absolute', 12, 5)->nullable(); // Cambio absoluto
            $table->decimal('price_change_percentage', 8, 4)->nullable(); // Cambio porcentual
            $table->decimal('volatility', 8, 4)->nullable(); // Volatilidad
            
            // Comparación con períodos anteriores
            $table->decimal('vs_previous_day', 8, 4)->nullable(); // vs día anterior %
            $table->decimal('vs_previous_week', 8, 4)->nullable(); // vs semana anterior %
            $table->decimal('vs_previous_month', 8, 4)->nullable(); // vs mes anterior %
            $table->decimal('vs_previous_year', 8, 4)->nullable(); // vs año anterior %
            
            // Factores que influyen en el precio
            $table->decimal('demand_mw', 12, 2)->nullable(); // Demanda
            $table->decimal('supply_mw', 12, 2)->nullable(); // Oferta
            $table->decimal('renewable_generation_mw', 12, 2)->nullable(); // Generación renovable
            $table->decimal('conventional_generation_mw', 12, 2)->nullable(); // Generación convencional
            $table->decimal('imports_mw', 12, 2)->nullable(); // Importaciones
            $table->decimal('exports_mw', 12, 2)->nullable(); // Exportaciones
            
            // Condiciones del sistema
            $table->decimal('system_margin_mw', 12, 2)->nullable(); // Margen del sistema
            $table->decimal('reserve_margin_percentage', 5, 2)->nullable(); // Margen de reserva
            $table->enum('system_condition', [
                'normal',
                'tight',
                'emergency',
                'surplus'
            ])->nullable();
            
            // Factores climáticos
            $table->decimal('temperature_celsius', 6, 2)->nullable(); // Temperatura
            $table->decimal('wind_generation_factor', 5, 4)->nullable(); // Factor de generación eólica
            $table->decimal('solar_generation_factor', 5, 4)->nullable(); // Factor de generación solar
            $table->decimal('hydro_reservoir_level', 5, 2)->nullable(); // Nivel embalses %
            
            // Precios de combustibles relacionados
            $table->decimal('natural_gas_price', 10, 4)->nullable(); // Precio gas natural
            $table->decimal('coal_price', 10, 4)->nullable(); // Precio carbón
            $table->decimal('oil_price', 10, 4)->nullable(); // Precio petróleo
            $table->decimal('co2_price', 10, 4)->nullable(); // Precio CO2
            
            // Información de la fuente de datos
            $table->string('data_source'); // Fuente de los datos
            $table->string('data_provider')->nullable(); // Proveedor de datos
            $table->enum('data_quality', [
                'official',        // Datos oficiales
                'preliminary',     // Datos preliminares
                'estimated',       // Datos estimados
                'forecasted'       // Datos pronosticados
            ])->default('official');
            
            // API/Feed information
            $table->string('feed_id')->nullable(); // ID del feed de datos
            $table->json('api_metadata')->nullable(); // Metadatos de la API
            $table->timestamp('data_retrieved_at')->nullable(); // Cuándo se obtuvieron los datos
            
            // Análisis técnico
            $table->decimal('sma_7', 12, 5)->nullable(); // Media móvil 7 períodos
            $table->decimal('sma_30', 12, 5)->nullable(); // Media móvil 30 períodos
            $table->decimal('ema_7', 12, 5)->nullable(); // Media exponencial 7 períodos
            $table->decimal('ema_30', 12, 5)->nullable(); // Media exponencial 30 períodos
            $table->decimal('rsi', 5, 2)->nullable(); // RSI
            
            // Alertas y umbrales
            $table->boolean('price_spike_detected')->default(false);
            $table->decimal('spike_threshold', 12, 5)->nullable();
            $table->boolean('unusual_volume_detected')->default(false);
            $table->json('market_alerts')->nullable();
            
            // Predicciones y forecasting
            $table->decimal('forecast_next_hour', 12, 5)->nullable();
            $table->decimal('forecast_next_day', 12, 5)->nullable();
            $table->decimal('forecast_confidence', 5, 2)->nullable(); // % confianza
            $table->string('forecast_model')->nullable(); // Modelo usado para predicción
            
            // Información regulatoria
            $table->boolean('regulated_price')->default(false);
            $table->string('regulatory_period')->nullable();
            $table->json('regulatory_adjustments')->nullable();
            
            // Estado del mercado
            $table->enum('market_status', [
                'open',            // Mercado abierto
                'closed',          // Mercado cerrado
                'pre_opening',     // Pre-apertura
                'auction',         // En subasta
                'suspended',       // Suspendido
                'maintenance'      // Mantenimiento
            ])->default('open');
            
            // Metadatos
            $table->json('additional_data')->nullable(); // Datos adicionales específicos del mercado
            $table->text('notes')->nullable();
            $table->boolean('is_holiday')->default(false); // Si es día festivo
            $table->string('day_type')->nullable(); // Tipo de día (weekend, holiday, working_day)
            
            $table->timestamps();
            
            // Índices para optimización de consultas
            $table->index(['market_name', 'commodity_type', 'price_date']);
            $table->index(['commodity_type', 'period_type', 'price_datetime']);
            $table->index(['country', 'commodity_type', 'price_date']);
            $table->index(['delivery_start_date', 'delivery_end_date']);
            $table->index(['price_datetime', 'period_type']);
            $table->index(['market_name', 'product_name', 'price_date']);
            $table->index(['data_source', 'price_datetime']);
            
            // Unique constraint para evitar duplicados
            $table->unique([
                'market_name', 
                'commodity_type', 
                'product_name', 
                'price_datetime', 
                'period_type', 
                'delivery_start_date'
            ], 'market_price_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_prices');
    }
};