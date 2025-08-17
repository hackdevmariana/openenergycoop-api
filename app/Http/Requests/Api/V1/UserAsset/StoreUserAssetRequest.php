<?php

namespace App\Http\Requests\Api\V1\UserAsset;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserAssetRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.0001',
            'total_investment' => 'required|numeric|min:0.01',
            'daily_yield' => 'nullable|numeric|min:0',
            'auto_reinvest' => 'nullable|boolean',
            'reinvest_threshold' => 'nullable|numeric|min:0',
            'risk_tolerance' => 'nullable|in:low,medium,high',
            'investment_strategy' => 'nullable|in:conservative,balanced,aggressive',
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
            'user_id.required' => 'El usuario es obligatorio.',
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'product_id.required' => 'El producto es obligatorio.',
            'product_id.exists' => 'El producto seleccionado no existe.',
            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.numeric' => 'La cantidad debe ser un número.',
            'quantity.min' => 'La cantidad debe ser mayor a 0.',
            'total_investment.required' => 'La inversión total es obligatoria.',
            'total_investment.numeric' => 'La inversión total debe ser un número.',
            'total_investment.min' => 'La inversión total debe ser mayor a 0.01.',
            'daily_yield.numeric' => 'El rendimiento diario debe ser un número.',
            'daily_yield.min' => 'El rendimiento diario no puede ser negativo.',
            'auto_reinvest.boolean' => 'La auto-reinversión debe ser verdadero o falso.',
            'reinvest_threshold.numeric' => 'El umbral de reinversión debe ser un número.',
            'reinvest_threshold.min' => 'El umbral de reinversión no puede ser negativo.',
            'risk_tolerance.in' => 'La tolerancia al riesgo debe ser: baja, media o alta.',
            'investment_strategy.in' => 'La estrategia debe ser: conservadora, equilibrada o agresiva.',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'usuario',
            'product_id' => 'producto',
            'quantity' => 'cantidad',
            'total_investment' => 'inversión total',
            'daily_yield' => 'rendimiento diario',
            'auto_reinvest' => 'auto-reinversión',
            'reinvest_threshold' => 'umbral de reinversión',
            'risk_tolerance' => 'tolerancia al riesgo',
            'investment_strategy' => 'estrategia de inversión',
            'notes' => 'notas'
        ];
    }
}
