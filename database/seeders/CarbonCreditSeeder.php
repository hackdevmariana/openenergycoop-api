<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CarbonCredit;
use App\Models\User;
use App\Models\Provider;
use App\Models\UserAsset;
use App\Models\EnergyProduction;
use Carbon\Carbon;

class CarbonCreditSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Creando créditos de carbono...');

        $users = User::all();
        $providers = Provider::all();
        $userAssets = UserAsset::all();
        $energyProductions = EnergyProduction::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles.');
            return;
        }

        // Limpiar datos existentes
        CarbonCredit::query()->delete();

        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("🏢 Proveedores disponibles: {$providers->count()}");
        $this->command->info("🏠 Activos disponibles: {$userAssets->count()}");
        $this->command->info("⚡ Producciones disponibles: {$energyProductions->count()}");

        // Crear diferentes tipos de créditos
        $this->createRenewableEnergyCredits($users, $providers, $userAssets, $energyProductions);
        $this->createForestryCredits($users, $providers, $userAssets, $energyProductions);
        $this->createBiomassCredits($users, $providers, $userAssets, $energyProductions);
        $this->createEfficiencyCredits($users, $providers, $userAssets, $energyProductions);
        $this->createMethaneCredits($users, $providers, $userAssets, $energyProductions);

        $this->command->info('✅ CarbonCreditSeeder completado. Se crearon ' . CarbonCredit::count() . ' créditos.');
    }

    private function createRenewableEnergyCredits($users, $providers, $userAssets, $energyProductions): void
    {
        $this->command->info('☀️ Creando créditos de energía renovable...');

        $projectTypes = ['Energía Solar', 'Energía Eólica', 'Energía Hidroeléctrica'];
        $countries = ['España', 'Portugal', 'Francia', 'Alemania', 'Italia'];

        for ($i = 0; $i < 30; $i++) {
            $user = $users->random();
            $provider = $providers->isEmpty() ? null : $providers->random();
            $userAsset = $userAssets->isEmpty() ? null : $userAssets->random();
            $energyProduction = $energyProductions->isEmpty() ? null : $energyProductions->random();

            $totalCredits = fake()->randomFloat(2, 500, 5000);
            $retiredCredits = fake()->randomFloat(2, 0, $totalCredits * 0.2);
            $transferredCredits = fake()->randomFloat(2, 0, ($totalCredits - $retiredCredits) * 0.3);
            $availableCredits = $totalCredits - $retiredCredits - $transferredCredits;

            $projectType = fake()->randomElement($projectTypes);
            $country = fake()->randomElement($countries);

            CarbonCredit::create([
                'user_id' => $user->id,
                'provider_id' => $provider?->id,
                'user_asset_id' => $userAsset?->id,
                'energy_production_id' => $energyProduction?->id,
                'credit_id' => 'CC-REN-' . strtoupper(fake()->bothify('####-??-####')),
                'registry_id' => 'REG-' . fake()->numerify('######'),
                'serial_number' => fake()->numerify('SN-##########'),
                'batch_id' => 'BATCH-' . fake()->numerify('####'),
                'credit_type' => fake()->randomElement(['vcs', 'gold_standard', 'vcu']),
                'standard_version' => 'v' . fake()->randomFloat(1, 1, 4),
                'methodology' => fake()->randomElement(['VM0007', 'VM0012', 'VM0015', 'ACM0002']),
                'project_name' => $projectType . ' Project ' . fake()->words(2, true),
                'project_description' => fake()->paragraphs(2, true),
                'project_id' => 'PROJ-' . fake()->numerify('######'),
                'project_type' => $projectType,
                'project_location' => fake()->city . ', ' . fake()->state,
                'project_country' => $country,
                'project_coordinates' => json_encode([
                    'lat' => fake()->latitude,
                    'lng' => fake()->longitude
                ]),
                'total_credits' => $totalCredits,
                'available_credits' => $availableCredits,
                'retired_credits' => $retiredCredits,
                'transferred_credits' => $transferredCredits,
                'status' => fake()->randomElement(['verified', 'issued', 'available']),
                'credit_period_start' => Carbon::now()->subYears(rand(1, 3)),
                'credit_period_end' => Carbon::now()->addYears(rand(1, 5)),
                'vintage_year' => Carbon::now()->subYears(rand(1, 2)),
                'issuance_date' => Carbon::now()->subMonths(rand(1, 12)),
                'verification_date' => Carbon::now()->subMonths(rand(1, 6)),
                'expiry_date' => Carbon::now()->addYears(rand(5, 10)),
                'purchase_price_per_credit' => fake()->randomFloat(4, 8, 25),
                'current_market_price' => fake()->randomFloat(4, 12, 35),
                'total_investment' => $totalCredits * fake()->randomFloat(4, 8, 25),
                'currency' => 'EUR',
                'verifier_name' => fake()->company . ' Verification Services',
                'verifier_accreditation' => 'ISO 14064-2',
                'last_verification_date' => Carbon::now()->subMonths(rand(1, 6)),
                'next_verification_date' => Carbon::now()->addMonths(rand(6, 12)),
                'verification_documents' => json_encode([
                    'verification_report' => 'https://example.com/verification-report.pdf',
                    'monitoring_plan' => 'https://example.com/monitoring-plan.pdf'
                ]),
                'additionality_demonstrated' => fake()->boolean(85),
                'additionality_justification' => fake()->optional(0.8)->paragraph,
                'co_benefits' => json_encode([
                    'biodiversity' => 'Conservación de especies locales',
                    'social' => 'Creación de empleos locales',
                    'economic' => 'Desarrollo económico regional'
                ]),
                'sdg_contributions' => json_encode(['7', '13', '15']),
                'monitoring_frequency' => fake()->randomElement(['monthly', 'quarterly', 'annual']),
                'last_monitoring_report_date' => Carbon::now()->subMonths(rand(1, 3)),
                'monitoring_data' => json_encode([
                    'last_report' => '2024-Q3',
                    'next_report' => '2024-Q4',
                    'compliance_status' => 'compliant'
                ]),
                'transaction_history' => json_encode([
                    [
                        'date' => Carbon::now()->subMonths(3)->toDateString(),
                        'type' => 'purchase',
                        'amount' => $totalCredits * 0.8,
                        'price' => fake()->randomFloat(4, 8, 25)
                    ]
                ]),
                'original_owner_id' => $user->id,
                'last_transfer_date' => fake()->optional(0.3)->dateTimeBetween('-6 months', 'now'),
                'transfer_fees' => fake()->optional(0.2)->randomFloat(2, 50, 200),
                'retirement_reason' => fake()->optional(0.1)->sentence,
                'retirement_date' => fake()->optional(0.1)->dateTimeBetween('-1 year', 'now'),
                'retired_by' => fake()->optional(0.1)->randomElement($users->pluck('id')->toArray()),
                'retirement_certificate' => fake()->optional(0.1)->url,
                'risk_rating' => fake()->randomElement(['low', 'medium']),
                'risk_factors' => fake()->optional(0.3)->paragraph,
                'insurance_coverage' => fake()->boolean(60),
                'insurance_amount' => fake()->optional(0.6)->randomFloat(2, 10000, 100000),
                'blockchain_hash' => fake()->optional(0.7)->sha256,
                'provenance_chain' => json_encode([
                    'origin' => 'Project Developer',
                    'verification' => 'Third Party Verifier',
                    'registry' => 'Carbon Registry'
                ]),
                'public_registry_listed' => fake()->boolean(80),
                'registry_url' => fake()->optional(0.8)->url,
                'actual_co2_reduced' => $totalCredits * fake()->randomFloat(2, 0.9, 1.1),
                'measurement_uncertainty' => fake()->randomFloat(2, 2, 8),
                'environmental_monitoring' => json_encode([
                    'air_quality' => 'Improved',
                    'water_quality' => 'Maintained',
                    'biodiversity' => 'Enhanced'
                ]),
                'technical_specifications' => json_encode([
                    'capacity' => fake()->randomFloat(2, 1, 50) . ' MW',
                    'technology' => $projectType,
                    'efficiency' => fake()->randomFloat(2, 85, 98) . '%'
                ]),
                'project_capacity_mw' => fake()->randomFloat(3, 1, 50),
                'expected_project_lifetime_years' => fake()->numberBetween(20, 30),
                'annual_emission_reductions' => $totalCredits / fake()->numberBetween(10, 20),
                'regulatory_approvals' => json_encode([
                    'local_authority' => 'Approved',
                    'environmental_agency' => 'Approved',
                    'energy_regulator' => 'Approved'
                ]),
                'meets_article_6_requirements' => fake()->boolean(90),
                'corresponding_adjustment_applied' => fake()->boolean(70),
                'regulatory_metadata' => json_encode([
                    'compliance_status' => 'compliant',
                    'reporting_frequency' => 'annual'
                ]),
                'sustainability_certifications' => json_encode(['ISO 14001', 'FSC']),
                'gender_inclusive' => fake()->boolean(75),
                'community_engagement' => fake()->boolean(80),
                'social_impact_description' => fake()->optional(0.7)->paragraph,
                'custom_attributes' => json_encode([
                    'local_community_benefits' => true,
                    'technology_transfer' => true
                ]),
                'notes' => fake()->optional(0.3)->sentence,
                'attachments' => json_encode([
                    'project_documentation' => 'https://example.com/project-docs.pdf',
                    'environmental_assessment' => 'https://example.com/env-assessment.pdf'
                ]),
                'is_active' => true,
                'approved_at' => Carbon::now()->subMonths(rand(1, 6)),
                'approved_by' => fake()->randomElement($users->pluck('id')->toArray()),
            ]);
        }
    }

    private function createForestryCredits($users, $providers, $userAssets, $energyProductions): void
    {
        $this->command->info('🌳 Creando créditos forestales...');

        $countries = ['Brasil', 'Perú', 'Colombia', 'Indonesia', 'Costa Rica', 'México'];

        for ($i = 0; $i < 25; $i++) {
            $user = $users->random();
            $provider = $providers->isEmpty() ? null : $providers->random();

            $totalCredits = fake()->randomFloat(2, 1000, 8000);
            $retiredCredits = fake()->randomFloat(2, 0, $totalCredits * 0.15);
            $transferredCredits = fake()->randomFloat(2, 0, ($totalCredits - $retiredCredits) * 0.25);
            $availableCredits = $totalCredits - $retiredCredits - $transferredCredits;

            $country = fake()->randomElement($countries);

            CarbonCredit::create([
                'user_id' => $user->id,
                'provider_id' => $provider?->id,
                'credit_id' => 'CC-FOR-' . strtoupper(fake()->bothify('####-??-####')),
                'credit_type' => fake()->randomElement(['vcs', 'gold_standard', 'cdm']),
                'project_name' => fake()->words(3, true) . ' Forest Conservation Project',
                'project_type' => fake()->randomElement(['Reforestación', 'Conservación Forestal', 'Manejo Forestal Sostenible']),
                'project_country' => $country,
                'project_location' => fake()->city . ', ' . fake()->state,
                'total_credits' => $totalCredits,
                'available_credits' => $availableCredits,
                'retired_credits' => $retiredCredits,
                'transferred_credits' => $transferredCredits,
                'status' => fake()->randomElement(['verified', 'issued', 'available']),
                'vintage_year' => Carbon::now()->subYears(rand(1, 3)),
                'credit_period_start' => Carbon::now()->subYears(rand(2, 4)),
                'credit_period_end' => Carbon::now()->addYears(rand(10, 20)),
                'purchase_price_per_credit' => fake()->randomFloat(4, 5, 20),
                'current_market_price' => fake()->randomFloat(4, 8, 25),
                'methodology' => fake()->randomElement(['VM0007', 'VM0012', 'CDM-AR-AM0010']),
                'verifier_name' => fake()->company . ' Forestry Verification',
                'additionality_demonstrated' => fake()->boolean(90),
                'co_benefits' => json_encode([
                    'biodiversity' => 'Protección de especies endémicas',
                    'water' => 'Protección de cuencas hidrográficas',
                    'social' => 'Desarrollo comunitario'
                ]),
                'sdg_contributions' => json_encode(['13', '15', '6']),
                'monitoring_frequency' => 'annual',
                'risk_rating' => fake()->randomElement(['low', 'medium']),

                'actual_co2_reduced' => $totalCredits * fake()->randomFloat(2, 0.85, 1.15),
                'project_capacity_mw' => null,
                'expected_project_lifetime_years' => fake()->numberBetween(25, 50),
                'is_active' => true,
            ]);
        }
    }

    private function createBiomassCredits($users, $providers, $userAssets, $energyProductions): void
    {
        $this->command->info('🌱 Creando créditos de biomasa...');

        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $provider = $providers->isEmpty() ? null : $providers->random();

            $totalCredits = fake()->randomFloat(2, 300, 2000);
            $retiredCredits = fake()->randomFloat(2, 0, $totalCredits * 0.1);
            $transferredCredits = fake()->randomFloat(2, 0, ($totalCredits - $retiredCredits) * 0.2);
            $availableCredits = $totalCredits - $retiredCredits - $transferredCredits;

            CarbonCredit::create([
                'user_id' => $user->id,
                'provider_id' => $provider?->id,
                'credit_id' => 'CC-BIO-' . strtoupper(fake()->bothify('####-??-####')),
                'credit_type' => fake()->randomElement(['vcs', 'gold_standard', 'cdm']),
                'project_name' => fake()->words(3, true) . ' Biomass Energy Project',
                'project_type' => 'Biomasa',
                'project_country' => fake()->randomElement(['España', 'Portugal', 'Francia', 'Alemania']),
                'project_location' => fake()->city . ', ' . fake()->state,
                'total_credits' => $totalCredits,
                'available_credits' => $availableCredits,
                'retired_credits' => $retiredCredits,
                'transferred_credits' => $transferredCredits,
                'status' => fake()->randomElement(['verified', 'issued', 'available']),
                'vintage_year' => Carbon::now()->subYears(rand(1, 2)),
                'credit_period_start' => Carbon::now()->subYears(rand(1, 2)),
                'credit_period_end' => Carbon::now()->addYears(rand(5, 15)),
                'purchase_price_per_credit' => fake()->randomFloat(4, 6, 18),
                'current_market_price' => fake()->randomFloat(4, 10, 22),
                'methodology' => fake()->randomElement(['ACM0006', 'AMS-I.D', 'VM0012']),
                'verifier_name' => fake()->company . ' Biomass Verification',
                'additionality_demonstrated' => fake()->boolean(80),
                'co_benefits' => json_encode([
                    'waste_management' => 'Gestión sostenible de residuos',
                    'local_employment' => 'Creación de empleos locales',
                    'energy_security' => 'Seguridad energética local'
                ]),
                'sdg_contributions' => json_encode(['7', '11', '13']),
                'monitoring_frequency' => 'monthly',
                'risk_rating' => 'medium',
                'actual_co2_reduced' => $totalCredits * fake()->randomFloat(2, 0.9, 1.1),
                'project_capacity_mw' => fake()->randomFloat(3, 0.5, 10),
                'expected_project_lifetime_years' => fake()->numberBetween(15, 25),
                'is_active' => true,
            ]);
        }
    }

    private function createEfficiencyCredits($users, $providers, $userAssets, $energyProductions): void
    {
        $this->command->info('⚡ Creando créditos de eficiencia energética...');

        for ($i = 0; $i < 15; $i++) {
            $user = $users->random();
            $provider = $providers->isEmpty() ? null : $providers->random();

            $totalCredits = fake()->randomFloat(2, 200, 1500);
            $retiredCredits = fake()->randomFloat(2, 0, $totalCredits * 0.05);
            $transferredCredits = fake()->randomFloat(2, 0, ($totalCredits - $retiredCredits) * 0.15);
            $availableCredits = $totalCredits - $retiredCredits - $transferredCredits;

            CarbonCredit::create([
                'user_id' => $user->id,
                'provider_id' => $provider?->id,
                'credit_id' => 'CC-EFF-' . strtoupper(fake()->bothify('####-??-####')),
                'credit_type' => fake()->randomElement(['vcs', 'gold_standard', 'cer']),
                'project_name' => fake()->words(3, true) . ' Energy Efficiency Project',
                'project_type' => 'Eficiencia Energética',
                'project_country' => fake()->randomElement(['España', 'Portugal', 'Francia', 'Italia']),
                'project_location' => fake()->city . ', ' . fake()->state,
                'total_credits' => $totalCredits,
                'available_credits' => $availableCredits,
                'retired_credits' => $retiredCredits,
                'transferred_credits' => $transferredCredits,
                'status' => fake()->randomElement(['verified', 'issued', 'available']),
                'vintage_year' => Carbon::now()->subYears(rand(1, 2)),
                'credit_period_start' => Carbon::now()->subYears(rand(1, 2)),
                'credit_period_end' => Carbon::now()->addYears(rand(3, 10)),
                'purchase_price_per_credit' => fake()->randomFloat(4, 4, 15),
                'current_market_price' => fake()->randomFloat(4, 8, 18),
                'methodology' => fake()->randomElement(['AMS-II.C', 'AMS-II.D', 'ACM0006']),
                'verifier_name' => fake()->company . ' Efficiency Verification',
                'additionality_demonstrated' => fake()->boolean(85),
                'co_benefits' => json_encode([
                    'cost_savings' => 'Ahorro en costos energéticos',
                    'productivity' => 'Mejora en productividad',
                    'comfort' => 'Mejor confort térmico'
                ]),
                'sdg_contributions' => json_encode(['7', '11', '13']),
                'monitoring_frequency' => 'quarterly',
                'risk_rating' => 'low',
                'actual_co2_reduced' => $totalCredits * fake()->randomFloat(2, 0.95, 1.05),
                'project_capacity_mw' => null,
                'expected_project_lifetime_years' => fake()->numberBetween(10, 20),
                'is_active' => true,
            ]);
        }
    }

    private function createMethaneCredits($users, $providers, $userAssets, $energyProductions): void
    {
        $this->command->info('🔥 Creando créditos de metano...');

        for ($i = 0; $i < 10; $i++) {
            $user = $users->random();
            $provider = $providers->isEmpty() ? null : $providers->random();

            $totalCredits = fake()->randomFloat(2, 500, 3000);
            $retiredCredits = fake()->randomFloat(2, 0, $totalCredits * 0.1);
            $transferredCredits = fake()->randomFloat(2, 0, ($totalCredits - $retiredCredits) * 0.2);
            $availableCredits = $totalCredits - $retiredCredits - $transferredCredits;

            CarbonCredit::create([
                'user_id' => $user->id,
                'provider_id' => $provider?->id,
                'credit_id' => 'CC-MET-' . strtoupper(fake()->bothify('####-??-####')),
                'credit_type' => fake()->randomElement(['vcs', 'gold_standard', 'cdm']),
                'project_name' => fake()->words(3, true) . ' Methane Capture Project',
                'project_type' => 'Metano',
                'project_country' => fake()->randomElement(['España', 'Portugal', 'Francia', 'Alemania']),
                'project_location' => fake()->city . ', ' . fake()->state,
                'total_credits' => $totalCredits,
                'available_credits' => $availableCredits,
                'retired_credits' => $retiredCredits,
                'transferred_credits' => $transferredCredits,
                'status' => fake()->randomElement(['verified', 'issued', 'available']),
                'vintage_year' => Carbon::now()->subYears(rand(1, 2)),
                'credit_period_start' => Carbon::now()->subYears(rand(1, 2)),
                'credit_period_end' => Carbon::now()->addYears(rand(5, 15)),
                'purchase_price_per_credit' => fake()->randomFloat(4, 3, 12),
                'current_market_price' => fake()->randomFloat(4, 6, 15),
                'methodology' => fake()->randomElement(['ACM0001', 'AMS-III.D', 'VM0010']),
                'verifier_name' => fake()->company . ' Methane Verification',
                'additionality_demonstrated' => fake()->boolean(90),
                'co_benefits' => json_encode([
                    'air_quality' => 'Mejora en calidad del aire',
                    'safety' => 'Reducción de riesgos de explosión',
                    'energy' => 'Generación de energía renovable'
                ]),
                'sdg_contributions' => json_encode(['7', '13', '15']),
                'monitoring_frequency' => 'continuous',
                'risk_rating' => 'medium',
                'actual_co2_reduced' => $totalCredits * fake()->randomFloat(2, 0.9, 1.1),
                'project_capacity_mw' => fake()->randomFloat(3, 0.1, 5),
                'expected_project_lifetime_years' => fake()->numberBetween(15, 25),
                'is_active' => true,
            ]);
        }
    }
}
