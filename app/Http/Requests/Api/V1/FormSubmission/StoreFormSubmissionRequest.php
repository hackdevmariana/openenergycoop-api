<?php

namespace App\Http\Requests\Api\V1\FormSubmission;

use App\Models\FormSubmission;
use Illuminate\Foundation\Http\FormRequest;

class StoreFormSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint - anyone can submit forms
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'form_name' => 'required|string|max:255',
            'fields' => 'required|array|min:1',
            'fields.*' => 'nullable', // Allow any field values
            'source_url' => 'nullable|string|url|max:500',
            'organization_id' => 'nullable|exists:organizations,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'form_name.required' => 'El nombre del formulario es obligatorio.',
            'form_name.max' => 'El nombre del formulario no puede exceder 255 caracteres.',
            'fields.required' => 'Los campos del formulario son obligatorios.',
            'fields.array' => 'Los campos deben ser un objeto válido.',
            'fields.min' => 'Debe proporcionar al menos un campo.',
            'source_url.url' => 'La URL de origen debe ser una URL válida.',
            'source_url.max' => 'La URL de origen no puede exceder 500 caracteres.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'form_name' => 'nombre del formulario',
            'fields' => 'campos',
            'source_url' => 'URL de origen',
            'organization_id' => 'organización',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and sanitize form name
        if ($this->form_name) {
            $this->merge(['form_name' => trim(strtolower($this->form_name))]);
        }

        // Set source URL from referer if not provided
        if (!$this->source_url && $this->header('referer')) {
            $this->merge(['source_url' => $this->header('referer')]);
        }

        // Clean fields data
        if ($this->fields && is_array($this->fields)) {
            $cleanFields = [];
            foreach ($this->fields as $key => $value) {
                // Remove empty fields
                if ($value !== null && $value !== '') {
                    // Sanitize field names
                    $cleanKey = trim(strtolower($key));
                    
                    // Sanitize string values
                    if (is_string($value)) {
                        $cleanFields[$cleanKey] = trim($value);
                    } else {
                        $cleanFields[$cleanKey] = $value;
                    }
                }
            }
            $this->merge(['fields' => $cleanFields]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check for spam patterns
            if ($this->isPotentialSpam()) {
                $validator->errors()->add('fields', 'El contenido del formulario contiene patrones sospechosos.');
            }

            // Validate required fields for specific form types
            if ($this->form_name === 'contact') {
                $this->validateContactForm($validator);
            } elseif ($this->form_name === 'newsletter') {
                $this->validateNewsletterForm($validator);
            }

            // Rate limiting by IP
            if ($this->isRateLimited()) {
                $validator->errors()->add('form_name', 'Has enviado demasiados formularios recientemente. Intenta de nuevo más tarde.');
            }

            // Validate form name is allowed
            if (!$this->isValidFormType()) {
                $validator->errors()->add('form_name', 'El tipo de formulario no es válido.');
            }
        });
    }

    /**
     * Validate contact form specific requirements.
     */
    private function validateContactForm($validator): void
    {
        $fields = $this->fields ?? [];

        $hasName = isset($fields['name']) || isset($fields['nombre']) || isset($fields['full_name']);
        $hasEmail = isset($fields['email']) || isset($fields['correo']) || isset($fields['email_address']);
        $hasMessage = isset($fields['message']) || isset($fields['mensaje']) || isset($fields['content']);

        if (!$hasName && !$hasEmail) {
            $validator->errors()->add('fields', 'El formulario de contacto debe incluir al menos un nombre o email.');
        }

        if (!$hasMessage) {
            $validator->errors()->add('fields', 'El formulario de contacto debe incluir un mensaje.');
        }
    }

    /**
     * Validate newsletter form specific requirements.
     */
    private function validateNewsletterForm($validator): void
    {
        $fields = $this->fields ?? [];

        $hasEmail = isset($fields['email']) || isset($fields['correo']) || isset($fields['email_address']);

        if (!$hasEmail) {
            $validator->errors()->add('fields', 'El formulario de newsletter debe incluir un email.');
        }

        // Validate email format if present
        foreach (['email', 'correo', 'email_address'] as $emailField) {
            if (isset($fields[$emailField]) && !filter_var($fields[$emailField], FILTER_VALIDATE_EMAIL)) {
                $validator->errors()->add('fields', 'El email proporcionado no tiene un formato válido.');
                break;
            }
        }
    }

    /**
     * Check if the form type is valid/allowed.
     */
    private function isValidFormType(): bool
    {
        $allowedTypes = array_keys(FormSubmission::FORM_TYPES);
        return in_array($this->form_name, $allowedTypes);
    }

    /**
     * Check for spam patterns in form content.
     */
    private function isPotentialSpam(): bool
    {
        $fields = $this->fields ?? [];
        $allText = implode(' ', array_filter($fields, 'is_string'));

        // Check for common spam patterns
        $spamPatterns = [
            '/\b(viagra|cialis|casino|poker|loan|mortgage)\b/i',
            '/\b(click here|visit now|act now|limited time)\b/i',
            '/https?:\/\/[^\s]+.*https?:\/\/[^\s]+/', // Multiple URLs
            '/<script|<iframe|javascript:/i', // Potential XSS
        ];

        foreach ($spamPatterns as $pattern) {
            if (preg_match($pattern, $allText)) {
                return true;
            }
        }

        // Check for excessive length
        if (strlen($allText) > 5000) {
            return true;
        }

        // Check for too many links
        if (substr_count($allText, 'http') > 3) {
            return true;
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
        
        // Check submissions from this IP in the last hour
        $recentSubmissions = FormSubmission::where('ip_address', $ip)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return $recentSubmissions >= 5; // Max 5 submissions per hour per IP
    }
}
