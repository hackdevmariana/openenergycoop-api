<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EnergyContract;
use App\Models\User;
use App\Models\Provider;
use App\Models\Product;
use Carbon\Carbon;

class EnergyContractSeeder extends Seeder
{
    private $contractCounter = 1;

    public function run(): void
    {
        $this->command->info('⚡ Creando contratos energéticos...');

        $users = User::all();
        $providers = Provider::all();
        $products = Product::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        if ($providers->isEmpty()) {
            $this->command->error('❌ No hay proveedores disponibles.');
            return;
        }

        if ($products->isEmpty()) {
            $this->command->error('❌ No hay productos disponibles.');
            return;
        }

        // Limpiar contratos existentes
        EnergyContract::query()->delete();

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("🏢 Proveedores disponibles: {$providers->count()}");
        $this->command->info("📦 Productos disponibles: {$products->count()}");

        // Crear diferentes tipos de contratos energéticos
        $this->createSupplyContracts($users, $providers, $products);
        $this->createGenerationContracts($users, $providers, $products);
        $this->createStorageContracts($users, $providers, $products);
        $this->createHybridContracts($users, $providers, $products);

        $this->command->info('✅ EnergyContractSeeder completado. Se crearon ' . EnergyContract::count() . ' contratos energéticos.');
    }

    private function createSupplyContracts($users, $providers, $products): void
    {
        $this->command->info('🔌 Creando contratos de suministro...');

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $this->createContract($user, $providers->random(), $products->random(), 'supply', [
                    'name' => 'Contrato de Suministro ' . fake()->words(2, true),
                    'description' => 'Contrato para el suministro de energía eléctrica',
                    'total_value' => fake()->randomFloat(2, 5000, 50000),
                    'monthly_payment' => fake()->randomFloat(2, 100, 1500),
                    'contracted_power' => fake()->randomFloat(2, 3.45, 15),
                    'estimated_annual_consumption' => fake()->randomFloat(2, 3000, 25000),
                    'guaranteed_supply_percentage' => fake()->randomFloat(2, 95, 100),
                    'green_energy_percentage' => fake()->randomFloat(2, 30, 100),
                    'billing_frequency' => fake()->randomElement(['monthly', 'quarterly']),
                    'auto_renewal' => fake()->boolean(70),
                    'carbon_neutral' => fake()->boolean(40),
                    'special_clauses' => [
                        'Descuento por consumo eficiente',
                        'Garantía de suministro renovable',
                        'Bonificación por instalación de paneles solares'
                    ],
                    'sustainability_certifications' => [
                        'Certificado Energía Renovable',
                        'ISO 14001 - Gestión Ambiental'
                    ],
                    'custom_fields' => [
                        'smart_meter_included' => true,
                        'priority_support' => fake()->boolean(30),
                        'installation_support' => fake()->boolean(20)
                    ]
                ]);
            }
        }

        $this->command->info("   ✅ Contratos de suministro creados");
    }

    private function createGenerationContracts($users, $providers, $products): void
    {
        $this->command->info('☀️ Creando contratos de generación...');

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 2); $i++) {
                $this->createContract($user, $providers->random(), $products->random(), 'generation', [
                    'name' => 'Contrato de Generación ' . fake()->words(2, true),
                    'description' => 'Contrato para la generación de energía renovable',
                    'total_value' => fake()->randomFloat(2, 15000, 100000),
                    'monthly_payment' => fake()->randomFloat(2, 200, 3000),
                    'contracted_power' => fake()->randomFloat(2, 5, 50),
                    'estimated_annual_consumption' => fake()->randomFloat(2, 8000, 40000),
                    'guaranteed_supply_percentage' => fake()->randomFloat(2, 90, 100),
                    'green_energy_percentage' => fake()->randomFloat(2, 80, 100),
                    'billing_frequency' => fake()->randomElement(['monthly', 'quarterly', 'semi_annual']),
                    'auto_renewal' => fake()->boolean(80),
                    'carbon_neutral' => fake()->boolean(80),
                    'special_clauses' => [
                        'Compra garantizada de excedentes',
                        'Tarifa premium por energía verde',
                        'Incentivos por eficiencia energética'
                    ],
                    'sustainability_certifications' => [
                        'Certificado Energía Renovable',
                        'Carbon Neutral Certified',
                        'LEED Certified'
                    ],
                    'custom_fields' => [
                        'battery_storage_included' => fake()->boolean(50),
                        'monitoring_system' => true,
                        'maintenance_included' => fake()->boolean(60)
                    ]
                ]);
            }
        }

        $this->command->info("   ✅ Contratos de generación creados");
    }

    private function createStorageContracts($users, $providers, $products): void
    {
        $this->command->info('🔋 Creando contratos de almacenamiento...');

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 2); $i++) {
                $this->createContract($user, $providers->random(), $products->random(), 'storage', [
                    'name' => 'Contrato de Almacenamiento ' . fake()->words(2, true),
                    'description' => 'Contrato para servicios de almacenamiento de energía',
                    'total_value' => fake()->randomFloat(2, 8000, 75000),
                    'monthly_payment' => fake()->randomFloat(2, 150, 2000),
                    'contracted_power' => fake()->randomFloat(2, 2, 25),
                    'estimated_annual_consumption' => fake()->randomFloat(2, 5000, 30000),
                    'guaranteed_supply_percentage' => fake()->randomFloat(2, 85, 100),
                    'green_energy_percentage' => fake()->randomFloat(2, 60, 100),
                    'billing_frequency' => fake()->randomElement(['monthly', 'quarterly']),
                    'auto_renewal' => fake()->boolean(60),
                    'carbon_neutral' => fake()->boolean(60),
                    'special_clauses' => [
                        'Servicio de gestión de carga',
                        'Optimización de tarifas',
                        'Participación en programas de respuesta a la demanda'
                    ],
                    'sustainability_certifications' => [
                        'ISO 14001 - Gestión Ambiental',
                        'Certificado de Eficiencia Energética'
                    ],
                    'custom_fields' => [
                        'peak_shaving' => true,
                        'load_balancing' => true,
                        'emergency_backup' => fake()->boolean(80)
                    ]
                ]);
            }
        }

        $this->command->info("   ✅ Contratos de almacenamiento creados");
    }

    private function createHybridContracts($users, $providers, $products): void
    {
        $this->command->info('🔗 Creando contratos híbridos...');

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 2); $i++) {
                $this->createContract($user, $providers->random(), $products->random(), 'hybrid', [
                    'name' => 'Contrato Híbrido ' . fake()->words(2, true),
                    'description' => 'Contrato combinado de suministro, generación y almacenamiento',
                    'total_value' => fake()->randomFloat(2, 20000, 150000),
                    'monthly_payment' => fake()->randomFloat(2, 300, 4000),
                    'contracted_power' => fake()->randomFloat(2, 5, 75),
                    'estimated_annual_consumption' => fake()->randomFloat(2, 10000, 50000),
                    'guaranteed_supply_percentage' => fake()->randomFloat(2, 92, 100),
                    'green_energy_percentage' => fake()->randomFloat(2, 70, 100),
                    'billing_frequency' => fake()->randomElement(['monthly', 'quarterly', 'semi_annual']),
                    'auto_renewal' => fake()->boolean(85),
                    'carbon_neutral' => fake()->boolean(70),
                    'special_clauses' => [
                        'Sistema integrado de gestión energética',
                        'Tarifa única para todos los servicios',
                        'Garantía de independencia energética',
                        'Optimización automática de costes'
                    ],
                    'sustainability_certifications' => [
                        'Certificado Energía Renovable',
                        'Carbon Neutral Certified',
                        'ISO 14001 - Gestión Ambiental',
                        'LEED Certified'
                    ],
                    'custom_fields' => [
                        'integrated_management' => true,
                        'smart_home_integration' => fake()->boolean(70),
                        'predictive_analytics' => true,
                        'energy_optimization' => true
                    ]
                ]);
            }
        }

        $this->command->info("   ✅ Contratos híbridos creados");
    }

    private function createContract($user, $provider, $product, $type, $additionalData = []): void
    {
        $startDate = Carbon::now()->subDays(rand(30, 365));
        $endDate = $startDate->copy()->addYears(rand(1, 5));
        $signedDate = fake()->optional(0.8)->dateTimeBetween($startDate->format('Y-m-d'), 'now');
        $activationDate = $signedDate ? fake()->optional(0.7)->dateTimeBetween($signedDate->format('Y-m-d'), 'now') : null;

        $totalValue = $additionalData['total_value'] ?? fake()->randomFloat(2, 5000, 100000);
        $monthlyPayment = $additionalData['monthly_payment'] ?? fake()->randomFloat(2, 100, 2000);
        $contractedPower = $additionalData['contracted_power'] ?? fake()->randomFloat(2, 3.45, 50);
        $estimatedConsumption = $additionalData['estimated_annual_consumption'] ?? fake()->randomFloat(2, 3000, 40000);

        EnergyContract::create(array_merge([
            'user_id' => $user->id,
            'provider_id' => $provider->id,
            'product_id' => $product->id,
            'contract_number' => 'CTR-' . strtoupper($type) . '-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($this->contractCounter++, 4, '0', STR_PAD_LEFT),
            'name' => $additionalData['name'] ?? 'Contrato ' . ucfirst($type) . ' ' . fake()->words(2, true),
            'description' => $additionalData['description'] ?? 'Contrato de energía tipo ' . $type,
            'type' => $type,
            'status' => fake()->randomElement(['active', 'active', 'active', 'pending', 'draft', 'suspended']),
            'total_value' => $totalValue,
            'monthly_payment' => $monthlyPayment,
            'currency' => 'EUR',
            'deposit_amount' => fake()->randomFloat(2, 200, 3000),
            'deposit_paid' => fake()->boolean(80),
            'contracted_power' => $contractedPower,
            'estimated_annual_consumption' => $estimatedConsumption,
            'guaranteed_supply_percentage' => $additionalData['guaranteed_supply_percentage'] ?? fake()->randomFloat(2, 90, 100),
            'green_energy_percentage' => $additionalData['green_energy_percentage'] ?? fake()->randomFloat(2, 40, 100),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'signed_date' => $signedDate,
            'activation_date' => $activationDate,
            'terms_conditions' => fake()->paragraphs(3, true),
            'special_clauses' => $additionalData['special_clauses'] ?? [
                'Cláusula de revisión anual de precios',
                'Garantía de suministro renovable'
            ],
            'auto_renewal' => $additionalData['auto_renewal'] ?? fake()->boolean(70),
            'renewal_period_months' => fake()->randomElement([12, 24, 36]),
            'early_termination_fee' => fake()->randomFloat(2, 0, 1000),
            'billing_frequency' => $additionalData['billing_frequency'] ?? fake()->randomElement(['monthly', 'quarterly', 'semi_annual', 'annual']),
            'next_billing_date' => fake()->optional()->dateTimeBetween('+1 week', '+2 months'),
            'last_billing_date' => fake()->optional()->dateTimeBetween('-3 months', '-1 week'),
            'performance_metrics' => json_encode([
                'uptime_percentage' => fake()->randomFloat(2, 95, 99.9),
                'response_time_minutes' => fake()->randomFloat(2, 5, 60),
                'customer_satisfaction' => fake()->randomFloat(2, 7.5, 10.0),
                'energy_efficiency_score' => fake()->randomFloat(2, 80, 100)
            ]),
            'current_satisfaction_score' => fake()->randomFloat(2, 7.0, 10.0),
            'total_claims' => fake()->numberBetween(0, 5),
            'resolved_claims' => fake()->numberBetween(0, 5),
            'estimated_co2_reduction' => $additionalData['estimated_co2_reduction'] ?? fake()->randomFloat(2, 500, 15000),
            'sustainability_certifications' => $additionalData['sustainability_certifications'] ?? [
                'Certificado Energía Renovable',
                'ISO 14001 - Gestión Ambiental'
            ],
            'carbon_neutral' => $additionalData['carbon_neutral'] ?? fake()->boolean(50),
            'custom_fields' => $additionalData['custom_fields'] ?? [
                'smart_meter_included' => fake()->boolean(70),
                'priority_support' => fake()->boolean(30),
                'installation_support' => fake()->boolean(40)
            ],
            'attachments' => json_encode([
                'contract_pdf' => fake()->optional()->url(),
                'technical_specifications' => fake()->optional()->url(),
                'installation_guide' => fake()->optional()->url()
            ]),
            'notes' => fake()->optional()->paragraph(),
            'approved_at' => fake()->optional()->dateTimeBetween('-6 months', '-1 day'),
            'approved_by' => fake()->optional()->randomElement([1, 2, 3, 4, 5]),
            'terminated_at' => null,
            'terminated_by' => null,
            'termination_reason' => null,
            'created_at' => $startDate,
            'updated_at' => Carbon::now()->subDays(rand(0, 30))
        ], $additionalData));
    }
}
