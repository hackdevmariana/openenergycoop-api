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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            
            // Identificación y numeración
            $table->string('invoice_number')->unique()->index(); // INV-2024-001
            $table->string('invoice_code')->unique()->index(); // Código interno
            $table->enum('type', ['standard', 'proforma', 'credit_note', 'debit_note', 'recurring'])->default('standard');
            $table->enum('status', ['draft', 'sent', 'viewed', 'overdue', 'paid', 'partially_paid', 'cancelled', 'refunded'])->default('draft');
            
            // Relaciones principales
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Cliente
            $table->foreignId('energy_cooperative_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('parent_invoice_id')->nullable()->constrained('invoices')->onDelete('set null'); // Para credit notes
            
            // Fechas importantes
            $table->date('issue_date'); // Fecha de emisión
            $table->date('due_date'); // Fecha de vencimiento
            $table->date('service_period_start')->nullable(); // Período de servicio
            $table->date('service_period_end')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Montos y cálculos
            $table->decimal('subtotal', 12, 4); // Subtotal sin impuestos
            $table->decimal('tax_amount', 10, 4)->default(0); // Impuestos
            $table->decimal('discount_amount', 10, 4)->default(0); // Descuentos
            $table->decimal('total_amount', 12, 4); // Total final
            $table->decimal('paid_amount', 12, 4)->default(0); // Cantidad pagada
            $table->decimal('pending_amount', 12, 4); // Cantidad pendiente
            $table->string('currency', 3)->default('EUR');
            
            // Información fiscal
            $table->decimal('tax_rate', 5, 4)->default(0); // Tipo de IVA (0.21 = 21%)
            $table->string('tax_number')->nullable(); // Número de identificación fiscal
            $table->string('tax_type')->nullable(); // Tipo de impuesto
            
            // Información del cliente (snapshot)
            $table->string('customer_name');
            $table->string('customer_email');
            $table->text('billing_address')->nullable();
            $table->string('customer_tax_id')->nullable();
            
            // Información de la empresa (snapshot)
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_tax_id')->nullable();
            $table->string('company_email')->nullable();
            
            // Detalles del contenido
            $table->json('line_items'); // Items de la factura
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();
            
            // Información energética específica
            $table->decimal('energy_consumption_kwh', 10, 4)->nullable();
            $table->decimal('energy_production_kwh', 10, 4)->nullable();
            $table->decimal('energy_price_per_kwh', 8, 6)->nullable();
            $table->string('meter_reading_start')->nullable();
            $table->string('meter_reading_end')->nullable();
            
            // Configuración de pagos
            $table->json('payment_methods')->nullable(); // Métodos de pago aceptados
            $table->string('payment_terms')->nullable(); // Términos de pago
            $table->integer('grace_period_days')->default(0); // Días de gracia
            
            // Facturación recurrente
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_frequency', ['monthly', 'quarterly', 'yearly'])->nullable();
            $table->date('next_billing_date')->nullable();
            $table->integer('recurring_count')->default(0); // Número de recurrencias
            $table->integer('max_recurring_count')->nullable(); // Máximo de recurrencias
            
            // Archivos y documentos
            $table->string('pdf_path')->nullable(); // Ruta del PDF generado
            $table->string('pdf_url')->nullable(); // URL del PDF
            $table->json('attachments')->nullable(); // Archivos adjuntos
            
            // Auditoría y tracking
            $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->ipAddress('customer_ip')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->json('activity_log')->nullable(); // Log de actividades
            
            // Metadata y configuración
            $table->json('metadata')->nullable();
            $table->string('language', 2)->default('es'); // Idioma de la factura
            $table->string('template')->nullable(); // Plantilla utilizada
            $table->boolean('is_test')->default(false);
            
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['user_id', 'status']);
            $table->index(['status', 'due_date']);
            $table->index(['invoice_number']);
            $table->index(['issue_date', 'due_date']);
            $table->index(['is_recurring', 'next_billing_date']);
            $table->index(['energy_cooperative_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};