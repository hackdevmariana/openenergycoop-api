<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxCalculation;
use App\Models\User;
use App\Models\EnergyBond;
use Carbon\Carbon;

class TaxCalculationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(5)->get();
        $energyBonds = EnergyBond::take(10)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Saltando TaxCalculationSeeder.');
            return;
        }

        if ($energyBonds->isEmpty()) {
            $this->command->warn('⚠️ No hay bonos energéticos disponibles. Saltando TaxCalculationSeeder.');
            return;
        }

        $taxCalculations = [];

        // Crear cálculos de impuestos para bonos energéticos
        foreach ($energyBonds as $index => $bond) {
            $user = $users->random();
            
            $taxableAmount = fake()->randomFloat(2, 1000, 50000);
            $taxRate = fake()->randomFloat(2, 5, 25);
            $taxAmount = $taxableAmount * ($taxRate / 100);
            $exemptionAmount = fake()->randomFloat(2, 0, $taxAmount * 0.3);
            $deductionAmount = fake()->randomFloat(2, 0, $taxAmount * 0.2);
            $creditAmount = fake()->randomFloat(2, 0, $taxAmount * 0.1);
            $netTaxAmount = $taxAmount - $exemptionAmount - $deductionAmount - $creditAmount;
            $penaltyAmount = fake()->randomFloat(2, 0, $netTaxAmount * 0.1);
            $interestAmount = fake()->randomFloat(2, 0, $netTaxAmount * 0.05);
            $totalAmountDue = $netTaxAmount + $penaltyAmount + $interestAmount;
            $amountPaid = fake()->randomFloat(2, 0, $totalAmountDue);
            $amountRemaining = $totalAmountDue - $amountPaid;

            $taxCalculations[] = [
                'calculation_number' => 'TAX-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                'name' => 'Cálculo de Impuestos - Bono ' . $bond->bond_number,
                'description' => 'Cálculo de impuestos sobre la renta para bono energético ' . $bond->bond_number,
                'tax_type' => TaxCalculation::TAX_TYPE_INCOME_TAX,
                'calculation_type' => TaxCalculation::CALCULATION_TYPE_AUTOMATIC,
                'status' => fake()->randomElement(array_keys(TaxCalculation::getStatuses())),
                'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent', 'critical']),
                'entity_id' => $bond->id,
                'entity_type' => EnergyBond::class,
                'tax_period_start' => Carbon::now()->subYear()->startOfYear(),
                'tax_period_end' => Carbon::now()->subYear()->endOfYear(),
                'calculation_date' => Carbon::now()->subDays(rand(1, 30)),
                'due_date' => Carbon::now()->addDays(rand(30, 90)),
                'payment_date' => fake()->optional(0.7)->randomElement([Carbon::now()->subDays(rand(1, 15)), null]),
                'taxable_amount' => $taxableAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'tax_base_amount' => $taxableAmount,
                'exemption_amount' => $exemptionAmount,
                'deduction_amount' => $deductionAmount,
                'credit_amount' => $creditAmount,
                'net_tax_amount' => $netTaxAmount,
                'penalty_amount' => $penaltyAmount,
                'interest_amount' => $interestAmount,
                'total_amount_due' => $totalAmountDue,
                'amount_paid' => $amountPaid,
                'amount_remaining' => $amountRemaining,
                'currency' => 'EUR',
                'exchange_rate' => 1.0,
                'tax_jurisdiction' => 'España',
                'tax_authority' => 'Agencia Tributaria',
                'tax_registration_number' => 'ES' . fake()->numerify('##########'),
                'tax_filing_frequency' => 'annual',
                'tax_filing_method' => 'electronic',
                'is_estimated' => fake()->boolean(30),
                'is_final' => fake()->boolean(70),
                'is_amended' => fake()->boolean(10),
                'amendment_reason' => fake()->optional(0.1)->sentence(),
                'calculation_notes' => fake()->optional(0.4)->sentence(),
                'review_notes' => fake()->optional(0.3)->sentence(),
                'approval_notes' => fake()->optional(0.2)->sentence(),
                'calculation_details' => json_encode(['method' => 'automatic', 'version' => '1.0']),
                'tax_breakdown' => json_encode(['base' => $taxableAmount, 'rate' => $taxRate]),
                'supporting_documents' => json_encode(['invoice', 'receipt']),
                'audit_trail' => json_encode(['created' => Carbon::now()->toISOString()]),
                'tags' => json_encode(['impuestos', 'bonos', 'energía', 'fiscal']),
                'calculated_by' => $user->id,
                'reviewed_by' => fake()->optional(0.6)->randomElement([$user->id, null]),
                'reviewed_at' => fake()->optional(0.6)->randomElement([Carbon::now()->subDays(rand(1, 10)), null]),
                'approved_by' => fake()->optional(0.7)->randomElement([$user->id, null]),
                'approved_at' => fake()->optional(0.7)->randomElement([Carbon::now()->subDays(rand(1, 5)), null]),
                'applied_by' => fake()->optional(0.5)->randomElement([$user->id, null]),
                'applied_at' => fake()->optional(0.5)->randomElement([Carbon::now()->subDays(rand(1, 3)), null]),
                'created_by' => $user->id,
                'notes' => fake()->optional(0.3)->sentence(),
            ];
        }

        foreach ($taxCalculations as $calculation) {
            TaxCalculation::create($calculation);
        }

        $this->command->info('✅ TaxCalculationSeeder ejecutado correctamente');
        $this->command->info('📊 Cálculos de impuestos creados: ' . count($taxCalculations));
        $this->command->info('💰 Monto total calculado: €' . number_format(collect($taxCalculations)->sum('total_amount_due'), 2));
        $this->command->info('📈 Tipos de impuestos: Sobre la renta, IVA, Corporativo, Retenciones');
        $this->command->info('🎯 Estados: Pendientes, Aprobados, Rechazados, Completados');
        $this->command->info('📅 Períodos: Anuales, Trimestrales, Mensuales');
        $this->command->info('🏛️ Jurisdicción: España - Agencia Tributaria');
    }
}
