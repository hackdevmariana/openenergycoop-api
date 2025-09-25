<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\User;
use App\Models\EnergyCooperative;
use Carbon\Carbon;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(10)->get();
        $cooperatives = EnergyCooperative::take(3)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Saltando InvoiceSeeder.');
            return;
        }

        $invoices = [
            [
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoice_code' => Invoice::generateInvoiceCode(),
                'type' => 'standard',
                'status' => 'paid',
                'user_id' => $users->first()->id,
                'energy_cooperative_id' => $cooperatives->first()?->id,
                'issue_date' => Carbon::now()->subDays(10),
                'due_date' => Carbon::now()->subDays(5),
                'service_period_start' => Carbon::now()->subDays(30),
                'service_period_end' => Carbon::now()->subDays(1),
                'sent_at' => Carbon::now()->subDays(10),
                'viewed_at' => Carbon::now()->subDays(9),
                'paid_at' => Carbon::now()->subDays(5),
                'subtotal' => 125.50,
                'tax_amount' => 26.36,
                'discount_amount' => 0.00,
                'total_amount' => 151.86,
                'paid_amount' => 151.86,
                'pending_amount' => 0.00,
                'currency' => 'EUR',
                'tax_rate' => 21.0,
                'tax_number' => 'ES12345678Z',
                'tax_type' => 'VAT',
                'customer_name' => $users->first()->name,
                'customer_email' => $users->first()->email,
                'billing_address' => 'Calle Mayor 123, 28001 Madrid, España',
                'customer_tax_id' => '12345678Z',
                'company_name' => 'OpenEnergyCoop S.L.',
                'company_address' => 'Avenida de la Energía 45, 28002 Madrid, España',
                'company_tax_id' => 'B12345678',
                'company_email' => 'facturacion@openenergycoop.com',
                'line_items' => [
                    [
                        'description' => 'Energía solar - 150 kWh',
                        'quantity' => 150,
                        'unit_price' => 0.81,
                        'total' => 121.50,
                        'tax_rate' => 21.0,
                        'tax_amount' => 25.52
                    ],
                    [
                        'description' => 'Servicio de distribución',
                        'quantity' => 1,
                        'unit_price' => 4.00,
                        'total' => 4.00,
                        'tax_rate' => 21.0,
                        'tax_amount' => 0.84
                    ]
                ],
                'description' => 'Factura por consumo de energía solar del período ' . Carbon::now()->subDays(30)->format('d/m/Y') . ' - ' . Carbon::now()->subDays(1)->format('d/m/Y'),
                'notes' => 'Gracias por confiar en nuestra energía renovable.',
                'terms_and_conditions' => 'Pago a 30 días. Intereses de demora según normativa vigente.',
                'energy_consumption_kwh' => 150.00,
                'energy_production_kwh' => 0.00,
                'energy_price_per_kwh' => 0.81,
                'meter_reading_start' => 1250.50,
                'meter_reading_end' => 1400.50,
                'payment_methods' => ['card', 'bank_transfer', 'paypal'],
                'payment_terms' => '30 días',
                'grace_period_days' => 5,
                'is_recurring' => false,
                'pdf_path' => '/invoices/2024/INV-2024-000001.pdf',
                'pdf_url' => 'https://openenergycoop.com/invoices/INV-2024-000001.pdf',
                'attachments' => ['meter_reading_certificate.pdf'],
                'created_by_id' => $users->first()->id,
                'customer_ip' => '192.168.1.100',
                'view_count' => 3,
                'last_viewed_at' => Carbon::now()->subDays(5),
                'activity_log' => [
                    ['action' => 'created', 'timestamp' => Carbon::now()->subDays(10)->toISOString(), 'user_id' => $users->first()->id],
                    ['action' => 'sent', 'timestamp' => Carbon::now()->subDays(10)->toISOString(), 'user_id' => $users->first()->id],
                    ['action' => 'viewed', 'timestamp' => Carbon::now()->subDays(9)->toISOString(), 'user_id' => $users->first()->id],
                    ['action' => 'paid', 'timestamp' => Carbon::now()->subDays(5)->toISOString(), 'user_id' => $users->first()->id]
                ],
                'metadata' => ['energy_type' => 'solar', 'billing_period' => 'monthly', 'meter_id' => 'MTR-001'],
                'language' => 'es',
                'template' => 'standard',
                'is_test' => false,
            ],
            [
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoice_code' => Invoice::generateInvoiceCode(),
                'type' => 'standard',
                'status' => 'sent',
                'user_id' => $users->skip(1)->first()->id,
                'energy_cooperative_id' => $cooperatives->skip(1)->first()?->id,
                'issue_date' => Carbon::now()->subDays(5),
                'due_date' => Carbon::now()->addDays(25),
                'service_period_start' => Carbon::now()->subDays(30),
                'service_period_end' => Carbon::now()->subDays(1),
                'sent_at' => Carbon::now()->subDays(5),
                'viewed_at' => null,
                'paid_at' => null,
                'subtotal' => 89.99,
                'tax_amount' => 18.90,
                'discount_amount' => 0.00,
                'total_amount' => 108.89,
                'paid_amount' => 0.00,
                'pending_amount' => 108.89,
                'currency' => 'EUR',
                'tax_rate' => 21.0,
                'tax_number' => 'ES87654321A',
                'tax_type' => 'VAT',
                'customer_name' => $users->skip(1)->first()->name,
                'customer_email' => $users->skip(1)->first()->email,
                'billing_address' => 'Plaza del Sol 45, 08001 Barcelona, España',
                'customer_tax_id' => '87654321A',
                'company_name' => 'OpenEnergyCoop S.L.',
                'company_address' => 'Avenida de la Energía 45, 28002 Madrid, España',
                'company_tax_id' => 'B12345678',
                'company_email' => 'facturacion@openenergycoop.com',
                'line_items' => [
                    [
                        'description' => 'Suscripción Plan Premium - Mensual',
                        'quantity' => 1,
                        'unit_price' => 89.99,
                        'total' => 89.99,
                        'tax_rate' => 21.0,
                        'tax_amount' => 18.90
                    ]
                ],
                'description' => 'Factura por suscripción Plan Premium del período ' . Carbon::now()->subDays(30)->format('d/m/Y') . ' - ' . Carbon::now()->subDays(1)->format('d/m/Y'),
                'notes' => 'Suscripción renovada automáticamente.',
                'terms_and_conditions' => 'Pago a 30 días. Cancelación con 30 días de antelación.',
                'payment_methods' => ['card', 'bank_transfer'],
                'payment_terms' => '30 días',
                'grace_period_days' => 5,
                'is_recurring' => true,
                'recurring_frequency' => 'monthly',
                'next_billing_date' => Carbon::now()->addMonth(),
                'recurring_count' => 1,
                'max_recurring_count' => 12,
                'pdf_path' => '/invoices/2024/INV-2024-000002.pdf',
                'pdf_url' => 'https://openenergycoop.com/invoices/INV-2024-000002.pdf',
                'attachments' => [],
                'created_by_id' => $users->first()->id,
                'customer_ip' => '192.168.1.101',
                'view_count' => 0,
                'last_viewed_at' => null,
                'activity_log' => [
                    ['action' => 'created', 'timestamp' => Carbon::now()->subDays(5)->toISOString(), 'user_id' => $users->first()->id],
                    ['action' => 'sent', 'timestamp' => Carbon::now()->subDays(5)->toISOString(), 'user_id' => $users->first()->id]
                ],
                'metadata' => ['plan' => 'premium', 'billing_cycle' => 'monthly', 'auto_renewal' => true],
                'language' => 'es',
                'template' => 'subscription',
                'is_test' => false,
            ],
            [
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoice_code' => Invoice::generateInvoiceCode(),
                'type' => 'standard',
                'status' => 'overdue',
                'user_id' => $users->skip(2)->first()->id,
                'energy_cooperative_id' => $cooperatives->skip(2)->first()?->id,
                'issue_date' => Carbon::now()->subDays(35),
                'due_date' => Carbon::now()->subDays(5),
                'service_period_start' => Carbon::now()->subDays(60),
                'service_period_end' => Carbon::now()->subDays(30),
                'sent_at' => Carbon::now()->subDays(35),
                'viewed_at' => Carbon::now()->subDays(30),
                'paid_at' => null,
                'subtotal' => 75.25,
                'tax_amount' => 15.80,
                'discount_amount' => 0.00,
                'total_amount' => 91.05,
                'paid_amount' => 0.00,
                'pending_amount' => 91.05,
                'currency' => 'EUR',
                'tax_rate' => 21.0,
                'tax_number' => 'ES11223344B',
                'tax_type' => 'VAT',
                'customer_name' => $users->skip(2)->first()->name,
                'customer_email' => $users->skip(2)->first()->email,
                'billing_address' => 'Calle Valencia 78, 46002 Valencia, España',
                'customer_tax_id' => '11223344B',
                'company_name' => 'OpenEnergyCoop S.L.',
                'company_address' => 'Avenida de la Energía 45, 28002 Madrid, España',
                'company_tax_id' => 'B12345678',
                'company_email' => 'facturacion@openenergycoop.com',
                'line_items' => [
                    [
                        'description' => 'Energía eólica - 100 kWh',
                        'quantity' => 100,
                        'unit_price' => 0.75,
                        'total' => 75.00,
                        'tax_rate' => 21.0,
                        'tax_amount' => 15.75
                    ],
                    [
                        'description' => 'Servicio de distribución',
                        'quantity' => 1,
                        'unit_price' => 0.25,
                        'total' => 0.25,
                        'tax_rate' => 21.0,
                        'tax_amount' => 0.05
                    ]
                ],
                'description' => 'Factura por consumo de energía eólica del período ' . Carbon::now()->subDays(60)->format('d/m/Y') . ' - ' . Carbon::now()->subDays(30)->format('d/m/Y'),
                'notes' => 'Factura vencida. Por favor, proceda al pago.',
                'terms_and_conditions' => 'Pago a 30 días. Intereses de demora según normativa vigente.',
                'energy_consumption_kwh' => 100.00,
                'energy_production_kwh' => 0.00,
                'energy_price_per_kwh' => 0.75,
                'meter_reading_start' => 2100.25,
                'meter_reading_end' => 2200.25,
                'payment_methods' => ['card', 'bank_transfer', 'paypal'],
                'payment_terms' => '30 días',
                'grace_period_days' => 5,
                'is_recurring' => false,
                'pdf_path' => '/invoices/2024/INV-2024-000003.pdf',
                'pdf_url' => 'https://openenergycoop.com/invoices/INV-2024-000003.pdf',
                'attachments' => ['meter_reading_certificate.pdf', 'payment_reminder.pdf'],
                'created_by_id' => $users->first()->id,
                'customer_ip' => '192.168.1.102',
                'view_count' => 5,
                'last_viewed_at' => Carbon::now()->subDays(2),
                'activity_log' => [
                    ['action' => 'created', 'timestamp' => Carbon::now()->subDays(35)->toISOString(), 'user_id' => $users->first()->id],
                    ['action' => 'sent', 'timestamp' => Carbon::now()->subDays(35)->toISOString(), 'user_id' => $users->first()->id],
                    ['action' => 'viewed', 'timestamp' => Carbon::now()->subDays(30)->toISOString(), 'user_id' => $users->skip(2)->first()->id],
                    ['action' => 'overdue', 'timestamp' => Carbon::now()->subDays(5)->toISOString(), 'user_id' => $users->first()->id],
                    ['action' => 'reminder_sent', 'timestamp' => Carbon::now()->subDays(2)->toISOString(), 'user_id' => $users->first()->id]
                ],
                'metadata' => ['energy_type' => 'wind', 'billing_period' => 'monthly', 'meter_id' => 'MTR-002', 'overdue_days' => 5],
                'language' => 'es',
                'template' => 'standard',
                'is_test' => false,
            ],
            [
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoice_code' => Invoice::generateInvoiceCode(),
                'type' => 'credit_note',
                'status' => 'sent',
                'user_id' => $users->skip(3)->first()->id,
                'energy_cooperative_id' => $cooperatives->first()?->id,
                'parent_invoice_id' => 1, // Referencia a la primera factura
                'issue_date' => Carbon::now()->subDays(2),
                'due_date' => Carbon::now()->addDays(28),
                'service_period_start' => Carbon::now()->subDays(10),
                'service_period_end' => Carbon::now()->subDays(1),
                'sent_at' => Carbon::now()->subDays(2),
                'viewed_at' => null,
                'paid_at' => null,
                'subtotal' => -25.00,
                'tax_amount' => -5.25,
                'discount_amount' => 0.00,
                'total_amount' => -30.25,
                'paid_amount' => 0.00,
                'pending_amount' => -30.25,
                'currency' => 'EUR',
                'tax_rate' => 21.0,
                'tax_number' => 'ES99887766C',
                'tax_type' => 'VAT',
                'customer_name' => $users->skip(3)->first()->name,
                'customer_email' => $users->skip(3)->first()->email,
                'billing_address' => 'Avenida Andalucía 12, 41001 Sevilla, España',
                'customer_tax_id' => '99887766C',
                'company_name' => 'OpenEnergyCoop S.L.',
                'company_address' => 'Avenida de la Energía 45, 28002 Madrid, España',
                'company_tax_id' => 'B12345678',
                'company_email' => 'facturacion@openenergycoop.com',
                'line_items' => [
                    [
                        'description' => 'Reembolso por interrupción de servicio',
                        'quantity' => 1,
                        'unit_price' => -25.00,
                        'total' => -25.00,
                        'tax_rate' => 21.0,
                        'tax_amount' => -5.25
                    ]
                ],
                'description' => 'Nota de crédito por interrupción de servicio energético',
                'notes' => 'Reembolso por interrupción de servicio del 15/01/2024 al 20/01/2024.',
                'terms_and_conditions' => 'Este crédito será aplicado a la próxima factura.',
                'payment_methods' => ['credit_to_account'],
                'payment_terms' => 'Inmediato',
                'grace_period_days' => 0,
                'is_recurring' => false,
                'pdf_path' => '/invoices/2024/INV-2024-000004.pdf',
                'pdf_url' => 'https://openenergycoop.com/invoices/INV-2024-000004.pdf',
                'attachments' => ['service_interruption_report.pdf'],
                'created_by_id' => $users->first()->id,
                'customer_ip' => '192.168.1.103',
                'view_count' => 0,
                'last_viewed_at' => null,
                'activity_log' => [
                    ['action' => 'created', 'timestamp' => Carbon::now()->subDays(2)->toISOString(), 'user_id' => $users->first()->id],
                    ['action' => 'sent', 'timestamp' => Carbon::now()->subDays(2)->toISOString(), 'user_id' => $users->first()->id]
                ],
                'metadata' => ['credit_type' => 'service_interruption', 'original_invoice_id' => 1, 'interruption_days' => 5],
                'language' => 'es',
                'template' => 'credit_note',
                'is_test' => false,
            ],
            [
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoice_code' => Invoice::generateInvoiceCode(),
                'type' => 'proforma',
                'status' => 'draft',
                'user_id' => $users->skip(4)->first()->id,
                'energy_cooperative_id' => $cooperatives->skip(1)->first()?->id,
                'issue_date' => Carbon::now()->subDays(1),
                'due_date' => Carbon::now()->addDays(30),
                'service_period_start' => Carbon::now()->addDays(1),
                'service_period_end' => Carbon::now()->addDays(30),
                'sent_at' => null,
                'viewed_at' => null,
                'paid_at' => null,
                'subtotal' => 200.00,
                'tax_amount' => 42.00,
                'discount_amount' => 20.00,
                'total_amount' => 222.00,
                'paid_amount' => 0.00,
                'pending_amount' => 222.00,
                'currency' => 'EUR',
                'tax_rate' => 21.0,
                'tax_number' => 'ES55667788D',
                'tax_type' => 'VAT',
                'customer_name' => $users->skip(4)->first()->name,
                'customer_email' => $users->skip(4)->first()->email,
                'billing_address' => 'Calle Bilbao 34, 48001 Bilbao, España',
                'customer_tax_id' => '55667788D',
                'company_name' => 'OpenEnergyCoop S.L.',
                'company_address' => 'Avenida de la Energía 45, 28002 Madrid, España',
                'company_tax_id' => 'B12345678',
                'company_email' => 'facturacion@openenergycoop.com',
                'line_items' => [
                    [
                        'description' => 'Instalación sistema solar 5kW',
                        'quantity' => 1,
                        'unit_price' => 200.00,
                        'total' => 200.00,
                        'tax_rate' => 21.0,
                        'tax_amount' => 42.00
                    ]
                ],
                'description' => 'Presupuesto para instalación de sistema solar fotovoltaico',
                'notes' => 'Presupuesto válido por 30 días. Incluye instalación y garantía de 25 años.',
                'terms_and_conditions' => 'Presupuesto no vinculante. Precio final sujeto a condiciones del sitio.',
                'payment_methods' => ['card', 'bank_transfer'],
                'payment_terms' => '50% anticipo, 50% al finalizar',
                'grace_period_days' => 0,
                'is_recurring' => false,
                'pdf_path' => null,
                'pdf_url' => null,
                'attachments' => ['technical_specification.pdf', 'warranty_terms.pdf'],
                'created_by_id' => $users->first()->id,
                'customer_ip' => '192.168.1.104',
                'view_count' => 0,
                'last_viewed_at' => null,
                'activity_log' => [
                    ['action' => 'created', 'timestamp' => Carbon::now()->subDays(1)->toISOString(), 'user_id' => $users->first()->id]
                ],
                'metadata' => ['quote_type' => 'solar_installation', 'system_size' => '5kW', 'validity_days' => 30],
                'language' => 'es',
                'template' => 'proforma',
                'is_test' => false,
            ],
        ];

        foreach ($invoices as $invoice) {
            Invoice::firstOrCreate(
                ['invoice_code' => $invoice['invoice_code']],
                $invoice
            );
        }

        $this->command->info('✅ InvoiceSeeder ejecutado correctamente');
        $this->command->info('📊 Facturas creadas: ' . count($invoices));
        $this->command->info('📄 Tipos: Estándar (3), Nota de crédito (1), Proforma (1)');
        $this->command->info('📈 Estados: Pagada (1), Enviada (1), Vencida (1), Borrador (1), Enviada (1)');
        $this->command->info('💰 Monto total: €' . number_format(collect($invoices)->sum('total_amount'), 2));
        $this->command->info('⚡ Energía total: ' . number_format(collect($invoices)->sum('energy_consumption_kwh'), 2) . ' kWh');
        $this->command->info('🏢 Clientes: Madrid, Barcelona, Valencia, Sevilla, Bilbao');
        $this->command->info('🔄 Facturas recurrentes y no recurrentes incluidas');
    }
}
