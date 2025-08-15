<?php

namespace App\Http\Requests\Api\V1\UserSettings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserSettingsRequest extends FormRequest
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
            // Configuraciones generales
            'language' => 'sometimes|string|in:es,en,ca,eu,gl',
            'timezone' => 'sometimes|string|timezone',
            'theme' => 'sometimes|string|in:light,dark,auto',
            
            // Notificaciones
            'notifications_enabled' => 'sometimes|boolean',
            'email_notifications' => 'sometimes|boolean',
            'push_notifications' => 'sometimes|boolean',
            'sms_notifications' => 'sometimes|boolean',
            'marketing_emails' => 'sometimes|boolean',
            'newsletter_subscription' => 'sometimes|boolean',
            
            // Privacidad
            'privacy_level' => 'sometimes|string|in:public,friends,private',
            'profile_visibility' => 'sometimes|string|in:public,registered,private',
            'show_achievements' => 'sometimes|boolean',
            'show_statistics' => 'sometimes|boolean',
            'show_activity' => 'sometimes|boolean',
            
            // Formato y visualización
            'date_format' => 'sometimes|string|max:20',
            'time_format' => 'sometimes|string|in:12,24',
            'currency' => 'sometimes|string|size:3',
            'measurement_unit' => 'sometimes|string|in:metric,imperial',
            'energy_unit' => 'sometimes|string|in:kWh,MWh,GWh',
            
            // Configuraciones personalizadas
            'custom_settings' => 'sometimes|array',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'language' => 'idioma',
            'timezone' => 'zona horaria',
            'theme' => 'tema',
            'notifications_enabled' => 'notificaciones habilitadas',
            'email_notifications' => 'notificaciones por email',
            'push_notifications' => 'notificaciones push',
            'sms_notifications' => 'notificaciones SMS',
            'marketing_emails' => 'emails de marketing',
            'newsletter_subscription' => 'suscripción al boletín',
            'privacy_level' => 'nivel de privacidad',
            'profile_visibility' => 'visibilidad del perfil',
            'show_achievements' => 'mostrar logros',
            'show_statistics' => 'mostrar estadísticas',
            'show_activity' => 'mostrar actividad',
            'date_format' => 'formato de fecha',
            'time_format' => 'formato de hora',
            'currency' => 'moneda',
            'measurement_unit' => 'unidad de medida',
            'energy_unit' => 'unidad de energía',
            'custom_settings' => 'configuraciones personalizadas',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'language.in' => 'El idioma debe ser uno de: es, en, ca, eu, gl.',
            'timezone.timezone' => 'La zona horaria debe ser válida.',
            'theme.in' => 'El tema debe ser: light, dark o auto.',
            'privacy_level.in' => 'El nivel de privacidad debe ser: public, friends o private.',
            'profile_visibility.in' => 'La visibilidad del perfil debe ser: public, registered o private.',
            'time_format.in' => 'El formato de hora debe ser: 12 o 24.',
            'currency.size' => 'La moneda debe tener exactamente 3 caracteres (código ISO).',
            'measurement_unit.in' => 'La unidad de medida debe ser: metric o imperial.',
            'energy_unit.in' => 'La unidad de energía debe ser: kWh, MWh o GWh.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir valores booleanos
        $booleanFields = [
            'notifications_enabled',
            'email_notifications',
            'push_notifications',
            'sms_notifications',
            'marketing_emails',
            'newsletter_subscription',
            'show_achievements',
            'show_statistics',
            'show_activity',
        ];

        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $this->merge([$field => $this->boolean($field)]);
            }
        }
    }
}