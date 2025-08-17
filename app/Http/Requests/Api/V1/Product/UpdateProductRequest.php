<?php

namespace App\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'provider_id' => 'sometimes|exists:providers,id',
            'type' => 'sometimes|in:solar,wind,hydro,biomass,geothermal,battery,efficiency',
            'unit_price' => 'sometimes|numeric|min:0',
            'currency' => 'nullable|string|size:3|in:EUR,USD,GBP',
            'unit' => 'sometimes|string|max:50',
            'minimum_investment' => 'nullable|numeric|min:0',
            'maximum_investment' => 'nullable|numeric|min:0|gte:minimum_investment',
            'expected_yield_percentage' => 'nullable|numeric|min:0|max:100',
            'risk_level' => 'nullable|in:low,medium,high',
            'renewable_percentage' => 'nullable|integer|min:0|max:100',
            'co2_reduction' => 'nullable|numeric|min:0',
            'energy_efficiency' => 'nullable|in:A++,A+,A,B,C,D,E',
            'carbon_footprint' => 'nullable|numeric|min:0',
            'water_saving' => 'nullable|numeric|min:0',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:255',
            'availability' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'metadata' => 'nullable|array',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'provider_id.exists' => 'El proveedor seleccionado no existe.',
            'type.in' => 'El tipo debe ser: solar, eólico, hidráulico, biomasa, geotérmico, batería o eficiencia.',
            'unit_price.numeric' => 'El precio debe ser un número.',
            'unit_price.min' => 'El precio no puede ser negativo.',
            'currency.size' => 'La moneda debe tener 3 caracteres (ej: EUR).',
            'currency.in' => 'La moneda debe ser EUR, USD o GBP.',
            'maximum_investment.gte' => 'La inversión máxima debe ser mayor o igual a la mínima.',
            'expected_yield_percentage.max' => 'El rendimiento esperado no puede ser mayor al 100%.',
            'risk_level.in' => 'El nivel de riesgo debe ser: bajo, medio o alto.',
            'renewable_percentage.max' => 'El porcentaje renovable no puede ser mayor al 100%.',
            'energy_efficiency.in' => 'La eficiencia energética debe ser entre A++ y E.',
            'tag_ids.*.exists' => 'Una o más etiquetas seleccionadas no existen.'
        ];
    }
}
