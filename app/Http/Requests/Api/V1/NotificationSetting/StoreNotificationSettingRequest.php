<?php

namespace App\Http\Requests\Api\V1\NotificationSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationSettingRequest extends FormRequest
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
            'channel' => [
                'required',
                'string',
                Rule::in(['email', 'push', 'sms', 'in_app'])
            ],
            'notification_type' => [
                'required',
                'string',
                Rule::in(['wallet', 'event', 'message', 'general'])
            ],
            'enabled' => [
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
            'user_id.required' => 'El ID del usuario es obligatorio.',
            'user_id.integer' => 'El ID del usuario debe ser un número entero.',
            'user_id.exists' => 'El usuario especificado no existe.',
            'channel.required' => 'El canal de notificación es obligatorio.',
            'channel.in' => 'El canal debe ser: email, push, sms o in_app.',
            'notification_type.required' => 'El tipo de notificación es obligatorio.',
            'notification_type.in' => 'El tipo debe ser: wallet, event, message o general.',
            'enabled.boolean' => 'El campo habilitado debe ser verdadero o falso.'
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
            'channel' => 'canal',
            'notification_type' => 'tipo de notificación',
            'enabled' => 'habilitado'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir a minúsculas si se proporcionan
        if ($this->has('channel')) {
            $this->merge([
                'channel' => strtolower($this->channel)
            ]);
        }

        if ($this->has('notification_type')) {
            $this->merge([
                'notification_type' => strtolower($this->notification_type)
            ]);
        }

        // Establecer enabled como true por defecto si no se proporciona
        if (!$this->has('enabled')) {
            $this->merge([
                'enabled' => true
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que no exista ya una configuración para la misma combinación de usuario, canal y tipo
            if ($this->filled('user_id') && $this->filled('channel') && $this->filled('notification_type')) {
                $exists = \App\Models\NotificationSetting::where([
                    'user_id' => $this->user_id,
                    'channel' => $this->channel,
                    'notification_type' => $this->notification_type
                ])->exists();

                if ($exists) {
                    $validator->errors()->add('channel', 'Ya existe una configuración para este usuario, canal y tipo de notificación.');
                }
            }

            // Validar que el usuario tenga permisos para recibir notificaciones
            if ($this->filled('user_id')) {
                $user = \App\Models\User::find($this->user_id);
                if ($user && !$user->email_verified_at && $this->channel === 'email') {
                    $validator->errors()->add('channel', 'El usuario no puede recibir notificaciones por email hasta verificar su cuenta.');
                }
            }

            // Validar restricciones específicas por canal
            if ($this->filled('channel')) {
                switch ($this->channel) {
                    case 'sms':
                        // Validar que solo se permitan tipos críticos por SMS
                        if (!in_array($this->notification_type, ['wallet', 'alert'])) {
                            $validator->errors()->add('channel', 'SMS solo está disponible para notificaciones de billetera y alertas críticas.');
                        }
                        break;
                    
                    case 'push':
                        // Validar que push no esté disponible para tipos generales
                        if ($this->notification_type === 'general') {
                            $validator->errors()->add('channel', 'Las notificaciones push no están disponibles para el tipo general.');
                        }
                        break;
                }
            }

            // Validar restricciones específicas por tipo
            if ($this->filled('notification_type')) {
                switch ($this->notification_type) {
                    case 'wallet':
                        // Las notificaciones de billetera deben estar habilitadas por defecto
                        if ($this->enabled === false) {
                            $validator->errors()->add('enabled', 'Las notificaciones de billetera no pueden estar deshabilitadas por seguridad.');
                        }
                        break;
                }
            }
        });
    }
}
