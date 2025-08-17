<?php

namespace App\Http\Requests\Api\V1\UserAsset;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Autorización manejada por middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'quantity' => 'sometimes|numeric|min:0.0001',
            'total_investment' => 'sometimes|numeric|min:0.01',
            'current_value' => 'sometimes|numeric|min:0',
            'daily_yield' => 'nullable|numeric|min:0',
            'auto_reinvest' => 'nullable|boolean',
            'reinvest_threshold' => 'nullable|numeric|min:0',
            'risk_tolerance' => 'nullable|in:low,medium,high',
            'investment_strategy' => 'nullable|in:conservative,balanced,aggressive',
            'status' => 'sometimes|in:active,inactive,pending,completed',
            'notes' => 'nullable|string|max:1000',
            'metadata' => 'nullable|array'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'quantity.numeric' => 'La cantidad debe ser un número.',
            'quantity.min' => 'La cantidad debe ser mayor a 0.',
            'total_investment.numeric' => 'La inversión total debe ser un número.',
            'total_investment.min' => 'La inversión total debe ser mayor a 0.01.',
            'current_value.numeric' => 'El valor actual debe ser un número.',
            'current_value.min' => 'El valor actual no puede ser negativo.',
            'daily_yield.numeric' => 'El rendimiento diario debe ser un número.',
            'daily_yield.min' => 'El rendimiento diario no puede ser negativo.',
            'auto_reinvest.boolean' => 'La auto-reinversión debe ser verdadero o falso.',
            'reinvest_threshold.numeric' => 'El umbral de reinversión debe ser un número.',
            'reinvest_threshold.min' => 'El umbral de reinversión no puede ser negativo.',
            'risk_tolerance.in' => 'La tolerancia al riesgo debe ser: baja, media o alta.',
            'investment_strategy.in' => 'La estrategia debe ser: conservadora, equilibrada o agresiva.',
            'status.in' => 'El estado debe ser: activo, inactivo, pendiente o completado.',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres.'
        ];
    }
}
