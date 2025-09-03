<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EnergyBond;
use App\Models\User;
use Carbon\Carbon;

class EnergyBondSeeder extends Seeder
{
    private $bondCounter = 1;
    public function run(): void
    {
        $this->command->info('💚 Creando bonos energéticos...');

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        // Limpiar bonos existentes
        EnergyBond::query()->delete();

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");

        // Crear diferentes tipos de bonos energéticos
        $this->createSolarBonds($users);
        $this->createWindBonds($users);
        $this->createHydroBonds($users);
        $this->createBiomassBonds($users);
        $this->createGeothermalBonds($users);
        $this->createHybridBonds($users);
        $this->createOtherBonds($users);

        $this->command->info('✅ EnergyBondSeeder completado. Se crearon ' . EnergyBond::count() . ' bonos energéticos.');
    }

    private function createSolarBonds($users): void
    {
        $this->command->info('☀️ Creando bonos solares...');

        foreach ($users as $user) {
            for ($i = 0; $i < rand(2, 4); $i++) {
                $this->createBond($user, 'solar', [
                    'name' => 'Bono Solar ' . fake()->words(2, true),
                    'description' => 'Bono para financiar instalaciones de energía solar fotovoltaica',
                    'face_value' => fake()->randomFloat(2, 10000, 500000),
                    'interest_rate' => fake()->randomFloat(2, 3.5, 8.5),
                    'risk_level' => fake()->randomElement(['low', 'medium']),
                    'credit_rating' => fake()->randomElement(['aaa', 'aa', 'a']),
                    'priority_order' => fake()->randomElement([1, 2]),
                    'is_tax_free' => fake()->boolean(30),
                    'is_guaranteed' => fake()->boolean(70),
                    'is_featured' => fake()->boolean(20),
                    'minimum_investment' => fake()->randomFloat(2, 1000, 10000),
                    'maximum_investment' => fake()->randomFloat(2, 50000, 200000),
                    'total_units_available' => fake()->numberBetween(100, 1000),
                    'unit_price' => fake()->randomFloat(2, 100, 1000),
                    'tags' => ['solar', 'renewable', 'green-energy', 'photovoltaic']
                ]);
            }
        }

        $this->command->info("   ✅ Bonos solares creados");
    }

    private function createWindBonds($users): void
    {
        $this->command->info('💨 Creando bonos eólicos...');

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $this->createBond($user, 'wind', [
                    'name' => 'Bono Eólico ' . fake()->words(2, true),
                    'description' => 'Bono para financiar parques eólicos y turbinas de viento',
                    'face_value' => fake()->randomFloat(2, 50000, 1000000),
                    'interest_rate' => fake()->randomFloat(2, 4.0, 9.0),
                    'risk_level' => fake()->randomElement(['medium', 'high']),
                    'credit_rating' => fake()->randomElement(['aa', 'a', 'bbb']),
                    'priority_order' => fake()->randomElement([1, 2, 3]),
                    'is_tax_free' => fake()->boolean(25),
                    'is_guaranteed' => fake()->boolean(60),
                    'is_featured' => fake()->boolean(15),
                    'minimum_investment' => fake()->randomFloat(2, 5000, 25000),
                    'maximum_investment' => fake()->randomFloat(2, 100000, 500000),
                    'total_units_available' => fake()->numberBetween(50, 500),
                    'unit_price' => fake()->randomFloat(2, 500, 5000),
                    'tags' => ['wind', 'renewable', 'turbine', 'offshore']
                ]);
            }
        }

        $this->command->info("   ✅ Bonos eólicos creados");
    }

    private function createHydroBonds($users): void
    {
        $this->command->info('💧 Creando bonos hidroeléctricos...');

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 2); $i++) {
                $this->createBond($user, 'hydro', [
                    'name' => 'Bono Hidroeléctrico ' . fake()->words(2, true),
                    'description' => 'Bono para financiar centrales hidroeléctricas',
                    'face_value' => fake()->randomFloat(2, 100000, 2000000),
                    'interest_rate' => fake()->randomFloat(2, 3.0, 7.5),
                    'risk_level' => fake()->randomElement(['low', 'medium']),
                    'credit_rating' => fake()->randomElement(['aaa', 'aa']),
                    'priority_order' => fake()->randomElement([1, 2]),
                    'is_tax_free' => fake()->boolean(40),
                    'is_guaranteed' => fake()->boolean(80),
                    'is_featured' => fake()->boolean(25),
                    'minimum_investment' => fake()->randomFloat(2, 10000, 50000),
                    'maximum_investment' => fake()->randomFloat(2, 200000, 1000000),
                    'total_units_available' => fake()->numberBetween(25, 200),
                    'unit_price' => fake()->randomFloat(2, 1000, 10000),
                    'tags' => ['hydro', 'renewable', 'dam', 'water']
                ]);
            }
        }

        $this->command->info("   ✅ Bonos hidroeléctricos creados");
    }

    private function createBiomassBonds($users): void
    {
        $this->command->info('🌱 Creando bonos de biomasa...');

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 2); $i++) {
                $this->createBond($user, 'biomass', [
                    'name' => 'Bono de Biomasa ' . fake()->words(2, true),
                    'description' => 'Bono para financiar plantas de biomasa y bioenergía',
                    'face_value' => fake()->randomFloat(2, 25000, 500000),
                    'interest_rate' => fake()->randomFloat(2, 4.5, 9.5),
                    'risk_level' => fake()->randomElement(['medium', 'high']),
                    'credit_rating' => fake()->randomElement(['a', 'bbb', 'bb']),
                    'priority_order' => fake()->randomElement([2, 3]),
                    'is_tax_free' => fake()->boolean(20),
                    'is_guaranteed' => fake()->boolean(50),
                    'is_featured' => fake()->boolean(10),
                    'minimum_investment' => fake()->randomFloat(2, 2000, 15000),
                    'maximum_investment' => fake()->randomFloat(2, 75000, 300000),
                    'total_units_available' => fake()->numberBetween(75, 400),
                    'unit_price' => fake()->randomFloat(2, 200, 2000),
                    'tags' => ['biomass', 'bioenergy', 'organic', 'waste-to-energy']
                ]);
            }
        }

        $this->command->info("   ✅ Bonos de biomasa creados");
    }

    private function createGeothermalBonds($users): void
    {
        $this->command->info('🌋 Creando bonos geotérmicos...');

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 2); $i++) {
                $this->createBond($user, 'geothermal', [
                    'name' => 'Bono Geotérmico ' . fake()->words(2, true),
                    'description' => 'Bono para financiar plantas de energía geotérmica',
                    'face_value' => fake()->randomFloat(2, 50000, 800000),
                    'interest_rate' => fake()->randomFloat(2, 3.8, 8.2),
                    'risk_level' => fake()->randomElement(['medium', 'high']),
                    'credit_rating' => fake()->randomElement(['aa', 'a', 'bbb']),
                    'priority_order' => fake()->randomElement([2, 3]),
                    'is_tax_free' => fake()->boolean(35),
                    'is_guaranteed' => fake()->boolean(65),
                    'is_featured' => fake()->boolean(15),
                    'minimum_investment' => fake()->randomFloat(2, 5000, 30000),
                    'maximum_investment' => fake()->randomFloat(2, 150000, 600000),
                    'total_units_available' => fake()->numberBetween(40, 300),
                    'unit_price' => fake()->randomFloat(2, 800, 4000),
                    'tags' => ['geothermal', 'renewable', 'heat', 'underground']
                ]);
            }
        }

        $this->command->info("   ✅ Bonos geotérmicos creados");
    }

    private function createHybridBonds($users): void
    {
        $this->command->info('🔋 Creando bonos híbridos...');

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 2); $i++) {
                $this->createBond($user, 'hybrid', [
                    'name' => 'Bono Híbrido ' . fake()->words(2, true),
                    'description' => 'Bono para financiar sistemas híbridos de energía renovable',
                    'face_value' => fake()->randomFloat(2, 30000, 600000),
                    'interest_rate' => fake()->randomFloat(2, 4.2, 8.8),
                    'risk_level' => fake()->randomElement(['low', 'medium']),
                    'credit_rating' => fake()->randomElement(['aaa', 'aa', 'a']),
                    'priority_order' => fake()->randomElement([1, 2]),
                    'is_tax_free' => fake()->boolean(45),
                    'is_guaranteed' => fake()->boolean(75),
                    'is_featured' => fake()->boolean(30),
                    'minimum_investment' => fake()->randomFloat(2, 3000, 20000),
                    'maximum_investment' => fake()->randomFloat(2, 120000, 400000),
                    'total_units_available' => fake()->numberBetween(60, 350),
                    'unit_price' => fake()->randomFloat(2, 600, 3000),
                    'tags' => ['hybrid', 'renewable', 'mixed', 'storage']
                ]);
            }
        }

        $this->command->info("   ✅ Bonos híbridos creados");
    }

    private function createOtherBonds($users): void
    {
        $this->command->info('🔧 Creando otros bonos energéticos...');

        foreach ($users as $user) {
            for ($i = 0; $i < rand(1, 2); $i++) {
                $this->createBond($user, 'other', [
                    'name' => 'Bono Energético ' . fake()->words(2, true),
                    'description' => 'Bono para financiar otros proyectos de energía sostenible',
                    'face_value' => fake()->randomFloat(2, 15000, 300000),
                    'interest_rate' => fake()->randomFloat(2, 5.0, 10.0),
                    'risk_level' => fake()->randomElement(['medium', 'high', 'very_high']),
                    'credit_rating' => fake()->randomElement(['a', 'bbb', 'bb', 'b']),
                    'priority_order' => fake()->randomElement([3, 4]),
                    'is_tax_free' => fake()->boolean(15),
                    'is_guaranteed' => fake()->boolean(40),
                    'is_featured' => fake()->boolean(5),
                    'minimum_investment' => fake()->randomFloat(2, 1000, 10000),
                    'maximum_investment' => fake()->randomFloat(2, 50000, 200000),
                    'total_units_available' => fake()->numberBetween(100, 600),
                    'unit_price' => fake()->randomFloat(2, 100, 1500),
                    'tags' => ['other', 'sustainable', 'innovation', 'emerging-tech']
                ]);
            }
        }

        $this->command->info("   ✅ Otros bonos energéticos creados");
    }

    private function createBond($user, $bondType, $additionalData = []): void
    {
        $issueDate = Carbon::now()->subDays(rand(30, 365));
        $maturityDate = $issueDate->copy()->addYears(rand(3, 15));
        $firstInterestDate = $issueDate->copy()->addMonths(rand(1, 6));

        $faceValue = $additionalData['face_value'] ?? fake()->randomFloat(2, 10000, 500000);
        $currentValue = $faceValue * fake()->randomFloat(2, 0.95, 1.05);
        $interestRate = $additionalData['interest_rate'] ?? fake()->randomFloat(2, 3.5, 8.5);
        $totalInterestPayments = $maturityDate->diffInMonths($firstInterestDate) / 12;
        $outstandingPrincipal = $faceValue * fake()->randomFloat(2, 0.7, 1.0);

        EnergyBond::create(array_merge([
            'bond_number' => 'BON-' . strtoupper($bondType) . '-' . str_pad($user->id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($this->bondCounter++, 4, '0', STR_PAD_LEFT),
            'name' => $additionalData['name'] ?? 'Bono ' . ucfirst($bondType) . ' ' . fake()->words(2, true),
            'description' => $additionalData['description'] ?? 'Bono para financiar proyectos de energía ' . $bondType,
            'bond_type' => $bondType,
            'status' => fake()->randomElement(['active', 'active', 'active', 'pending_approval', 'inactive']),
            'face_value' => $faceValue,
            'current_value' => $currentValue,
            'interest_rate' => $interestRate,
            'interest_frequency' => fake()->randomElement(['monthly', 'quarterly', 'semi_annually', 'annually']),
            'issue_date' => $issueDate,
            'maturity_date' => $maturityDate,
            'first_interest_date' => $firstInterestDate,
            'last_interest_payment_date' => fake()->optional()->dateTimeBetween('-1 year', '-1 month'),
            'next_interest_payment_date' => fake()->optional()->dateTimeBetween('+1 month', '+6 months'),
            'total_interest_payments' => (int) $totalInterestPayments,
            'paid_interest_payments' => fake()->numberBetween(0, (int) $totalInterestPayments),
            'total_interest_paid' => fake()->randomFloat(2, 0, $faceValue * $interestRate / 100 * 2),
            'outstanding_principal' => $outstandingPrincipal,
            'minimum_investment' => $additionalData['minimum_investment'] ?? fake()->randomFloat(2, 1000, 10000),
            'maximum_investment' => $additionalData['maximum_investment'] ?? fake()->randomFloat(2, 50000, 200000),
            'total_units_available' => $additionalData['total_units_available'] ?? fake()->numberBetween(100, 1000),
            'units_issued' => fake()->numberBetween(0, $additionalData['total_units_available'] ?? 1000),
            'units_reserved' => fake()->numberBetween(0, 50),
            'unit_price' => $additionalData['unit_price'] ?? fake()->randomFloat(2, 100, 1000),
            'payment_schedule' => fake()->randomElement(['monthly', 'quarterly', 'semi_annually', 'annually', 'at_maturity']),
            'is_tax_free' => $additionalData['is_tax_free'] ?? fake()->boolean(30),
            'tax_rate' => fake()->randomFloat(2, 0, 25),
            'is_guaranteed' => $additionalData['is_guaranteed'] ?? fake()->boolean(70),
            'guarantor_name' => fake()->optional()->company(),
            'guarantee_terms' => fake()->optional()->paragraph(),
            'is_collateralized' => fake()->boolean(40),
            'collateral_description' => fake()->optional()->paragraph(),
            'collateral_value' => fake()->optional()->randomFloat(2, 10000, 500000),
            'risk_level' => $additionalData['risk_level'] ?? fake()->randomElement(['low', 'medium', 'high', 'very_high']),
            'credit_rating' => $additionalData['credit_rating'] ?? fake()->randomElement(['aaa', 'aa', 'a', 'bbb', 'bb', 'b']),
            'risk_disclosure' => fake()->optional()->paragraph(),
            'is_public' => fake()->boolean(80),
            'is_featured' => $additionalData['is_featured'] ?? fake()->boolean(15),
            'priority_order' => $additionalData['priority_order'] ?? fake()->randomElement([1, 2, 3, 4]),
            'terms_conditions' => [
                'minimum_holding_period' => fake()->numberBetween(12, 60) . ' months',
                'early_redemption_fee' => fake()->randomFloat(2, 1, 5) . '%',
                'transfer_restrictions' => fake()->boolean(30),
                'voting_rights' => fake()->boolean(20)
            ],
            'disclosure_documents' => [
                'prospectus' => fake()->optional()->url(),
                'financial_statements' => fake()->optional()->url(),
                'risk_assessment' => fake()->optional()->url()
            ],
            'legal_documents' => [
                'bond_agreement' => fake()->optional()->url(),
                'trust_deed' => fake()->optional()->url(),
                'regulatory_approvals' => fake()->optional()->url()
            ],
            'financial_reports' => [
                'quarterly_report' => fake()->optional()->url(),
                'annual_report' => fake()->optional()->url(),
                'audit_report' => fake()->optional()->url()
            ],
            'performance_metrics' => [
                'total_return' => fake()->randomFloat(2, -5, 25) . '%',
                'volatility' => fake()->randomFloat(2, 5, 30) . '%',
                'sharpe_ratio' => fake()->randomFloat(2, 0.5, 2.5),
                'max_drawdown' => fake()->randomFloat(2, -20, -5) . '%'
            ],
            'tags' => $additionalData['tags'] ?? ['renewable', 'energy', 'investment'],
            'notes' => fake()->optional()->paragraph(),
            'created_by' => $user->id,
            'approved_by' => fake()->optional()->randomElement([1, 2, 3, 4, 5]),
            'approved_at' => fake()->optional()->dateTimeBetween('-6 months', '-1 day'),
            'managed_by' => fake()->optional()->randomElement([1, 2, 3, 4, 5]),
            'created_at' => $issueDate,
            'updated_at' => Carbon::now()->subDays(rand(0, 30))
        ], $additionalData));
    }
}
