<?php

namespace App\Http\Requests\Api\V1\UserDevice;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserDeviceRequest extends FormRequest
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
            'device_name' => 'sometimes|required|string|max:255',
            'push_token' => 'nullable|string|max:500',
            'is_current' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'device_name' => 'nombre del dispositivo',
            'push_token' => 'token de notificaciones push',
            'is_current' => 'dispositivo actual',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'device_name.required' => 'El nombre del dispositivo es obligatorio.',
            'device_name.max' => 'El nombre del dispositivo no puede tener más de 255 caracteres.',
            'push_token.max' => 'El token de notificaciones push no puede tener más de 500 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir valores booleanos
        if ($this->has('is_current')) {
            $this->merge(['is_current' => $this->boolean('is_current')]);
        }
    }
}