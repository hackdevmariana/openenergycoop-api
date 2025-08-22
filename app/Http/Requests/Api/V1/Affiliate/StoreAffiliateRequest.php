<?php

namespace App\Http\Requests\Api\V1\Affiliate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAffiliateRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:affiliates,email',
            'company_name' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:2000',
            'type' => [
                'required',
                'string',
                Rule::in(['partner', 'reseller', 'distributor', 'consultant', 'other'])
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['active', 'inactive', 'pending', 'suspended', 'terminated'])
            ],
            'commission_rate' => 'nullable|numeric|between:0,100',
            'payment_terms' => 'nullable|string|max:100',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'organization_id' => 'nullable|exists:organizations,id',
            'user_id' => 'nullable|exists:users,id',
            'is_verified' => 'boolean',
            'notes' => 'nullable|string|max:2000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'social_media' => 'nullable|array',
            'social_media.*' => 'string|max:255',
            'banking_info' => 'nullable|array',
            'banking_info.account_name' => 'nullable|string|max:255',
            'banking_info.account_number' => 'nullable|string|max:50',
            'banking_info.bank_name' => 'nullable|string|max:255',
            'banking_info.routing_number' => 'nullable|string|max:50',
            'banking_info.swift_code' => 'nullable|string|max:20',
            'banking_info.iban' => 'nullable|string|max:50',
            'tax_info' => 'nullable|array',
            'tax_info.tax_id' => 'nullable|string|max:100',
            'tax_info.tax_exempt' => 'boolean',
            'tax_info.tax_exemption_number' => 'nullable|string|max:100',
            'performance_rating' => 'nullable|integer|between:1,5',
            'rating_notes' => 'nullable|string|max:1000',
            'rate_change_reason' => 'nullable|string|max:1000',
            'verification_notes' => 'nullable|string|max:1000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'string|max:255',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del afiliado es obligatorio.',
            'name.max' => 'El nombre del afiliado no puede tener más de 255 caracteres.',
            'email.required' => 'El email del afiliado es obligatorio.',
            'email.email' => 'El email del afiliado debe tener un formato válido.',
            'email.unique' => 'El email del afiliado ya está registrado.',
            'email.max' => 'El email del afiliado no puede tener más de 255 caracteres.',
            'company_name.max' => 'El nombre de la empresa no puede tener más de 255 caracteres.',
            'website.url' => 'El sitio web debe tener un formato válido.',
            'website.max' => 'El sitio web no puede tener más de 255 caracteres.',
            'phone.max' => 'El teléfono no puede tener más de 20 caracteres.',
            'address.max' => 'La dirección no puede tener más de 500 caracteres.',
            'city.max' => 'La ciudad no puede tener más de 100 caracteres.',
            'state.max' => 'El estado no puede tener más de 100 caracteres.',
            'country.max' => 'El país no puede tener más de 100 caracteres.',
            'postal_code.max' => 'El código postal no puede tener más de 20 caracteres.',
            'description.max' => 'La descripción no puede tener más de 2000 caracteres.',
            'type.required' => 'El tipo de afiliado es obligatorio.',
            'type.in' => 'El tipo de afiliado debe ser uno de: partner, reseller, distributor, consultant, other.',
            'status.required' => 'El estado del afiliado es obligatorio.',
            'status.in' => 'El estado del afiliado debe ser uno de: active, inactive, pending, suspended, terminated.',
            'commission_rate.numeric' => 'La tasa de comisión debe ser un número.',
            'commission_rate.between' => 'La tasa de comisión debe estar entre 0 y 100.',
            'payment_terms.max' => 'Los términos de pago no pueden tener más de 100 caracteres.',
            'contract_start_date.date' => 'La fecha de inicio del contrato debe ser una fecha válida.',
            'contract_end_date.date' => 'La fecha de fin del contrato debe ser una fecha válida.',
            'contract_end_date.after' => 'La fecha de fin del contrato debe ser posterior a la fecha de inicio.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'is_verified.boolean' => 'El estado de verificación debe ser verdadero o falso.',
            'notes.max' => 'Las notas no pueden tener más de 2000 caracteres.',
            'tags.array' => 'Las etiquetas deben ser un array.',
            'tags.*.string' => 'Cada etiqueta debe ser una cadena de texto.',
            'tags.*.max' => 'Cada etiqueta no puede tener más de 50 caracteres.',
            'social_media.array' => 'Las redes sociales deben ser un array.',
            'social_media.*.string' => 'Cada red social debe ser una cadena de texto.',
            'social_media.*.max' => 'Cada red social no puede tener más de 255 caracteres.',
            'banking_info.array' => 'La información bancaria debe ser un array.',
            'banking_info.account_name.max' => 'El nombre de la cuenta no puede tener más de 255 caracteres.',
            'banking_info.account_number.max' => 'El número de cuenta no puede tener más de 50 caracteres.',
            'banking_info.bank_name.max' => 'El nombre del banco no puede tener más de 255 caracteres.',
            'banking_info.routing_number.max' => 'El número de ruta no puede tener más de 50 caracteres.',
            'banking_info.swift_code.max' => 'El código SWIFT no puede tener más de 20 caracteres.',
            'banking_info.iban.max' => 'El IBAN no puede tener más de 50 caracteres.',
            'tax_info.array' => 'La información fiscal debe ser un array.',
            'tax_info.tax_id.max' => 'El ID fiscal no puede tener más de 100 caracteres.',
            'tax_info.tax_exempt.boolean' => 'El estado de exención fiscal debe ser verdadero o falso.',
            'tax_info.tax_exemption_number.max' => 'El número de exención fiscal no puede tener más de 100 caracteres.',
            'performance_rating.integer' => 'El rating de rendimiento debe ser un número entero.',
            'performance_rating.between' => 'El rating de rendimiento debe estar entre 1 y 5.',
            'rating_notes.max' => 'Las notas del rating no pueden tener más de 1000 caracteres.',
            'rate_change_reason.max' => 'La razón del cambio de tasa no puede tener más de 1000 caracteres.',
            'verification_notes.max' => 'Las notas de verificación no pueden tener más de 1000 caracteres.',
            'attachments.array' => 'Los archivos adjuntos deben ser un array.',
            'attachments.*.string' => 'Cada archivo adjunto debe ser una cadena de texto.',
            'attachments.*.max' => 'Cada archivo adjunto no puede tener más de 255 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre del afiliado',
            'email' => 'email del afiliado',
            'company_name' => 'nombre de la empresa',
            'website' => 'sitio web',
            'phone' => 'teléfono',
            'address' => 'dirección',
            'city' => 'ciudad',
            'state' => 'estado',
            'country' => 'país',
            'postal_code' => 'código postal',
            'description' => 'descripción',
            'type' => 'tipo de afiliado',
            'status' => 'estado del afiliado',
            'commission_rate' => 'tasa de comisión',
            'payment_terms' => 'términos de pago',
            'contract_start_date' => 'fecha de inicio del contrato',
            'contract_end_date' => 'fecha de fin del contrato',
            'organization_id' => 'organización',
            'user_id' => 'usuario',
            'is_verified' => 'estado de verificación',
            'notes' => 'notas',
            'tags' => 'etiquetas',
            'social_media' => 'redes sociales',
            'banking_info' => 'información bancaria',
            'tax_info' => 'información fiscal',
            'performance_rating' => 'rating de rendimiento',
            'rating_notes' => 'notas del rating',
            'rate_change_reason' => 'razón del cambio de tasa',
            'verification_notes' => 'notas de verificación',
            'attachments' => 'archivos adjuntos',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir strings booleanos a booleanos reales
        if ($this->has('is_verified')) {
            $this->merge([
                'is_verified' => filter_var($this->is_verified, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        if ($this->has('tax_info.tax_exempt')) {
            $this->merge([
                'tax_info' => array_merge($this->tax_info ?? [], [
                    'tax_exempt' => filter_var($this->input('tax_info.tax_exempt'), FILTER_VALIDATE_BOOLEAN)
                ])
            ]);
        }

        // Limpiar arrays vacíos
        if ($this->has('tags') && empty($this->tags)) {
            $this->merge(['tags' => null]);
        }

        if ($this->has('social_media') && empty($this->social_media)) {
            $this->merge(['social_media' => null]);
        }

        if ($this->has('attachments') && empty($this->attachments)) {
            $this->merge(['attachments' => null]);
        }
    }
}
