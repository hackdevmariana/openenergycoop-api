<?php

namespace App\Http\Requests\Api\V1\Balance;

use Illuminate\Foundation\Http\FormRequest;

class StoreBalanceRequest extends FormRequest
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
            'amount' => 'required|numeric|not_in:0',
            'transaction_type' => 'required|in:deposit,withdrawal,yield,investment,fee,refund',
            'description' => 'nullable|string|max:500',
            'reference_id' => 'nullable|string|max:100|unique:balances,reference_id',
            'status' => 'nullable|in:pending,completed,failed,cancelled',
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
            'amount.required' => 'El monto es obligatorio.',
            'amount.numeric' => 'El monto debe ser un número.',
            'amount.not_in' => 'El monto no puede ser cero.',
            'transaction_type.required' => 'El tipo de transacción es obligatorio.',
            'transaction_type.in' => 'El tipo debe ser: depósito, retiro, rendimiento, inversión, comisión o reembolso.',
            'description.max' => 'La descripción no puede exceder 500 caracteres.',
            'reference_id.unique' => 'Ya existe una transacción con esta referencia.',
            'status.in' => 'El estado debe ser: pendiente, completado, fallido o cancelado.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'usuario',
            'amount' => 'monto',
            'transaction_type' => 'tipo de transacción',
            'description' => 'descripción',
            'reference_id' => 'ID de referencia',
            'status' => 'estado'
        ];
    }
}
