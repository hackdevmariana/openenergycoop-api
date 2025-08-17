<?php

namespace App\Http\Requests\Api\V1\Balance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBalanceRequest extends FormRequest
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
            'description' => 'sometimes|string|max:500',
            'status' => 'sometimes|in:pending,completed,failed,cancelled',
            'metadata' => 'nullable|array'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'description.max' => 'La descripción no puede exceder 500 caracteres.',
            'status.in' => 'El estado debe ser: pendiente, completado, fallido o cancelado.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'description' => 'descripción',
            'status' => 'estado'
        ];
    }
}
