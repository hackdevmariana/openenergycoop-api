<?php

namespace App\Http\Requests\Api\V1\Contact;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware and policies
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address' => 'sometimes|nullable|string|max:500',
            'icon_address' => 'sometimes|nullable|string|max:100',
            'phone' => 'sometimes|nullable|string|max:20',
            'icon_phone' => 'sometimes|nullable|string|max:100',
            'email' => 'sometimes|nullable|email|max:255',
            'icon_email' => 'sometimes|nullable|string|max:100',
            'latitude' => 'sometimes|nullable|numeric|between:-90,90',
            'longitude' => 'sometimes|nullable|numeric|between:-180,180',
            'contact_type' => 'sometimes|string|in:main,support,sales,media,technical,billing,emergency',
            'business_hours' => 'sometimes|nullable|array',
            'additional_info' => 'sometimes|nullable|string|max:1000',
            'organization_id' => 'sometimes|nullable|exists:organizations,id',
            'is_draft' => 'sometimes|boolean',
            'is_primary' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
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
        // Convertir valores booleanos
        if ($this->has('is_draft')) {
            $this->merge(['is_draft' => $this->boolean('is_draft')]);
        }

        if ($this->has('is_primary')) {
            $this->merge(['is_primary' => $this->boolean('is_primary')]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // If location is being updated, both lat and lng should be present or both null
            if ($this->has('latitude') || $this->has('longitude')) {
                if (($this->latitude && !$this->longitude) || (!$this->latitude && $this->longitude)) {
                    $validator->errors()->add('location', 'Si proporciona ubicación, debe incluir tanto latitud como longitud.');
                }
            }
        });
    }
}
