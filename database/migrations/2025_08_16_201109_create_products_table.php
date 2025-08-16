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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', [
                'physical', 
                'energy_kwh', 
                'production_right', 
                'storage_capacity', 
                'mining_ths', 
                'energy_bond'
            ]);
            
            // Precios
            $table->decimal('base_purchase_price', 10, 2)->nullable();
            $table->decimal('base_sale_price', 10, 2)->nullable();
            
            // Comisiones
            $table->enum('commission_type', ['percentage', 'fixed', 'none'])->default('none');
            $table->decimal('commission_value', 8, 4)->nullable();
            
            // Recargos
            $table->enum('surcharge_type', ['percentage', 'fixed', 'none'])->default('none');
            $table->decimal('surcharge_value', 8, 4)->nullable();
            
            // Unidad y estado
            $table->string('unit', 50)->default('unit'); // kWh, TH/s, unit, etc.
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            // Metadata específica por tipo
            $table->json('metadata')->nullable();
            
            // Información de sostenibilidad
            $table->decimal('renewable_percentage', 5, 2)->nullable(); // % renovable
            $table->decimal('carbon_footprint', 8, 4)->nullable(); // Huella de carbono
            $table->string('geographical_zone')->nullable(); // Zona geográfica
            
            // Información adicional
            $table->string('image_path')->nullable();
            $table->json('features')->nullable(); // Características técnicas
            $table->integer('stock_quantity')->nullable(); // Para productos físicos
            $table->decimal('weight', 8, 3)->nullable(); // Peso en kg
            $table->json('dimensions')->nullable(); // Dimensiones
            $table->text('warranty_info')->nullable(); // Información de garantía
            $table->integer('estimated_lifespan_years')->nullable(); // Vida útil estimada
            
            // SEO y marketing
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('keywords')->nullable(); // Keywords para búsqueda
            
            $table->timestamps();
            
            // Índices
            $table->index('provider_id');
            $table->index('type');
            $table->index('is_active');
            $table->index('renewable_percentage');
            $table->index('geographical_zone');
            $table->index(['start_date', 'end_date']);
            $table->fullText(['name', 'description']); // Búsqueda de texto completo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};