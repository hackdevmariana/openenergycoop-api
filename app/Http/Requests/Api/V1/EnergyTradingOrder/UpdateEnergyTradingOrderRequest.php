<?php

namespace App\Http\Requests\Api\V1\EnergyTradingOrder;

use App\Models\EnergyTradingOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEnergyTradingOrderRequest extends FormRequest
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
        $energyTradingOrderId = $this->route('energyTradingOrder')->id ?? $this->route('id');
        
        return [
            'order_number' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('energy_trading_orders', 'order_number')->ignore($energyTradingOrderId)
            ],
            'order_type' => ['sometimes', 'string', Rule::in(array_keys(EnergyTradingOrder::getOrderTypes()))],
            'order_status' => ['sometimes', 'string', Rule::in(array_keys(EnergyTradingOrder::getOrderStatuses()))],
            'order_side' => ['sometimes', 'string', Rule::in(array_keys(EnergyTradingOrder::getOrderSides()))],
            'trader_id' => 'sometimes|exists:users,id',
            'pool_id' => 'sometimes|exists:energy_pools,id',
            'counterparty_id' => 'sometimes|nullable|exists:users,id',
            'quantity_mwh' => 'sometimes|numeric|min:0.01|max:999999.99',
            'filled_quantity_mwh' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'remaining_quantity_mwh' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'price_per_mwh' => 'sometimes|numeric|min:0.01|max:999999.99',
            'total_value' => 'sometimes|nullable|numeric|min:0|max:999999999.99',
            'filled_value' => 'sometimes|nullable|numeric|min:0|max:999999999.99',
            'remaining_value' => 'sometimes|nullable|numeric|min:0|max:999999999.99',
            'price_type' => ['sometimes', 'string', Rule::in(array_keys(EnergyTradingOrder::getPriceTypes()))],
            'price_index' => 'sometimes|nullable|string|max:255',
            'price_adjustment' => 'sometimes|nullable|numeric|min:-999999.99|max:999999.99',
            'valid_from' => 'sometimes|date|before_or_equal:now',
            'valid_until' => 'sometimes|nullable|date|after:valid_from',
            'execution_time' => 'sometimes|nullable|date|after_or_equal:valid_from',
            'expiry_time' => 'sometimes|nullable|date|after:valid_from',
            'execution_type' => ['sometimes', 'string', Rule::in(array_keys(EnergyTradingOrder::getExecutionTypes()))],
            'priority' => ['sometimes', 'string', Rule::in(array_keys(EnergyTradingOrder::getPriorities()))],
            'is_negotiable' => 'sometimes|boolean',
            'negotiation_terms' => 'sometimes|nullable|string|max:1000',
            'special_conditions' => 'sometimes|nullable|string|max:1000',
            'delivery_requirements' => 'sometimes|nullable|string|max:1000',
            'payment_terms' => 'sometimes|nullable|string|max:1000',
            'order_conditions' => 'sometimes|nullable|array',
            'order_restrictions' => 'sometimes|nullable|array',
            'order_metadata' => 'sometimes|nullable|array',
            'tags' => 'sometimes|nullable|array',
            'approved_by' => 'sometimes|nullable|exists:users,id',
            'approved_at' => 'sometimes|nullable|date|before_or_equal:now',
            'executed_by' => 'sometimes|nullable|exists:users,id',
            'executed_at' => 'sometimes|nullable|date|after_or_equal:valid_from',
            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'order_number.unique' => 'El número de orden ya existe.',
            'order_type.in' => 'El tipo de orden seleccionado no es válido.',
            'order_status.in' => 'El estado de la orden seleccionado no es válido.',
            'order_side.in' => 'El lado de la orden seleccionado no es válido.',
            'trader_id.exists' => 'El trader seleccionado no existe.',
            'pool_id.exists' => 'El pool de energía seleccionado no existe.',
            'counterparty_id.exists' => 'La contraparte seleccionada no existe.',
            'quantity_mwh.numeric' => 'La cantidad debe ser un número.',
            'quantity_mwh.min' => 'La cantidad mínima es 0.01 MWh.',
            'quantity_mwh.max' => 'La cantidad máxima es 999,999.99 MWh.',
            'filled_quantity_mwh.numeric' => 'La cantidad llenada debe ser un número.',
            'filled_quantity_mwh.min' => 'La cantidad llenada mínima es 0 MWh.',
            'filled_quantity_mwh.max' => 'La cantidad llenada máxima es 999,999.99 MWh.',
            'remaining_quantity_mwh.numeric' => 'La cantidad restante debe ser un número.',
            'remaining_quantity_mwh.min' => 'La cantidad restante mínima es 0 MWh.',
            'remaining_quantity_mwh.max' => 'La cantidad restante máxima es 999,999.99 MWh.',
            'price_per_mwh.numeric' => 'El precio debe ser un número.',
            'price_per_mwh.min' => 'El precio mínimo es $0.01 por MWh.',
            'price_per_mwh.max' => 'El precio máximo es $999,999.99 por MWh.',
            'total_value.numeric' => 'El valor total debe ser un número.',
            'total_value.min' => 'El valor total mínimo es $0.',
            'total_value.max' => 'El valor total máximo es $999,999,999.99.',
            'filled_value.numeric' => 'El valor llenado debe ser un número.',
            'filled_value.min' => 'El valor llenado mínimo es $0.',
            'filled_value.max' => 'El valor llenado máximo es $999,999,999.99.',
            'remaining_value.numeric' => 'El valor restante debe ser un número.',
            'remaining_value.min' => 'El valor restante mínimo es $0.',
            'remaining_value.max' => 'El valor restante máximo es $999,999,999.99.',
            'price_type.in' => 'El tipo de precio seleccionado no es válido.',
            'price_index.max' => 'El índice de precio no puede exceder 255 caracteres.',
            'price_adjustment.numeric' => 'El ajuste de precio debe ser un número.',
            'price_adjustment.min' => 'El ajuste de precio mínimo es -$999,999.99.',
            'price_adjustment.max' => 'El ajuste de precio máximo es $999,999.99.',
            'valid_from.date' => 'La fecha de validez desde debe ser una fecha válida.',
            'valid_from.before_or_equal' => 'La fecha de validez desde debe ser anterior o igual a hoy.',
            'valid_until.date' => 'La fecha de validez hasta debe ser una fecha válida.',
            'valid_until.after' => 'La fecha de validez hasta debe ser posterior a la fecha de validez desde.',
            'execution_time.date' => 'La fecha de ejecución debe ser una fecha válida.',
            'execution_time.after_or_equal' => 'La fecha de ejecución debe ser posterior o igual a la fecha de validez desde.',
            'expiry_time.date' => 'La fecha de expiración debe ser una fecha válida.',
            'expiry_time.after' => 'La fecha de expiración debe ser posterior a la fecha de validez desde.',
            'execution_type.in' => 'El tipo de ejecución seleccionado no es válido.',
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
            $energyTradingOrder = $this->route('energyTradingOrder');
            
            if (!$energyTradingOrder) {
                return;
            }

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

            // Validar que no se pueda modificar una orden que ya no es modificable
            if (!$energyTradingOrder->canBeModified()) {
                $modifiableFields = ['notes', 'order_conditions', 'order_restrictions', 'order_metadata', 'tags'];
                $requestedFields = array_keys($this->all());
                
                foreach ($requestedFields as $field) {
                    if (!in_array($field, $modifiableFields)) {
                        $validator->errors()->add($field, 'Este campo no puede ser modificado en el estado actual de la orden.');
                    }
                }
            }

            // Validar que no se pueda cambiar el estado a uno inválido
            if ($this->filled('order_status')) {
                $currentStatus = $energyTradingOrder->order_status;
                $newStatus = $this->order_status;
                
                // Validar transiciones de estado válidas
                $validTransitions = [
                    'pending' => ['active', 'rejected', 'cancelled'],
                    'active' => ['filled', 'partially_filled', 'cancelled', 'expired'],
                    'partially_filled' => ['filled', 'cancelled', 'expired'],
                    'filled' => ['completed'],
                    'rejected' => [],
                    'cancelled' => [],
                    'expired' => [],
                    'completed' => []
                ];
                
                if (isset($validTransitions[$currentStatus]) && !in_array($newStatus, $validTransitions[$currentStatus])) {
                    $validator->errors()->add('order_status', "No se puede cambiar el estado de '{$currentStatus}' a '{$newStatus}'.");
                }
            }
        });
    }
}
