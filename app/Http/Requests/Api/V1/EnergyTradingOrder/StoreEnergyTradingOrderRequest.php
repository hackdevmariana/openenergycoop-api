<?php

namespace App\Http\Requests\Api\V1\EnergyTradingOrder;

use App\Models\EnergyTradingOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnergyTradingOrderRequest extends FormRequest
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
            'order_number' => 'required|string|max:255|unique:energy_trading_orders,order_number',
            'order_type' => ['required', 'string', Rule::in(array_keys(EnergyTradingOrder::getOrderTypes()))],
            'order_status' => ['required', 'string', Rule::in(array_keys(EnergyTradingOrder::getOrderStatuses()))],
            'order_side' => ['required', 'string', Rule::in(array_keys(EnergyTradingOrder::getOrderSides()))],
            'trader_id' => 'required|exists:users,id',
            'pool_id' => 'required|exists:energy_pools,id',
            'counterparty_id' => 'nullable|exists:users,id',
            'quantity_mwh' => 'required|numeric|min:0.01|max:999999.99',
            'filled_quantity_mwh' => 'nullable|numeric|min:0|max:999999.99',
            'remaining_quantity_mwh' => 'nullable|numeric|min:0|max:999999.99',
            'price_per_mwh' => 'required|numeric|min:0.01|max:999999.99',
            'total_value' => 'nullable|numeric|min:0|max:999999999.99',
            'filled_value' => 'nullable|numeric|min:0|max:999999999.99',
            'remaining_value' => 'nullable|numeric|min:0|max:999999999.99',
            'price_type' => ['required', 'string', Rule::in(array_keys(EnergyTradingOrder::getPriceTypes()))],
            'price_index' => 'nullable|string|max:255',
            'price_adjustment' => 'nullable|numeric|min:-999999.99|max:999999.99',
            'valid_from' => 'required|date|before_or_equal:now',
            'valid_until' => 'nullable|date|after:valid_from',
            'execution_time' => 'nullable|date|after_or_equal:valid_from',
            'expiry_time' => 'nullable|date|after:valid_from',
            'execution_type' => ['required', 'string', Rule::in(array_keys(EnergyTradingOrder::getExecutionTypes()))],
            'priority' => ['required', 'string', Rule::in(array_keys(EnergyTradingOrder::getPriorities()))],
            'is_negotiable' => 'boolean',
            'negotiation_terms' => 'nullable|string|max:1000',
            'special_conditions' => 'nullable|string|max:1000',
            'delivery_requirements' => 'nullable|string|max:1000',
            'payment_terms' => 'nullable|string|max:1000',
            'order_conditions' => 'nullable|array',
            'order_restrictions' => 'nullable|array',
            'order_metadata' => 'nullable|array',
            'tags' => 'nullable|array',
            'created_by' => 'nullable|exists:users,id',
            'approved_by' => 'nullable|exists:users,id',
            'approved_at' => 'nullable|date|before_or_equal:now',
            'executed_by' => 'nullable|exists:users,id',
            'executed_at' => 'nullable|date|after_or_equal:valid_from',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'order_number.required' => 'El número de orden es obligatorio.',
            'order_number.unique' => 'El número de orden ya existe.',
            'order_type.required' => 'El tipo de orden es obligatorio.',
            'order_type.in' => 'El tipo de orden seleccionado no es válido.',
            'order_status.required' => 'El estado de la orden es obligatorio.',
            'order_status.in' => 'El estado de la orden seleccionado no es válido.',
            'order_side.required' => 'El lado de la orden es obligatorio.',
            'order_side.in' => 'El lado de la orden seleccionado no es válido.',
            'trader_id.required' => 'El trader es obligatorio.',
            'trader_id.exists' => 'El trader seleccionado no existe.',
            'pool_id.required' => 'El pool de energía es obligatorio.',
            'pool_id.exists' => 'El pool de energía seleccionado no existe.',
            'counterparty_id.exists' => 'La contraparte seleccionada no existe.',
            'quantity_mwh.required' => 'La cantidad en MWh es obligatoria.',
            'quantity_mwh.numeric' => 'La cantidad debe ser un número.',
            'quantity_mwh.min' => 'La cantidad mínima es 0.01 MWh.',
            'quantity_mwh.max' => 'La cantidad máxima es 999,999.99 MWh.',
            'price_per_mwh.required' => 'El precio por MWh es obligatorio.',
            'price_per_mwh.numeric' => 'El precio debe ser un número.',
            'price_per_mwh.min' => 'El precio mínimo es $0.01 por MWh.',
            'price_per_mwh.max' => 'El precio máximo es $999,999.99 por MWh.',
            'price_type.required' => 'El tipo de precio es obligatorio.',
            'price_type.in' => 'El tipo de precio seleccionado no es válido.',
            'price_adjustment.numeric' => 'El ajuste de precio debe ser un número.',
            'price_adjustment.min' => 'El ajuste de precio mínimo es -$999,999.99.',
            'price_adjustment.max' => 'El ajuste de precio máximo es $999,999.99.',
            'valid_from.required' => 'La fecha de validez desde es obligatoria.',
            'valid_from.date' => 'La fecha de validez desde debe ser una fecha válida.',
            'valid_from.before_or_equal' => 'La fecha de validez desde debe ser anterior o igual a hoy.',
            'valid_until.date' => 'La fecha de validez hasta debe ser una fecha válida.',
            'valid_until.after' => 'La fecha de validez hasta debe ser posterior a la fecha de validez desde.',
            'execution_time.date' => 'La fecha de ejecución debe ser una fecha válida.',
            'execution_time.after_or_equal' => 'La fecha de ejecución debe ser posterior o igual a la fecha de validez desde.',
            'expiry_time.date' => 'La fecha de expiración debe ser una fecha válida.',
            'expiry_time.after' => 'La fecha de expiración debe ser posterior a la fecha de validez desde.',
            'execution_type.required' => 'El tipo de ejecución es obligatorio.',
            'execution_type.in' => 'El tipo de ejecución seleccionado no es válido.',
            'priority.required' => 'La prioridad es obligatoria.',
            'priority.in' => 'La prioridad seleccionada no es válida.',
            'is_negotiable.boolean' => 'El campo negociable debe ser verdadero o falso.',
            'negotiation_terms.max' => 'Los términos de negociación no pueden exceder 1000 caracteres.',
            'special_conditions.max' => 'Las condiciones especiales no pueden exceder 1000 caracteres.',
            'delivery_requirements.max' => 'Los requisitos de entrega no pueden exceder 1000 caracteres.',
            'payment_terms.max' => 'Los términos de pago no pueden exceder 1000 caracteres.',
            'order_conditions.array' => 'Las condiciones de la orden deben ser un array.',
            'order_restrictions.array' => 'Las restricciones de la orden deben ser un array.',
            'order_metadata.array' => 'Los metadatos de la orden deben ser un array.',
            'tags.array' => 'Las etiquetas deben ser un array.',
            'created_by.exists' => 'El usuario creador seleccionado no existe.',
            'approved_by.exists' => 'El usuario aprobador seleccionado no existe.',
            'approved_at.date' => 'La fecha de aprobación debe ser una fecha válida.',
            'approved_at.before_or_equal' => 'La fecha de aprobación debe ser anterior o igual a hoy.',
            'executed_by.exists' => 'El usuario ejecutor seleccionado no existe.',
            'executed_at.date' => 'La fecha de ejecución debe ser una fecha válida.',
            'executed_at.after_or_equal' => 'La fecha de ejecución debe ser posterior o igual a la fecha de validez desde.',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Asignar usuario creador si no se proporciona
        if (!$this->filled('created_by')) {
            $this->merge(['created_by' => auth()->id()]);
        }

        // Convertir campos booleanos
        if ($this->has('is_negotiable')) {
            $this->merge(['is_negotiable' => $this->boolean('is_negotiable')]);
        }

        // Convertir campos numéricos
        $numericFields = [
            'quantity_mwh', 'filled_quantity_mwh', 'remaining_quantity_mwh',
            'price_per_mwh', 'total_value', 'filled_value', 'remaining_value',
            'price_adjustment'
        ];

        foreach ($numericFields as $field) {
            if ($this->filled($field)) {
                $this->merge([$field => (float) $this->input($field)]);
            }
        }

        // Decodificar campos JSON si vienen como string
        $jsonFields = ['order_conditions', 'order_restrictions', 'order_metadata', 'tags'];
        
        foreach ($jsonFields as $field) {
            if ($this->filled($field) && is_string($this->input($field))) {
                $decoded = json_decode($this->input($field), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->merge([$field => $decoded]);
                }
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que la cantidad restante no exceda la cantidad total
            if ($this->filled('filled_quantity_mwh') && $this->filled('quantity_mwh')) {
                if ($this->filled_quantity_mwh > $this->quantity_mwh) {
                    $validator->errors()->add('filled_quantity_mwh', 'La cantidad llenada no puede exceder la cantidad total.');
                }
            }

            // Validar que la cantidad restante sea consistente
            if ($this->filled('remaining_quantity_mwh') && $this->filled('quantity_mwh') && $this->filled('filled_quantity_mwh')) {
                $expectedRemaining = $this->quantity_mwh - $this->filled_quantity_mwh;
                if (abs($this->remaining_quantity_mwh - $expectedRemaining) > 0.01) {
                    $validator->errors()->add('remaining_quantity_mwh', 'La cantidad restante debe ser igual a la cantidad total menos la cantidad llenada.');
                }
            }

            // Validar fechas de validez
            if ($this->filled('valid_until') && $this->filled('valid_from')) {
                if ($this->valid_until <= $this->valid_from) {
                    $validator->errors()->add('valid_until', 'La fecha de validez hasta debe ser posterior a la fecha de validez desde.');
                }
            }

            // Validar que la fecha de ejecución esté dentro del período de validez
            if ($this->filled('execution_time') && $this->filled('valid_from')) {
                if ($this->execution_time < $this->valid_from) {
                    $validator->errors()->add('execution_time', 'La fecha de ejecución debe ser posterior o igual a la fecha de validez desde.');
                }
            }

            // Validar que la fecha de expiración esté después de la fecha de validez desde
            if ($this->filled('expiry_time') && $this->filled('valid_from')) {
                if ($this->expiry_time <= $this->valid_from) {
                    $validator->errors()->add('expiry_time', 'La fecha de expiración debe ser posterior a la fecha de validez desde.');
                }
            }

            // Validar que el precio ajustado sea positivo si se proporciona
            if ($this->filled('price_adjustment') && $this->filled('price_per_mwh')) {
                $adjustedPrice = $this->price_per_mwh + $this->price_adjustment;
                if ($adjustedPrice <= 0) {
                    $validator->errors()->add('price_adjustment', 'El precio ajustado debe ser mayor a cero.');
                }
            }
        });
    }
}
