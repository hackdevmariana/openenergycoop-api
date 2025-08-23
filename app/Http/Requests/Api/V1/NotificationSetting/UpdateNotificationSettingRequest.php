<?php

namespace App\Http\Requests\Api\V1\NotificationSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationSettingRequest extends FormRequest
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
            'channel' => [
                'sometimes',
                'string',
                Rule::in(['email', 'push', 'sms', 'in_app'])
            ],
            'notification_type' => [
                'sometimes',
                'string',
                Rule::in(['wallet', 'event', 'message', 'general'])
            ],
            'enabled' => [
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
            'channel.in' => 'El canal debe ser: email, push, sms o in_app.',
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
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $setting = $this->route('notificationSetting');

            // Validar que no exista ya una configuración para la misma combinación de usuario, canal y tipo
            if ($this->filled('user_id') || $this->filled('channel') || $this->filled('notification_type')) {
                $userId = $this->get('user_id', $setting->user_id);
                $channel = $this->get('channel', $setting->channel);
                $notificationType = $this->get('notification_type', $setting->notification_type);

                $exists = \App\Models\NotificationSetting::where([
                    'user_id' => $userId,
                    'channel' => $channel,
                    'notification_type' => $notificationType
                ])->where('id', '!=', $setting->id)->exists();

                if ($exists) {
                    $validator->errors()->add('channel', 'Ya existe una configuración para este usuario, canal y tipo de notificación.');
                }
            }

            // Validar que el usuario tenga permisos para recibir notificaciones
            if ($this->filled('user_id') || $this->filled('channel')) {
                $userId = $this->get('user_id', $setting->user_id);
                $channel = $this->get('channel', $setting->channel);

                $user = \App\Models\User::find($userId);
                if ($user && !$user->email_verified_at && $channel === 'email') {
                    $validator->errors()->add('channel', 'El usuario no puede recibir notificaciones por email hasta verificar su cuenta.');
                }
            }

            // Validar restricciones específicas por canal
            if ($this->filled('channel') || $this->filled('notification_type')) {
                $channel = $this->get('channel', $setting->channel);
                $notificationType = $this->get('notification_type', $setting->notification_type);

                switch ($channel) {
                    case 'sms':
                        // Validar que solo se permitan tipos críticos por SMS
                        if (!in_array($notificationType, ['wallet', 'alert'])) {
                            $validator->errors()->add('channel', 'SMS solo está disponible para notificaciones de billetera y alertas críticas.');
                        }
                        break;
                    
                    case 'push':
                        // Validar que push no esté disponible para tipos generales
                        if ($notificationType === 'general') {
                            $validator->errors()->add('channel', 'Las notificaciones push no están disponibles para el tipo general.');
                        }
                        break;
                }
            }

            // Validar restricciones específicas por tipo
            if ($this->filled('notification_type') || $this->filled('enabled')) {
                $notificationType = $this->get('notification_type', $setting->notification_type);
                $enabled = $this->get('enabled', $setting->enabled);

                switch ($notificationType) {
                    case 'wallet':
                        // Las notificaciones de billetera no pueden estar deshabilitadas
                        if ($enabled === false) {
                            $validator->errors()->add('enabled', 'Las notificaciones de billetera no pueden estar deshabilitadas por seguridad.');
                        }
                        break;
                }
            }

            // Validar que no se pueda cambiar el tipo de notificaciones de billetera a otros tipos
            if ($this->filled('notification_type') && $setting->notification_type === 'wallet' && $this->notification_type !== 'wallet') {
                $validator->errors()->add('notification_type', 'No se puede cambiar el tipo de notificaciones de billetera por seguridad.');
            }

            // Validar que no se pueda cambiar el canal de notificaciones de billetera a SMS si no está habilitado
            if ($this->filled('channel') && $setting->notification_type === 'wallet' && $this->channel === 'sms') {
                // Verificar si el usuario tiene configuraciones SMS habilitadas
                $smsSettings = \App\Models\NotificationSetting::where('user_id', $setting->user_id)
                    ->where('channel', 'sms')
                    ->where('enabled', true)
                    ->exists();

                if (!$smsSettings) {
                    $validator->errors()->add('channel', 'No se puede cambiar a SMS para notificaciones de billetera sin tener configuraciones SMS habilitadas.');
                }
            }

            // Validar que si se cambia el usuario, tenga configuraciones de notificación habilitadas
            if ($this->filled('user_id') && $this->user_id !== $setting->user_id) {
                $user = \App\Models\User::find($this->user_id);
                if ($user && !$user->email_verified_at && $setting->channel === 'email') {
                    $validator->errors()->add('user_id', 'El nuevo usuario no puede recibir notificaciones por email hasta verificar su cuenta.');
                }
            }
        });
    }
}
