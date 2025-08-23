<?php

namespace App\Http\Requests\Api\V1\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationRequest extends FormRequest
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
                'sometimes',
                'integer',
                'exists:users,id'
            ],
            'title' => [
                'sometimes',
                'string',
                'max:255',
                'min:3'
            ],
            'message' => [
                'sometimes',
                'string',
                'max:1000',
                'min:5'
            ],
            'type' => [
                'sometimes',
                'string',
                Rule::in(['info', 'alert', 'success', 'warning', 'error'])
            ],
            'read_at' => [
                'sometimes',
                'nullable',
                'date',
                'before_or_equal:now'
            ],
            'delivered_at' => [
                'sometimes',
                'nullable',
                'date',
                'before_or_equal:now'
            ],
            'is_read' => [
                'sometimes',
                'boolean'
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
            'user_id.integer' => 'El ID del usuario debe ser un número entero.',
            'user_id.exists' => 'El usuario especificado no existe.',
            'title.string' => 'El título debe ser una cadena de texto.',
            'title.max' => 'El título no puede tener más de 255 caracteres.',
            'title.min' => 'El título debe tener al menos 3 caracteres.',
            'message.string' => 'El mensaje debe ser una cadena de texto.',
            'message.max' => 'El mensaje no puede tener más de 1000 caracteres.',
            'message.min' => 'El mensaje debe tener al menos 5 caracteres.',
            'type.in' => 'El tipo de notificación debe ser: info, alert, success, warning o error.',
            'read_at.date' => 'La fecha de lectura debe ser una fecha válida.',
            'read_at.before_or_equal' => 'La fecha de lectura no puede ser futura.',
            'delivered_at.date' => 'La fecha de entrega debe ser una fecha válida.',
            'delivered_at.before_or_equal' => 'La fecha de entrega no puede ser futura.',
            'is_read.boolean' => 'El campo de lectura debe ser verdadero o falso.'
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
            'delivered_at' => 'fecha de entrega',
            'is_read' => 'estado de lectura'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir a minúsculas si se proporciona type
        if ($this->has('type')) {
            $this->merge([
                'type' => strtolower($this->type)
            ]);
        }

        // Manejar el campo is_read para establecer read_at automáticamente
        if ($this->has('is_read')) {
            if ($this->boolean('is_read')) {
                // Si se marca como leída y no tiene read_at, establecerlo
                if (!$this->filled('read_at')) {
                    $this->merge([
                        'read_at' => now()
                    ]);
                }
            } else {
                // Si se desmarca como leída, limpiar read_at
                $this->merge([
                    'read_at' => null
                ]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $notification = $this->route('notification');

            // Validar que si se proporciona read_at, la notificación no sea del tipo 'error' o 'alert'
            if ($this->filled('read_at') && $this->filled('type') && in_array($this->type, ['error', 'alert'])) {
                $validator->errors()->add('read_at', 'Las notificaciones de error y alerta no pueden marcarse como leídas.');
            }

            // Validar que si se proporciona delivered_at, sea posterior o igual a la fecha de lectura
            if ($this->filled('delivered_at') && $this->filled('read_at')) {
                if ($this->delivered_at < $this->read_at) {
                    $validator->errors()->add('delivered_at', 'La fecha de entrega no puede ser anterior a la fecha de lectura.');
                }
            }

            // Validar que si se cambia el usuario, tenga configuraciones de notificación habilitadas
            if ($this->filled('user_id') && $this->user_id !== $notification->user_id) {
                $user = \App\Models\User::find($this->user_id);
                if ($user && !$user->notificationSettings()->enabled()->exists()) {
                    $validator->errors()->add('user_id', 'El nuevo usuario no tiene configuraciones de notificación habilitadas.');
                }
            }

            // Validar que no se pueda cambiar el tipo de notificaciones ya leídas a 'error' o 'alert'
            if ($this->filled('type') && $notification->read_at && in_array($this->type, ['error', 'alert'])) {
                $validator->errors()->add('type', 'No se puede cambiar el tipo a error o alerta en notificaciones ya leídas.');
            }

            // Validar que si se cambia el tipo a 'error' o 'alert', se limpie read_at
            if ($this->filled('type') && in_array($this->type, ['error', 'alert']) && $notification->read_at) {
                $this->merge([
                    'read_at' => null
                ]);
            }
        });
    }
}
