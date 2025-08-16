<?php

namespace App\Http\Requests\Api\V1\Message;

use App\Models\Message;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint - anyone can submit contact messages
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
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'priority' => 'nullable|string|in:low,normal,high,urgent',
            'message_type' => 'nullable|string|in:contact,support,complaint,suggestion',
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
            'subject.required' => 'El asunto es obligatorio.',
            'subject.max' => 'El asunto no puede exceder 255 caracteres.',
            'message.required' => 'El mensaje es obligatorio.',
            'message.max' => 'El mensaje no puede exceder 5000 caracteres.',
            'priority.in' => 'La prioridad debe ser: low, normal, high o urgent.',
            'message_type.in' => 'El tipo de mensaje debe ser: contact, support, complaint o suggestion.',
            'phone.max' => 'El teléfono no puede exceder 20 caracteres.',
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
            'phone' => 'teléfono',
            'subject' => 'asunto',
            'message' => 'mensaje',
            'priority' => 'prioridad',
            'message_type' => 'tipo de mensaje',
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
            'priority' => $this->priority ?? 'normal',
            'message_type' => $this->message_type ?? 'contact',
        ]);

        // Clean and format phone number
        if ($this->phone) {
            $cleanPhone = preg_replace('/[^0-9+\-\s\(\)]/', '', $this->phone);
            $this->merge(['phone' => $cleanPhone]);
        }

        // Trim whitespace from text fields
        $this->merge([
            'name' => $this->name ? trim($this->name) : null,
            'subject' => trim($this->subject ?? ''),
            'message' => trim($this->message ?? ''),
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate phone format if provided
            if ($this->phone) {
                if (!$this->isValidPhoneFormat($this->phone)) {
                    $validator->errors()->add('phone', 'El formato del teléfono no es válido.');
                }
            }

            // Check for spam patterns
            if ($this->hasSpamPatterns()) {
                $validator->errors()->add('message', 'El mensaje contiene contenido no permitido.');
            }

            // Rate limiting by email (if needed)
            if ($this->isRateLimited()) {
                $validator->errors()->add('email', 'Has enviado demasiados mensajes. Intenta de nuevo más tarde.');
            }
        });
    }

    /**
     * Validate phone number format.
     */
    private function isValidPhoneFormat(string $phone): bool
    {
        // Basic phone validation - adjust pattern as needed
        $patterns = [
            '/^\+?[0-9\s\-\(\)]{7,20}$/', // International format
            '/^[0-9]{9}$/', // Spanish format (9 digits)
            '/^\+34[0-9]{9}$/', // Spanish international format
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $phone)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for common spam patterns.
     */
    private function hasSpamPatterns(): bool
    {
        $spamKeywords = [
            'viagra', 'casino', 'lottery', 'winner', 'congratulations',
            'click here', 'act now', 'limited time', 'bitcoin', 'crypto',
            'investment opportunity', 'make money fast', 'work from home'
        ];

        $content = strtolower($this->subject . ' ' . $this->message);

        foreach ($spamKeywords as $keyword) {
            if (strpos($content, $keyword) !== false) {
                return true;
            }
        }

        // Check for excessive links
        if (substr_count($content, 'http') > 2) {
            return true;
        }

        return false;
    }

    /**
     * Check if the email is rate limited.
     */
    private function isRateLimited(): bool
    {
        if (!$this->email) {
            return false;
        }

        // Check messages from this email in the last hour
        $recentMessages = Message::where('email', $this->email)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return $recentMessages >= 5; // Max 5 messages per hour per email
    }
}
