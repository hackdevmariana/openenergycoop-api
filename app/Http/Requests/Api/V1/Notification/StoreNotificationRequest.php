<?php

namespace App\Http\Requests\Api\V1\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el controlador
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id'
            ],
            'title' => [
                'required',
                'string',
                'max:255',
                'min:3'
            ],
            'message' => [
                'required',
                'string',
                'max:1000',
                'min:5'
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['info', 'alert', 'success', 'warning', 'error'])
            ],
            'read_at' => [
                'nullable',
                'date',
                'before_or_equal:now'
            ],
            'delivered_at' => [
                'nullable',
                'date',
                'before_or_equal:now'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'El ID del usuario es obligatorio.',
            'user_id.integer' => 'El ID del usuario debe ser un número entero.',
            'user_id.exists' => 'El usuario especificado no existe.',
            'title.required' => 'El título de la notificación es obligatorio.',
            'title.string' => 'El título debe ser una cadena de texto.',
            'title.max' => 'El título no puede tener más de 255 caracteres.',
            'title.min' => 'El título debe tener al menos 3 caracteres.',
            'message.required' => 'El mensaje de la notificación es obligatorio.',
            'message.string' => 'El mensaje debe ser una cadena de texto.',
            'message.max' => 'El mensaje no puede tener más de 1000 caracteres.',
            'message.min' => 'El mensaje debe tener al menos 5 caracteres.',
            'type.required' => 'El tipo de notificación es obligatorio.',
            'type.in' => 'El tipo de notificación debe ser: info, alert, success, warning o error.',
            'read_at.date' => 'La fecha de lectura debe ser una fecha válida.',
            'read_at.before_or_equal' => 'La fecha de lectura no puede ser futura.',
            'delivered_at.date' => 'La fecha de entrega debe ser una fecha válida.',
            'delivered_at.before_or_equal' => 'La fecha de entrega no puede ser futura.'
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
            'user_id' => 'usuario',
            'title' => 'título',
            'message' => 'mensaje',
            'type' => 'tipo',
            'read_at' => 'fecha de lectura',
            'delivered_at' => 'fecha de entrega'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir a minúsculas si se proporciona
        if ($this->has('type')) {
            $this->merge([
                'type' => strtolower($this->type)
            ]);
        }

        // Si no se proporciona delivered_at, establecerlo automáticamente
        if (!$this->has('delivered_at')) {
            $this->merge([
                'delivered_at' => now()
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que si se proporciona read_at, la notificación no sea del tipo 'error' o 'alert'
            if ($this->filled('read_at') && in_array($this->type, ['error', 'alert'])) {
                $validator->errors()->add('read_at', 'Las notificaciones de error y alerta no pueden marcarse como leídas al crearse.');
            }

            // Validar que si se proporciona delivered_at, sea posterior o igual a la fecha de creación
            if ($this->filled('delivered_at') && $this->filled('read_at')) {
                if ($this->delivered_at < $this->read_at) {
                    $validator->errors()->add('delivered_at', 'La fecha de entrega no puede ser anterior a la fecha de lectura.');
                }
            }

            // Validar que el usuario tenga configuraciones de notificación habilitadas
            if ($this->filled('user_id')) {
                $user = \App\Models\User::find($this->user_id);
                if ($user && !$user->notificationSettings()->enabled()->exists()) {
                    $validator->errors()->add('user_id', 'El usuario no tiene configuraciones de notificación habilitadas.');
                }
            }
        });
    }
}
