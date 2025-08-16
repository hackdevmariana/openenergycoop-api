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
        Schema::create('user_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // Cantidad y fechas
            $table->decimal('quantity', 12, 4);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            
            // Origen y estado
            $table->enum('source_type', [
                'purchase', 'transfer', 'bonus', 'donation', 'reward', 
                'conversion', 'mining', 'production', 'referral'
            ]);
            $table->enum('status', ['active', 'expired', 'pending', 'suspended', 'transferred']);
            
            // Valor actual
            $table->decimal('current_value', 12, 2)->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable(); // Precio de compra original
            
            // Rendimiento para derechos de producción
            $table->decimal('daily_yield', 8, 4)->nullable();
            $table->decimal('total_yield_generated', 12, 4)->default(0); // Acumulado generado
            
            // Eficiencia y mantenimiento
            $table->decimal('efficiency_rating', 5, 2)->nullable(); // 0-100%
            $table->decimal('maintenance_cost', 8, 2)->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            
            // Reinversión automática
            $table->boolean('auto_reinvest')->default(false);
            $table->decimal('reinvest_threshold', 8, 2)->nullable(); // Umbral para reinvertir
            $table->decimal('reinvest_percentage', 5, 2)->nullable(); // % a reinvertir
            
            // Transferencia y delegación
            $table->boolean('is_transferable')->default(true);
            $table->boolean('is_delegatable')->default(false);
            $table->foreignId('delegated_to_user_id')->nullable()->constrained('users');
            
            // Metadata específica
            $table->json('metadata')->nullable();
            
            // Información de rendimiento
            $table->decimal('estimated_annual_return', 8, 4)->nullable(); // ROI estimado
            $table->decimal('actual_annual_return', 8, 4)->nullable(); // ROI real
            $table->json('performance_history')->nullable(); // Historial de rendimiento
            
            // Alertas y notificaciones
            $table->boolean('notifications_enabled')->default(true);
            $table->json('alert_preferences')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index('user_id');
            $table->index('product_id');
            $table->index('status');
            $table->index('source_type');
            $table->index(['start_date', 'end_date']);
            $table->index('auto_reinvest');
            $table->index('delegated_to_user_id');
            
            // Índice compuesto para consultas frecuentes
            $table->index(['user_id', 'status']);
            $table->index(['product_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_assets');
    }
};