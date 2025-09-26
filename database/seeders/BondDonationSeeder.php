<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BondDonation;
use App\Models\User;
use App\Models\EnergyBond;
use Carbon\Carbon;

class BondDonationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(10)->get();
        $energyBonds = EnergyBond::take(15)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Saltando BondDonationSeeder.');
            return;
        }

        if ($energyBonds->isEmpty()) {
            $this->command->warn('⚠️ No hay bonos energéticos disponibles. Saltando BondDonationSeeder.');
            return;
        }

        $donations = [];

        // Crear donaciones de bonos
        foreach ($energyBonds as $index => $bond) {
            $donor = $users->random();
            $approver = $users->random();
            
            $donationAmount = fake()->randomFloat(2, 100, 10000);
            $bondUnits = fake()->numberBetween(1, 100);
            $unitPrice = $bond->current_price ?? fake()->randomFloat(2, 10, 100);
            $totalValue = $bondUnits * $unitPrice;
            $currentValue = $totalValue * fake()->randomFloat(2, 0.8, 1.2);

            $donations[] = [
                'donation_number' => 'DON-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                'donor_id' => $donor->id,
                'energy_bond_id' => $bond->id,
                'donation_type' => fake()->randomElement(array_keys(BondDonation::getDonationTypes())),
                'status' => fake()->randomElement(array_keys(BondDonation::getStatuses())),
                'priority' => fake()->randomElement(array_keys(BondDonation::getPriorities())),
                'donation_amount' => $donationAmount,
                'bond_units' => $bondUnits,
                'unit_price_at_donation' => $unitPrice,
                'total_value_at_donation' => $totalValue,
                'current_value' => $currentValue,
                'donation_date' => Carbon::now()->subDays(rand(1, 365)),
                'approval_date' => fake()->optional(0.8)->randomElement([Carbon::now()->subDays(rand(1, 30)), null]),
                'completion_date' => fake()->optional(0.6)->randomElement([Carbon::now()->subDays(rand(1, 15)), null]),
                'expiry_date' => fake()->optional(0.3)->randomElement([Carbon::now()->addDays(rand(30, 365)), null]),
                'donation_purpose' => fake()->sentence(),
                'impact_description' => fake()->optional(0.7)->sentence(),
                'recipient_organization' => fake()->optional(0.6)->company(),
                'recipient_beneficiaries' => fake()->optional(0.5)->sentence(),
                'project_description' => fake()->optional(0.4)->paragraph(),
                'project_status' => fake()->optional(0.3)->randomElement(array_keys(BondDonation::getProjectStatuses())),
                'project_budget' => fake()->optional(0.2)->randomFloat(2, 1000, 50000),
                'project_spent' => fake()->optional(0.1)->randomFloat(2, 100, 10000),
                'project_start_date' => fake()->optional(0.2)->randomElement([Carbon::now()->subDays(rand(30, 180)), null]),
                'project_end_date' => fake()->optional(0.1)->randomElement([Carbon::now()->addDays(rand(30, 365)), null]),
                'project_milestones' => fake()->optional(0.1)->paragraph(),
                'project_outcomes' => fake()->optional(0.1)->paragraph(),
                'project_challenges' => fake()->optional(0.1)->paragraph(),
                'project_lessons_learned' => fake()->optional(0.1)->paragraph(),
                'is_anonymous' => fake()->boolean(20),
                'is_recurring' => fake()->boolean(15),
                'recurrence_frequency' => fake()->optional(0.15)->randomElement(array_keys(BondDonation::getRecurrenceFrequencies())),
                'next_recurrence_date' => fake()->optional(0.15)->randomElement([Carbon::now()->addDays(rand(30, 90)), null]),
                'recurrence_count' => fake()->numberBetween(0, 12),
                'max_recurrences' => fake()->optional(0.15)->randomElement([12, 24, 36, null]),
                'is_matched' => fake()->boolean(25),
                'matching_ratio' => fake()->optional(0.25)->randomFloat(2, 50, 200),
                'matching_amount' => fake()->optional(0.25)->randomFloat(2, 50, 2000),
                'matching_organization' => fake()->optional(0.25)->company(),
                'matching_terms' => fake()->optional(0.25)->sentence(),
                'is_tax_deductible' => fake()->boolean(60),
                'tax_deduction_reference' => fake()->optional(0.6)->numerify('TAX-########'),
                'tax_deduction_amount' => fake()->optional(0.6)->randomFloat(2, 10, 1000),
                'tax_deduction_notes' => fake()->optional(0.6)->sentence(),
                'donor_preferences' => json_encode([
                    'communication' => fake()->randomElement(['email', 'phone', 'mail']),
                    'frequency' => fake()->randomElement(['monthly', 'quarterly', 'annual']),
                    'updates' => fake()->boolean(80),
                ]),
                'communication_preferences' => json_encode([
                    'newsletter' => fake()->boolean(70),
                    'events' => fake()->boolean(50),
                    'reports' => fake()->boolean(60),
                ]),
                'reporting_preferences' => json_encode([
                    'format' => fake()->randomElement(['pdf', 'email', 'online']),
                    'frequency' => fake()->randomElement(['monthly', 'quarterly', 'annual']),
                ]),
                'recognition_preferences' => json_encode([
                    'public' => fake()->boolean(40),
                    'private' => fake()->boolean(60),
                    'anonymous' => fake()->boolean(20),
                ]),
                'special_instructions' => fake()->optional(0.3)->sentence(),
                'internal_notes' => fake()->optional(0.4)->sentence(),
                'tags' => json_encode(['donación', 'bonos', 'energía', 'sostenibilidad']),
                'created_by' => $donor->id,
                'approved_by' => fake()->optional(0.8)->randomElement([$approver->id, null]),
                'processed_by' => fake()->optional(0.6)->randomElement([$approver->id, null]),
            ];
        }

        foreach ($donations as $donation) {
            BondDonation::create($donation);
        }

        $this->command->info('✅ BondDonationSeeder ejecutado correctamente');
        $this->command->info('📊 Donaciones de bonos creadas: ' . count($donations));
        $this->command->info('💰 Valor total donado: €' . number_format(collect($donations)->sum('total_value_at_donation'), 2));
        $this->command->info('📈 Tipos de donación: Caritativa, Educativa, Ambiental, Comunitaria, Investigación');
        $this->command->info('🎯 Estados: Pendientes, Aprobadas, Rechazadas, Completadas');
        $this->command->info('⭐ Prioridades: Baja, Media, Alta, Urgente, Crítica');
        $this->command->info('🔄 Recurrentes: ' . collect($donations)->where('is_recurring', true)->count());
        $this->command->info('🤝 Emparejadas: ' . collect($donations)->where('is_matched', true)->count());
        $this->command->info('🏷️ Deducibles: ' . collect($donations)->where('is_tax_deductible', true)->count());
        $this->command->info('👤 Anónimas: ' . collect($donations)->where('is_anonymous', true)->count());
    }
}
