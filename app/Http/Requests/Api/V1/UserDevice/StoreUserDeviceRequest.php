<?php

namespace App\Http\Requests\Api\V1\UserDevice;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\AppEnums;

class StoreUserDeviceRequest extends FormRequest
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
            'device_name' => 'required|string|max:255',
            'device_type' => 'required|string|in:' . implode(',', array_keys(AppEnums::USER_DEVICE_TYPES)),
            'platform' => 'required|string|in:' . implode(',', array_keys(AppEnums::USER_DEVICE_PLATFORMS)),
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
            'device_type' => 'tipo de dispositivo',
            'platform' => 'plataforma',
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
            'device_type.required' => 'El tipo de dispositivo es obligatorio.',
            'device_type.in' => 'El tipo de dispositivo debe ser uno de: ' . implode(', ', array_keys(AppEnums::USER_DEVICE_TYPES)),
            'platform.required' => 'La plataforma es obligatoria.',
            'platform.in' => 'La plataforma debe ser una de: ' . implode(', ', array_keys(AppEnums::USER_DEVICE_PLATFORMS)),
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