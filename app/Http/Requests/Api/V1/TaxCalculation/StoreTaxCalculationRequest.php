<?php

namespace App\Http\Requests\Api\V1\TaxCalculation;

use App\Models\TaxCalculation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaxCalculationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'calculation_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('tax_calculations', 'calculation_number'),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'tax_type' => [
                'required',
                'string',
                Rule::in(array_keys(TaxCalculation::getTaxTypes())),
            ],
            'calculation_type' => [
                'required',
                'string',
                Rule::in(array_keys(TaxCalculation::getCalculationTypes())),
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(array_keys(TaxCalculation::getStatuses())),
            ],
            'priority' => [
                'nullable',
                'string',
                Rule::in(array_keys(TaxCalculation::getPriorities())),
            ],
            'entity_id' => 'nullable|integer',
            'entity_type' => 'nullable|string|max:255',
            'transaction_id' => 'nullable|integer',
            'transaction_type' => 'nullable|string|max:255',
            'tax_period_start' => 'required|date',
            'tax_period_end' => 'required|date|after:tax_period_start',
            'calculation_date' => 'nullable|date',
            'due_date' => 'nullable|date|after:tax_period_end',
            'payment_date' => 'nullable|date|after:calculation_date',
            'taxable_amount' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'tax_base_amount' => 'nullable|numeric|min:0',
            'exemption_amount' => 'nullable|numeric|min:0',
            'deduction_amount' => 'nullable|numeric|min:0',
            'credit_amount' => 'nullable|numeric|min:0',
            'net_tax_amount' => 'nullable|numeric',
            'penalty_amount' => 'nullable|numeric|min:0',
            'interest_amount' => 'nullable|numeric|min:0',
            'total_amount_due' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'amount_remaining' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'exchange_rate' => 'nullable|numeric|min:0',
            'tax_jurisdiction' => 'nullable|string|max:100',
            'tax_authority' => 'nullable|string|max:100',
            'tax_registration_number' => 'nullable|string|max:100',
            'tax_filing_frequency' => 'nullable|string|max:100',
            'tax_filing_method' => 'nullable|string|max:100',
            'is_estimated' => 'nullable|boolean',
            'is_final' => 'nullable|boolean',
            'is_amended' => 'nullable|boolean',
            'amendment_reason' => 'nullable|string|max:500',
            'calculation_notes' => 'nullable|string|max:1000',
            'review_notes' => 'nullable|string|max:1000',
            'approval_notes' => 'nullable|string|max:1000',
            'calculation_details' => 'nullable|array',
            'tax_breakdown' => 'nullable|array',
            'supporting_documents' => 'nullable|array',
            'audit_trail' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'tax_type.in' => 'El tipo de impuesto debe ser uno de los valores permitidos.',
            'calculation_type.in' => 'El tipo de cálculo debe ser uno de los valores permitidos.',
            'status.in' => 'El estado debe ser uno de los valores permitidos.',
            'priority.in' => 'La prioridad debe ser uno de los valores permitidos.',
            'tax_period_end.after' => 'La fecha de fin del período fiscal debe ser posterior a la fecha de inicio.',
            'due_date.after' => 'La fecha de vencimiento debe ser posterior al final del período fiscal.',
            'payment_date.after' => 'La fecha de pago debe ser posterior a la fecha de cálculo.',
            'taxable_amount.min' => 'El monto imponible debe ser al menos 0.',
            'tax_rate.min' => 'La tasa de impuesto debe ser al menos 0.',
            'tax_rate.max' => 'La tasa de impuesto no puede ser mayor a 100.',
            'tax_amount.min' => 'El monto del impuesto debe ser al menos 0.',
            'tax_base_amount.min' => 'El monto base del impuesto debe ser al menos 0.',
            'exemption_amount.min' => 'El monto de exención debe ser al menos 0.',
            'deduction_amount.min' => 'El monto de deducción debe ser al menos 0.',
            'credit_amount.min' => 'El monto de crédito debe ser al menos 0.',
            'penalty_amount.min' => 'El monto de penalización debe ser al menos 0.',
            'interest_amount.min' => 'El monto de interés debe ser al menos 0.',
            'total_amount_due.min' => 'El monto total adeudado debe ser al menos 0.',
            'amount_paid.min' => 'El monto pagado debe ser al menos 0.',
            'amount_remaining.min' => 'El monto restante debe ser al menos 0.',
            'exchange_rate.min' => 'La tasa de cambio debe ser al menos 0.',
            'tags.*.max' => 'Cada etiqueta no puede tener más de 100 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Asignar created_by si no está presente
        if (!$this->has('created_by')) {
            $this->merge(['created_by' => auth()->id()]);
        }

        // Generar calculation_number si no está presente
        if (!$this->has('calculation_number')) {
            $this->merge(['calculation_number' => 'TC-' . uniqid()]);
        }

        // Establecer status por defecto
        if (!$this->has('status')) {
            $this->merge(['status' => 'draft']);
        }

        // Establecer priority por defecto
        if (!$this->has('priority')) {
            $this->merge(['priority' => 'normal']);
        }

        // Establecer calculation_date por defecto
        if (!$this->has('calculation_date')) {
            $this->merge(['calculation_date' => now()]);
        }

        // Establecer currency por defecto
        if (!$this->has('currency')) {
            $this->merge(['currency' => 'EUR']);
        }

        // Establecer exchange_rate por defecto
        if (!$this->has('exchange_rate')) {
            $this->merge(['exchange_rate' => 1.0]);
        }

        // Establecer is_estimated por defecto
        if (!$this->has('is_estimated')) {
            $this->merge(['is_estimated' => false]);
        }

        // Establecer is_final por defecto
        if (!$this->has('is_final')) {
            $this->merge(['is_final' => false]);
        }

        // Establecer is_amended por defecto
        if (!$this->has('is_amended')) {
            $this->merge(['is_amended' => false]);
        }

        // Convertir campos numéricos
        $numericFields = [
            'taxable_amount', 'tax_rate', 'tax_amount', 'tax_base_amount',
            'exemption_amount', 'deduction_amount', 'credit_amount', 'net_tax_amount',
            'penalty_amount', 'interest_amount', 'total_amount_due', 'amount_paid',
            'amount_remaining', 'exchange_rate'
        ];

        foreach ($numericFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => (float) $this->input($field)]);
            }
        }

        // Convertir campos de array
        $arrayFields = [
            'calculation_details', 'tax_breakdown', 'supporting_documents',
            'audit_trail', 'tags'
        ];

        foreach ($arrayFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => json_decode($this->input($field), true)]);
            }
        }

        // Convertir campos booleanos
        $booleanFields = ['is_estimated', 'is_final', 'is_amended'];
        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $this->merge([$field => (bool) $this->input($field)]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que el monto del impuesto sea consistente con la tasa y el monto imponible
            if ($this->filled('tax_amount') && $this->filled('taxable_amount') && $this->filled('tax_rate')) {
                $expectedTaxAmount = ($this->taxable_amount * $this->tax_rate) / 100;
                $tolerance = 0.01; // Tolerancia de 1 céntimo
                
                if (abs($this->tax_amount - $expectedTaxAmount) > $tolerance) {
                    $validator->errors()->add('tax_amount', 'El monto del impuesto no es consistente con la tasa y el monto imponible.');
                }
            }

            // Validar que el monto neto del impuesto sea consistente
            if ($this->filled('net_tax_amount') && $this->filled('tax_amount') && 
                $this->filled('exemption_amount') && $this->filled('deduction_amount') && $this->filled('credit_amount')) {
                $expectedNetAmount = $this->tax_amount - $this->exemption_amount - $this->deduction_amount - $this->credit_amount;
                $tolerance = 0.01;
                
                if (abs($this->net_tax_amount - $expectedNetAmount) > $tolerance) {
                    $validator->errors()->add('net_tax_amount', 'El monto neto del impuesto no es consistente con los montos de exención, deducción y crédito.');
                }
            }

            // Validar que el monto total adeudado sea consistente
            if ($this->filled('total_amount_due') && $this->filled('net_tax_amount') && 
                $this->filled('penalty_amount') && $this->filled('interest_amount')) {
                $expectedTotal = $this->net_tax_amount + $this->penalty_amount + $this->interest_amount;
                $tolerance = 0.01;
                
                if (abs($this->total_amount_due - $expectedTotal) > $tolerance) {
                    $validator->errors()->add('total_amount_due', 'El monto total adeudado no es consistente con el impuesto neto, penalizaciones e intereses.');
                }
            }

            // Validar que el monto restante sea consistente
            if ($this->filled('amount_remaining') && $this->filled('total_amount_due') && $this->filled('amount_paid')) {
                $expectedRemaining = $this->total_amount_due - $this->amount_paid;
                $tolerance = 0.01;
                
                if (abs($this->amount_remaining - $expectedRemaining) > $tolerance) {
                    $validator->errors()->add('amount_remaining', 'El monto restante no es consistente con el total adeudado y el monto pagado.');
                }
            }

            // Validar que el período fiscal sea razonable (máximo 1 año)
            if ($this->filled('tax_period_start') && $this->filled('tax_period_end')) {
                $startDate = \Carbon\Carbon::parse($this->tax_period_start);
                $endDate = \Carbon\Carbon::parse($this->tax_period_end);
                $duration = $startDate->diffInDays($endDate);
                
                if ($duration > 366) { // Máximo 1 año + 1 día para años bisiestos
                    $validator->errors()->add('tax_period_end', 'El período fiscal no puede ser mayor a 1 año.');
                }
            }

            // Validar que la fecha de vencimiento sea posterior al período fiscal
            if ($this->filled('due_date') && $this->filled('tax_period_end')) {
                $dueDate = \Carbon\Carbon::parse($this->due_date);
                $periodEnd = \Carbon\Carbon::parse($this->tax_period_end);
                
                if ($dueDate <= $periodEnd) {
                    $validator->errors()->add('due_date', 'La fecha de vencimiento debe ser posterior al final del período fiscal.');
                }
            }

            // Validar que la fecha de pago sea posterior a la fecha de cálculo
            if ($this->filled('payment_date') && $this->filled('calculation_date')) {
                $paymentDate = \Carbon\Carbon::parse($this->payment_date);
                $calculationDate = \Carbon\Carbon::parse($this->calculation_date);
                
                if ($paymentDate <= $calculationDate) {
                    $validator->errors()->add('payment_date', 'La fecha de pago debe ser posterior a la fecha de cálculo.');
                }
            }
        });
    }
}
