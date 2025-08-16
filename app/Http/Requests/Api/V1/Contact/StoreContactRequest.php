<?php

namespace App\Http\Requests\Api\V1\Contact;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address' => 'nullable|string|max:500',
            'icon_address' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'icon_phone' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'icon_email' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'contact_type' => 'required|string|in:main,support,sales,media,technical,billing,emergency',
            'business_hours' => 'nullable|array',
            'additional_info' => 'nullable|string|max:1000',
            'organization_id' => 'nullable|exists:organizations,id',
            'is_draft' => 'nullable|boolean',
            'is_primary' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'contact_type.required' => 'El tipo de contacto es obligatorio.',
            'contact_type.in' => 'El tipo de contacto debe ser: main, support, sales, media, technical, billing o emergency.',
            'email.email' => 'El email debe tener un formato válido.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Valores por defecto
        $this->merge([
            'is_draft' => $this->has('is_draft') ? $this->boolean('is_draft') : true,
            'is_primary' => $this->has('is_primary') ? $this->boolean('is_primary') : false,
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // At least one contact method should be provided
            if (!$this->address && !$this->phone && !$this->email) {
                $validator->errors()->add('contact_info', 'Debe proporcionar al menos una forma de contacto (dirección, teléfono o email).');
            }

            // If location is provided, both lat and lng should be present
            if (($this->latitude && !$this->longitude) || (!$this->latitude && $this->longitude)) {
                $validator->errors()->add('location', 'Si proporciona ubicación, debe incluir tanto latitud como longitud.');
            }
        });
    }
}
