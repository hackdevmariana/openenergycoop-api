<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserSubscription;
use App\Models\User;
use App\Models\EnergyCooperative;
use App\Models\Provider;
use Carbon\Carbon;

class UserSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔌 Creando suscripciones de usuarios...');

        $users = User::take(5)->get();
        $cooperatives = EnergyCooperative::take(3)->get();
        $providers = Provider::take(3)->get();

        if ($users->isEmpty()) {
            $this->command->warn('   ⚠️ No hay usuarios disponibles. Creando usuarios de prueba...');
            $users = User::factory(5)->create();
        }

        if ($cooperatives->isEmpty()) {
            $this->command->warn('   ⚠️ No hay cooperativas disponibles. Saltando cooperativas...');
        }

        if ($providers->isEmpty()) {
            $this->command->warn('   ⚠️ No hay proveedores disponibles. Saltando proveedores...');
        }

        // Crear una suscripción por usuario con diferentes tipos y estados
        $subscriptionData = [
            [
                'subscription_type' => 'energy_plan',
                'plan_name' => 'Plan Energético Premium',
                'status' => 'active',
                'service_category' => 'residential',
            ],
            [
                'subscription_type' => 'solar_panel',
                'plan_name' => 'Panel Solar Básico',
                'status' => 'active',
                'service_category' => 'residential',
            ],
            [
                'subscription_type' => 'battery_storage',
                'plan_name' => 'Almacenamiento de Batería Pro',
                'status' => 'pending',
                'service_category' => 'commercial',
            ],
            [
                'subscription_type' => 'smart_meter',
                'plan_name' => 'Medidor Inteligente Enterprise',
                'status' => 'paused',
                'service_category' => 'industrial',
            ],
            [
                'subscription_type' => 'energy_monitoring',
                'plan_name' => 'Monitoreo Energético Avanzado',
                'status' => 'cancelled',
                'service_category' => 'community',
            ],
        ];

        foreach ($users as $index => $user) {
            if (isset($subscriptionData[$index])) {
                $data = $subscriptionData[$index];
                
                UserSubscription::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'subscription_type' => $data['subscription_type'],
                        'service_category' => $data['service_category'],
                    ],
                    [
                        'energy_cooperative_id' => $cooperatives->isNotEmpty() ? $cooperatives->random()->id : null,
                        'provider_id' => $providers->isNotEmpty() ? $providers->random()->id : null,
                        'plan_name' => $data['plan_name'],
                        'plan_description' => fake()->paragraph(),
                        'status' => $data['status'],
                        'start_date' => Carbon::now()->subMonths(rand(1, 12)),
                        'end_date' => $data['status'] === 'cancelled' ? Carbon::now()->subDays(rand(1, 30)) : Carbon::now()->addYears(rand(1, 5)),
                        'trial_end_date' => fake()->optional()->dateTimeBetween('-1 month', '+1 month'),
                        'next_billing_date' => Carbon::now()->addDays(rand(1, 30)),
                        'billing_frequency' => fake()->randomElement(['monthly', 'quarterly', 'annual']),
                        'price' => fake()->randomFloat(2, 25, 500),
                        'currency' => 'EUR',
                        'discount_percentage' => fake()->optional()->randomFloat(2, 5, 25),
                        'energy_allowance_kwh' => fake()->randomFloat(2, 100, 2000),
                        'overage_rate_per_kwh' => fake()->randomFloat(4, 0.05, 0.25),
                        'includes_renewable_energy' => fake()->boolean(80),
                        'renewable_percentage' => fake()->randomFloat(2, 50, 100),
                        'auto_renewal' => fake()->boolean(70),
                        'current_period_usage_kwh' => fake()->randomFloat(2, 50, 800),
                        'total_usage_kwh' => fake()->randomFloat(2, 200, 5000),
                        'current_period_cost' => fake()->randomFloat(2, 15, 200),
                        'total_cost_paid' => fake()->randomFloat(2, 100, 2000),
                        'billing_cycles_completed' => rand(1, 24),
                        'loyalty_points' => rand(0, 1000),
                        'payment_method' => fake()->randomElement(['credit_card', 'bank_transfer', 'paypal']),
                        'payment_status' => $data['status'] === 'pending' ? 'pending' : 'current',
                        'last_payment_date' => Carbon::now()->subDays(rand(1, 30)),
                        'last_payment_amount' => fake()->randomFloat(2, 25, 500),
                        'satisfaction_rating' => fake()->randomFloat(1, 3.5, 5.0),
                        'activated_at' => $data['status'] === 'active' ? Carbon::now()->subMonths(rand(1, 12)) : null,
                        'paused_at' => $data['status'] === 'paused' ? Carbon::now()->subDays(rand(1, 30)) : null,
                        'cancellation_date' => $data['status'] === 'cancelled' ? Carbon::now()->subDays(rand(1, 30)) : null,
                        'cancellation_reason' => $data['status'] === 'cancelled' ? fake()->randomElement(['price_too_high', 'service_quality', 'moving', 'financial_reasons']) : null,
                        'eligible_for_reactivation' => $data['status'] === 'cancelled' ? fake()->boolean(30) : true,
                        'created_at' => Carbon::now()->subMonths(rand(1, 12)),
                        'updated_at' => Carbon::now()->subDays(rand(0, 30)),
                    ]
                );
                
                $this->command->info("   ✅ Suscripción {$data['status']} creada para usuario {$user->name}");
            }
        }

        $this->command->info("   ✅ Suscripciones creadas exitosamente");
    }
}
