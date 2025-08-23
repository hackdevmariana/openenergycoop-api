<?php

namespace Database\Factories;

use App\Models\TaxCalculation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxCalculationFactory extends Factory
{
    protected $model = TaxCalculation::class;

    public function definition(): array
    {
        $taxTypes = array_keys(TaxCalculation::getTaxTypes());
        $calculationTypes = array_keys(TaxCalculation::getCalculationTypes());
        $statuses = array_keys(TaxCalculation::getStatuses());
        $priorities = array_keys(TaxCalculation::getPriorities());
        $currencies = ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD'];
        
        $taxableAmount = $this->faker->randomFloat(2, 100, 100000);
        $taxRate = $this->faker->randomFloat(2, 5, 50);
        $taxAmount = ($taxableAmount * $taxRate) / 100;
        $exemptionAmount = $this->faker->randomFloat(2, 0, $taxableAmount * 0.3);
        $deductionAmount = $this->faker->randomFloat(2, 0, $taxableAmount * 0.2);
        $creditAmount = $this->faker->randomFloat(2, 0, $taxAmount * 0.5);
        $netTaxAmount = $taxAmount - $exemptionAmount - $deductionAmount - $creditAmount;
        $penaltyAmount = $this->faker->randomFloat(2, 0, $netTaxAmount * 0.1);
        $interestAmount = $this->faker->randomFloat(2, 0, $netTaxAmount * 0.05);
        $totalAmountDue = $netTaxAmount + $penaltyAmount + $interestAmount;
        $amountPaid = $this->faker->randomFloat(2, 0, $totalAmountDue);
        $amountRemaining = $totalAmountDue - $amountPaid;

        return [
            'calculation_number' => 'TC' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'tax_type' => $this->faker->randomElement($taxTypes),
            'calculation_type' => $this->faker->randomElement($calculationTypes),
            'status' => $this->faker->randomElement($statuses),
            'priority' => $this->faker->randomElement($priorities),
            'entity_id' => $this->faker->numberBetween(1, 100),
            'entity_type' => $this->faker->randomElement(['App\\Models\\User', 'App\\Models\\Company', 'App\\Models\\Project']),
            'transaction_id' => $this->faker->numberBetween(1, 1000),
            'transaction_type' => $this->faker->randomElement(['App\\Models\\Invoice', 'App\\Models\\Purchase', 'App\\Models\\Sale']),
            'tax_period_start' => $this->faker->dateTimeBetween('-1 year', '-1 month'),
            'tax_period_end' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'calculation_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'payment_date' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', 'now'),
            'taxable_amount' => $taxableAmount,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'tax_base_amount' => $this->faker->randomFloat(2, $taxableAmount * 0.8, $taxableAmount * 1.2),
            'exemption_amount' => $exemptionAmount,
            'deduction_amount' => $deductionAmount,
            'credit_amount' => $creditAmount,
            'net_tax_amount' => $netTaxAmount,
            'penalty_amount' => $penaltyAmount,
            'interest_amount' => $interestAmount,
            'total_amount_due' => $totalAmountDue,
            'amount_paid' => $amountPaid,
            'amount_remaining' => $amountRemaining,
            'currency' => $this->faker->randomElement($currencies),
            'exchange_rate' => $this->faker->randomFloat(6, 0.5, 2.0),
            'tax_jurisdiction' => $this->faker->city() . ', ' . $this->faker->country(),
            'tax_authority' => $this->faker->company() . ' Tax Authority',
            'tax_registration_number' => 'TAX' . $this->faker->unique()->numberBetween(10000, 99999),
            'tax_filing_frequency' => $this->faker->randomElement(['monthly', 'quarterly', 'annually']),
            'tax_filing_method' => $this->faker->randomElement(['electronic', 'paper', 'online']),
            'is_estimated' => $this->faker->boolean(20),
            'is_final' => $this->faker->boolean(80),
            'is_amended' => $this->faker->boolean(10),
            'amendment_reason' => $this->faker->optional()->sentence(),
            'calculation_notes' => $this->faker->optional()->paragraph(),
            'review_notes' => $this->faker->optional()->paragraph(),
            'approval_notes' => $this->faker->optional()->paragraph(),
            'calculation_details' => [
                'method' => $this->faker->randomElement(['standard', 'simplified', 'detailed']),
                'version' => $this->faker->randomElement(['1.0', '2.0', '3.0']),
                'parameters' => [
                    'base_rate' => $taxRate,
                    'adjustments' => $this->faker->randomFloat(2, -5, 5),
                    'special_conditions' => $this->faker->optional()->sentence()
                ]
            ],
            'tax_breakdown' => [
                'base_tax' => $taxAmount,
                'exemptions' => [
                    'amount' => $exemptionAmount,
                    'reason' => $this->faker->sentence()
                ],
                'deductions' => [
                    'amount' => $deductionAmount,
                    'category' => $this->faker->word()
                ],
                'credits' => [
                    'amount' => $creditAmount,
                    'type' => $this->faker->word()
                ]
            ],
            'supporting_documents' => [
                'invoices' => $this->faker->numberBetween(1, 10),
                'receipts' => $this->faker->numberBetween(1, 20),
                'contracts' => $this->faker->numberBetween(0, 5)
            ],
            'audit_trail' => [
                'created_at' => now()->toISOString(),
                'created_by' => $this->faker->name(),
                'last_modified' => now()->toISOString(),
                'modifications' => []
            ],
            'tags' => $this->faker->words(3),
            'notes' => $this->faker->optional()->paragraph(),
            'calculated_by' => User::factory(),
            'reviewed_by' => $this->faker->optional(0.3)->randomElement([User::factory()]),
            'approved_by' => $this->faker->optional(0.2)->randomElement([User::factory()]),
            'applied_by' => $this->faker->optional(0.1)->randomElement([User::factory()]),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Estado para cálculos de impuestos sobre la renta
     */
    public function incomeTax(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tax_type' => 'income_tax',
                'calculation_type' => 'manual',
                'priority' => 'high',
                'tax_jurisdiction' => 'National Tax Authority',
                'tax_filing_frequency' => 'annually',
            ];
        });
    }

    /**
     * Estado para cálculos de impuestos sobre ventas
     */
    public function salesTax(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tax_type' => 'sales_tax',
                'calculation_type' => 'automatic',
                'priority' => 'medium',
                'tax_filing_frequency' => 'monthly',
                'is_estimated' => false,
                'is_final' => true,
            ];
        });
    }

    /**
     * Estado para cálculos de impuestos sobre la propiedad
     */
    public function propertyTax(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tax_type' => 'property_tax',
                'calculation_type' => 'manual',
                'priority' => 'medium',
                'tax_filing_frequency' => 'annually',
                'tax_jurisdiction' => 'Local Tax Authority',
            ];
        });
    }

    /**
     * Estado para cálculos de impuestos especiales
     */
    public function exciseTax(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tax_type' => 'excise_tax',
                'calculation_type' => 'automatic',
                'priority' => 'low',
                'tax_filing_frequency' => 'quarterly',
            ];
        });
    }

    /**
     * Estado para cálculos de aranceles aduaneros
     */
    public function customsDuty(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tax_type' => 'customs_duty',
                'calculation_type' => 'manual',
                'priority' => 'critical',
                'currency' => 'USD',
                'exchange_rate' => 1.0,
            ];
        });
    }

    /**
     * Estado para cálculos de alta prioridad
     */
    public function highPriority(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'high',
                'due_date' => now()->addDays(7),
                'is_final' => true,
            ];
        });
    }

    /**
     * Estado para cálculos críticos
     */
    public function criticalPriority(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'critical',
                'due_date' => now()->addDays(3),
                'is_final' => true,
                'penalty_amount' => $attributes['net_tax_amount'] * 0.2,
            ];
        });
    }

    /**
     * Estado para cálculos vencidos
     */
    public function overdue(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'due_date' => now()->subDays($this->faker->numberBetween(1, 30)),
                'penalty_amount' => $attributes['net_tax_amount'] * 0.1,
                'interest_amount' => $attributes['net_tax_amount'] * 0.05,
            ];
        });
    }

    /**
     * Estado para cálculos próximos a vencer
     */
    public function dueSoon(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'due_date' => now()->addDays($this->faker->numberBetween(1, 7)),
                'priority' => 'high',
            ];
        });
    }

    /**
     * Estado para cálculos estimados
     */
    public function estimated(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_estimated' => true,
                'is_final' => false,
                'status' => 'draft',
                'calculation_notes' => 'Cálculo estimado basado en datos preliminares',
            ];
        });
    }

    /**
     * Estado para cálculos finales
     */
    public function final(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_estimated' => false,
                'is_final' => true,
                'status' => 'approved',
                'calculation_notes' => 'Cálculo final validado y aprobado',
            ];
        });
    }

    /**
     * Estado para cálculos en borrador
     */
    public function draft(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'draft',
                'is_estimated' => true,
                'is_final' => false,
                'calculated_by' => null,
                'reviewed_by' => null,
                'approved_by' => null,
                'applied_by' => null,
            ];
        });
    }

    /**
     * Estado para cálculos calculados
     */
    public function calculated(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'calculated',
                'is_estimated' => false,
                'is_final' => true,
                'calculation_date' => now(),
            ];
        });
    }

    /**
     * Estado para cálculos revisados
     */
    public function reviewed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'reviewed',
                'is_estimated' => false,
                'is_final' => true,
                'review_notes' => 'Cálculo revisado y validado',
            ];
        });
    }

    /**
     * Estado para cálculos aprobados
     */
    public function approved(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
                'is_estimated' => false,
                'is_final' => true,
                'approval_notes' => 'Cálculo aprobado para aplicación',
            ];
        });
    }

    /**
     * Estado para cálculos aplicados
     */
    public function applied(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'applied',
                'is_estimated' => false,
                'is_final' => true,
                'applied_by' => User::factory(),
                'payment_date' => now(),
                'amount_paid' => $attributes['total_amount_due'],
                'amount_remaining' => 0,
            ];
        });
    }

    /**
     * Estado para cálculos cancelados
     */
    public function cancelled(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
                'calculation_notes' => 'Cálculo cancelado por solicitud del usuario',
            ];
        });
    }

    /**
     * Estado para cálculos con error
     */
    public function error(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'error',
                'calculation_notes' => 'Error en el cálculo: datos inconsistentes',
            ];
        });
    }

    /**
     * Estado para cálculos en EUR
     */
    public function inEur(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'currency' => 'EUR',
                'exchange_rate' => 1.0,
            ];
        });
    }

    /**
     * Estado para cálculos en USD
     */
    public function inUsd(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'currency' => 'USD',
                'exchange_rate' => 1.0,
            ];
        });
    }

    /**
     * Estado para cálculos con montos altos
     */
    public function highAmount(): static
    {
        return $this->state(function (array $attributes) {
            $taxableAmount = $this->faker->randomFloat(2, 100000, 1000000);
            $taxRate = $this->faker->randomFloat(2, 15, 35);
            $taxAmount = ($taxableAmount * $taxRate) / 100;
            
            return [
                'taxable_amount' => $taxableAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'priority' => 'critical',
            ];
        });
    }

    /**
     * Estado para cálculos con montos bajos
     */
    public function lowAmount(): static
    {
        return $this->state(function (array $attributes) {
            $taxableAmount = $this->faker->randomFloat(2, 100, 1000);
            $taxRate = $this->faker->randomFloat(2, 5, 15);
            $taxAmount = ($taxableAmount * $taxRate) / 100;
            
            return [
                'taxable_amount' => $taxableAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'priority' => 'low',
            ];
        });
    }
}
