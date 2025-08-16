<?php

namespace App\Http\Requests\Api\V1\ConsentLog;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\AppEnums;

class StoreConsentLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'consent_type' => 'required|string|in:' . implode(',', array_keys(AppEnums::CONSENT_TYPES)),
            'consent_given' => 'required|boolean',
            'version' => 'nullable|string|max:50',
            'purpose' => 'nullable|string|max:500',
            'legal_basis' => 'nullable|string|max:200',
            'data_categories' => 'nullable|array',
            'data_categories.*' => 'string|max:100',
            'retention_period' => 'nullable|string|max:100',
            'third_parties' => 'nullable|array',
            'third_parties.*' => 'string|max:200',
            'withdrawal_method' => 'nullable|string|max:200',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'consent_type' => 'tipo de consentimiento',
            'consent_given' => 'consentimiento otorgado',
            'version' => 'versión',
            'purpose' => 'propósito',
            'legal_basis' => 'base legal',
            'data_categories' => 'categorías de datos',
            'retention_period' => 'período de retención',
            'third_parties' => 'terceros',
            'withdrawal_method' => 'método de retirada',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'consent_type.required' => 'El tipo de consentimiento es obligatorio.',
            'consent_type.in' => 'El tipo de consentimiento debe ser uno de: ' . implode(', ', array_keys(AppEnums::CONSENT_TYPES)),
            'consent_given.required' => 'Debe especificar si el consentimiento fue otorgado.',
            'consent_given.boolean' => 'El consentimiento debe ser verdadero o falso.',
            'version.max' => 'La versión no puede tener más de 50 caracteres.',
            'purpose.max' => 'El propósito no puede tener más de 500 caracteres.',
            'legal_basis.max' => 'La base legal no puede tener más de 200 caracteres.',
            'data_categories.array' => 'Las categorías de datos deben ser un array.',
            'data_categories.*.max' => 'Cada categoría de datos no puede tener más de 100 caracteres.',
            'retention_period.max' => 'El período de retención no puede tener más de 100 caracteres.',
            'third_parties.array' => 'Los terceros deben ser un array.',
            'third_parties.*.max' => 'Cada tercero no puede tener más de 200 caracteres.',
            'withdrawal_method.max' => 'El método de retirada no puede tener más de 200 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir valores booleanos
        if ($this->has('consent_given')) {
            $this->merge(['consent_given' => $this->boolean('consent_given')]);
        }
    }
}