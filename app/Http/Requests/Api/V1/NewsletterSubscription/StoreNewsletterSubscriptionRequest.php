<?php

namespace App\Http\Requests\Api\V1\NewsletterSubscription;

use App\Models\NewsletterSubscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNewsletterSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint - anyone can subscribe
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('newsletter_subscriptions', 'email')
                    ->where('organization_id', $this->organization_id)
            ],
            'language' => 'nullable|string|in:es,en,ca,eu,gl',
            'preferences' => 'nullable|array',
            'preferences.frequency' => 'nullable|string|in:daily,weekly,monthly',
            'preferences.topics' => 'nullable|array',
            'preferences.topics.*' => 'string|max:100',
            'preferences.format' => 'nullable|string|in:html,text',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
            'subscription_source' => 'nullable|string|max:100',
            'organization_id' => 'nullable|exists:organizations,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Este email ya está suscrito al newsletter.',
            'language.in' => 'El idioma debe ser: es, en, ca, eu o gl.',
            'preferences.frequency.in' => 'La frecuencia debe ser: daily, weekly o monthly.',
            'preferences.format.in' => 'El formato debe ser: html o text.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'email',
            'language' => 'idioma',
            'preferences' => 'preferencias',
            'preferences.frequency' => 'frecuencia',
            'preferences.topics' => 'temas de interés',
            'preferences.format' => 'formato',
            'tags' => 'etiquetas',
            'subscription_source' => 'fuente de suscripción',
            'organization_id' => 'organización',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'language' => $this->language ?? 'es',
            'subscription_source' => $this->subscription_source ?? 'website',
        ]);

        // Clean and validate email
        if ($this->email) {
            $this->merge(['email' => strtolower(trim($this->email))]);
        }

        // Clean name
        if ($this->name) {
            $this->merge(['name' => trim($this->name)]);
        }

        // Set default preferences if not provided
        if (!$this->preferences) {
            $this->merge([
                'preferences' => [
                    'frequency' => 'weekly',
                    'format' => 'html',
                    'topics' => ['general'],
                ]
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check for disposable email domains
            if ($this->email && $this->isDisposableEmail($this->email)) {
                $validator->errors()->add('email', 'No se permiten emails desechables.');
            }

            // Validate email domain
            if ($this->email && !$this->isValidEmailDomain($this->email)) {
                $validator->errors()->add('email', 'El dominio del email no es válido.');
            }

            // Check for spam patterns in name
            if ($this->name && $this->containsSpamPatterns($this->name)) {
                $validator->errors()->add('name', 'El nombre contiene contenido no permitido.');
            }

            // Rate limiting by IP
            if ($this->isRateLimited()) {
                $validator->errors()->add('email', 'Has intentado suscribirte demasiadas veces. Intenta de nuevo más tarde.');
            }
        });
    }

    /**
     * Check if email is from a disposable email service.
     */
    private function isDisposableEmail(string $email): bool
    {
        $disposableDomains = [
            '10minutemail.com', 'guerrillamail.com', 'mailinator.com',
            'tempmail.org', 'throwaway.email', 'temp-mail.org',
            'yopmail.com', 'sharklasers.com', 'guerrillamailblock.com'
        ];

        $domain = substr(strrchr($email, '@'), 1);
        return in_array(strtolower($domain), $disposableDomains);
    }

    /**
     * Validate email domain format.
     */
    private function isValidEmailDomain(string $email): bool
    {
        $domain = substr(strrchr($email, '@'), 1);
        
        // Basic domain validation
        if (!$domain || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Check if domain has MX record (basic check)
        return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
    }

    /**
     * Check for spam patterns in name.
     */
    private function containsSpamPatterns(string $name): bool
    {
        $spamPatterns = [
            '/^[a-z]{1,3}[0-9]{3,}$/i', // Short letters + many numbers
            '/casino|poker|viagra|cialis/i',
            '/[^\w\s\-\.\'\`]/u', // Only allow word chars, spaces, hyphens, dots, apostrophes
        ];

        foreach ($spamPatterns as $pattern) {
            if (preg_match($pattern, $name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the IP is rate limited.
     */
    private function isRateLimited(): bool
    {
        // Skip rate limiting in testing environment
        if (app()->environment('testing')) {
            return false;
        }
        
        $ip = request()->ip();
        
        // Check subscriptions from this IP in the last hour
        $recentSubscriptions = NewsletterSubscription::where('ip_address', $ip)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return $recentSubscriptions >= 3; // Max 3 subscriptions per hour per IP
    }
}
