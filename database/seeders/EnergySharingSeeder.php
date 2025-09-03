<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EnergySharing;
use App\Models\User;
use App\Models\EnergyCooperative;
use Carbon\Carbon;

class EnergySharingSeeder extends Seeder
{
    private $sharingCounter = 1;

    public function run(): void
    {
        $this->command->info('⚡ Creando intercambios de energía...');

        $users = User::take(6)->get();
        $cooperatives = EnergyCooperative::take(3)->get();

        if ($users->isEmpty()) {
            $this->command->warn('   ⚠️ No hay usuarios disponibles. Creando usuarios de prueba...');
            $users = User::factory(6)->create();
        }

        if ($cooperatives->isEmpty()) {
            $this->command->warn('   ⚠️ No hay cooperativas disponibles. Saltando cooperativas...');
        }

        // Crear intercambios con diferentes estados
        $sharingData = [
            [
                'sharing_type' => 'direct',
                'title' => 'Intercambio Directo Solar',
                'status' => 'active',
                'energy_source' => 'solar',
                'is_renewable' => true,
                'renewable_percentage' => 100,
            ],
            [
                'sharing_type' => 'community',
                'title' => 'Intercambio Comunitario Eólico',
                'status' => 'active',
                'energy_source' => 'wind',
                'is_renewable' => true,
                'renewable_percentage' => 100,
            ],
            [
                'sharing_type' => 'marketplace',
                'title' => 'Intercambio Marketplace Hidráulico',
                'status' => 'proposed',
                'energy_source' => 'hydro',
                'is_renewable' => true,
                'renewable_percentage' => 85,
            ],
            [
                'sharing_type' => 'emergency',
                'title' => 'Intercambio de Emergencia',
                'status' => 'completed',
                'energy_source' => 'mixed',
                'is_renewable' => false,
                'renewable_percentage' => 0,
            ],
            [
                'sharing_type' => 'scheduled',
                'title' => 'Intercambio Programado',
                'status' => 'disputed',
                'energy_source' => 'solar',
                'is_renewable' => true,
                'renewable_percentage' => 90,
            ],
            [
                'sharing_type' => 'real_time',
                'title' => 'Intercambio Tiempo Real',
                'status' => 'failed',
                'energy_source' => 'wind',
                'is_renewable' => true,
                'renewable_percentage' => 100,
            ],
        ];

        foreach ($sharingData as $index => $data) {
            if (isset($users[$index]) && isset($users[($index + 1) % count($users)])) {
                $providerUser = $users[$index];
                $consumerUser = $users[($index + 1) % count($users)];
                
                $sharingStart = Carbon::now()->addDays(rand(1, 30));
                $sharingEnd = $sharingStart->copy()->addHours(rand(2, 8));
                $energyAmount = fake()->randomFloat(2, 50, 500);
                $energyDelivered = $data['status'] === 'completed' ? $energyAmount : fake()->randomFloat(2, 0, $energyAmount * 0.8);
                
                EnergySharing::create([
                    'provider_user_id' => $providerUser->id,
                    'consumer_user_id' => $consumerUser->id,
                    'energy_cooperative_id' => $cooperatives->isNotEmpty() ? $cooperatives->random()->id : null,
                    'sharing_code' => 'SHARE-' . strtoupper($data['sharing_type']) . '-' . str_pad($this->sharingCounter++, 4, '0', STR_PAD_LEFT),
                    'title' => $data['title'],
                    'description' => fake()->paragraph(),
                    'sharing_type' => $data['sharing_type'],
                    'status' => $data['status'],
                    'energy_amount_kwh' => $energyAmount,
                    'energy_delivered_kwh' => $energyDelivered,
                    'energy_remaining_kwh' => $energyAmount - $energyDelivered,
                    'energy_source' => $data['energy_source'],
                    'is_renewable' => $data['is_renewable'],
                    'renewable_percentage' => $data['renewable_percentage'],
                    'sharing_start_datetime' => $sharingStart,
                    'sharing_end_datetime' => $sharingEnd,
                    'proposal_expiry_datetime' => Carbon::now()->addDays(rand(1, 7)),
                    'duration_hours' => $sharingEnd->diffInHours($sharingStart),
                    'flexible_timing' => fake()->boolean(30),
                    'price_per_kwh' => fake()->randomFloat(4, 0.05, 0.25),
                    'total_amount' => $energyAmount * fake()->randomFloat(4, 0.05, 0.25),
                    'platform_fee' => fake()->randomFloat(2, 1, 10),
                    'cooperative_fee' => fake()->randomFloat(2, 2, 15),
                    'net_amount' => $energyAmount * fake()->randomFloat(4, 0.05, 0.25) - fake()->randomFloat(2, 3, 25),
                    'currency' => 'EUR',
                    'payment_method' => fake()->randomElement(['credits', 'bank_transfer', 'energy_tokens', 'barter', 'loyalty_points']),
                    'quality_score' => fake()->randomFloat(1, 7.5, 9.9),
                    'reliability_score' => fake()->randomFloat(1, 8.0, 9.9),
                    'delivery_efficiency' => fake()->randomFloat(1, 85, 98),
                    'interruptions_count' => rand(0, 3),
                    'average_voltage' => fake()->randomFloat(1, 220, 240),
                    'frequency_stability' => fake()->randomFloat(3, 49.8, 50.2),
                    'max_distance_km' => fake()->randomFloat(1, 5, 50),
                    'actual_distance_km' => fake()->randomFloat(1, 1, 25),
                    'requires_grid_approval' => fake()->boolean(20),
                    'connection_type' => fake()->randomElement(['direct', 'grid', 'hybrid']),
                    'allows_partial_delivery' => fake()->boolean(60),
                    'min_delivery_kwh' => $energyAmount * 0.1,
                    'co2_reduction_kg' => $energyAmount * fake()->randomFloat(2, 0.3, 0.8),
                    'environmental_impact_score' => fake()->randomFloat(1, 8.0, 10.0),
                    'certified_green_energy' => $data['is_renewable'] && fake()->boolean(70),
                    'certification_number' => $data['is_renewable'] ? 'CERT-' . strtoupper($data['energy_source']) . '-' . rand(1000, 9999) : null,
                    'monitoring_frequency_minutes' => rand(5, 60),
                    'real_time_tracking' => fake()->boolean(80),
                    'dispute_reason' => $data['status'] === 'disputed' ? fake()->randomElement(['delivery_delay', 'quality_issues', 'payment_dispute', 'technical_problems']) : null,
                    'dispute_resolution' => $data['status'] === 'disputed' ? fake()->optional()->sentence() : null,
                    'provider_rating' => fake()->randomFloat(1, 3.5, 9.9),
                    'consumer_rating' => fake()->randomFloat(1, 3.5, 9.9),
                    'provider_feedback' => fake()->optional()->sentence(),
                    'consumer_feedback' => fake()->optional()->sentence(),
                    'would_repeat' => fake()->boolean(80),
                    'payment_status' => $data['status'] === 'completed' ? 'completed' : ($data['status'] === 'active' ? 'processing' : 'pending'),
                    'payment_due_date' => Carbon::now()->addDays(rand(1, 30)),
                    'payment_completed_at' => $data['status'] === 'completed' ? Carbon::now()->subDays(rand(1, 7)) : null,
                    'proposed_at' => $data['status'] === 'proposed' ? Carbon::now()->subDays(rand(1, 5)) : null,
                    'accepted_at' => in_array($data['status'], ['active', 'completed']) ? Carbon::now()->subDays(rand(1, 3)) : null,
                    'started_at' => $data['status'] === 'active' ? Carbon::now()->subHours(rand(1, 24)) : null,
                    'completed_at' => $data['status'] === 'completed' ? Carbon::now()->subDays(rand(1, 7)) : null,
                    'cancelled_at' => $data['status'] === 'failed' ? Carbon::now()->subDays(rand(1, 3)) : null,
                    'created_at' => Carbon::now()->subDays(rand(1, 30)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 30)),
                ]);
                
                $this->command->info("   ✅ Intercambio {$data['status']} creado: {$data['title']}");
            }
        }

        $this->command->info("   ✅ Intercambios de energía creados exitosamente");
    }
}
