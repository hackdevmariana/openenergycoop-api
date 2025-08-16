<?php

namespace App\Http\Requests\Api\V1\NewsletterSubscription;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsletterSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware and policies
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|nullable|string|max:255',
            'language' => 'sometimes|string|in:es,en,ca,eu,gl',
            'preferences' => 'sometimes|nullable|array',
            'preferences.frequency' => 'sometimes|nullable|string|in:daily,weekly,monthly',
            'preferences.topics' => 'sometimes|nullable|array',
            'preferences.topics.*' => 'string|max:100',
            'preferences.format' => 'sometimes|nullable|string|in:html,text',
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'string|max:100',
            'status' => 'sometimes|string|in:pending,confirmed,unsubscribed,bounced,complained',
            'subscription_source' => 'sometimes|nullable|string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'language.in' => 'El idioma debe ser: es, en, ca, eu o gl.',
            'status.in' => 'El estado debe ser: pending, confirmed, unsubscribed, bounced o complained.',
            'preferences.frequency.in' => 'La frecuencia debe ser: daily, weekly o monthly.',
            'preferences.format.in' => 'El formato debe ser: html o text.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'language' => 'idioma',
            'preferences' => 'preferencias',
            'preferences.frequency' => 'frecuencia',
            'preferences.topics' => 'temas de interés',
            'preferences.format' => 'formato',
            'tags' => 'etiquetas',
            'status' => 'estado',
            'subscription_source' => 'fuente de suscripción',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean name if provided
        if ($this->has('name') && $this->name) {
            $this->merge(['name' => trim($this->name)]);
        }

        // Ensure preferences structure is maintained
        if ($this->has('preferences') && is_array($this->preferences)) {
            $preferences = $this->preferences;
            
            // Ensure required preference keys exist
            if (!isset($preferences['frequency'])) {
                $preferences['frequency'] = 'weekly';
            }
            if (!isset($preferences['format'])) {
                $preferences['format'] = 'html';
            }
            if (!isset($preferences['topics'])) {
                $preferences['topics'] = ['general'];
            }

            $this->merge(['preferences' => $preferences]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate status transitions
            if ($this->has('status')) {
                $subscription = $this->route('newsletterSubscription');
                if ($subscription && !$this->isValidStatusTransition($subscription->status, $this->status)) {
                    $validator->errors()->add('status', 'La transición de estado no es válida.');
                }
            }

            // Check for spam patterns in name if updated
            if ($this->has('name') && $this->name && $this->containsSpamPatterns($this->name)) {
                $validator->errors()->add('name', 'El nombre contiene contenido no permitido.');
            }

            // Validate tag format
            if ($this->has('tags') && is_array($this->tags)) {
                foreach ($this->tags as $tag) {
                    if (!$this->isValidTag($tag)) {
                        $validator->errors()->add('tags', 'Una o más etiquetas tienen formato inválido.');
                        break;
                    }
                }
            }
        });
    }

    /**
     * Check if status transition is valid.
     */
    private function isValidStatusTransition(string $currentStatus, string $newStatus): bool
    {
        // Define valid status transitions
        $validTransitions = [
            'pending' => ['confirmed', 'unsubscribed', 'bounced'],
            'confirmed' => ['unsubscribed', 'bounced', 'complained'],
            'unsubscribed' => ['confirmed'], // Can be resubscribed
            'bounced' => ['confirmed'], // Can be reactivated if email is fixed
            'complained' => [], // Once complained, cannot change (except by admin override)
        ];

        // Same status is always valid
        if ($currentStatus === $newStatus) {
            return true;
        }

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
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
     * Validate tag format.
     */
    private function isValidTag(string $tag): bool
    {
        // Tags should be alphanumeric with optional hyphens/underscores
        return preg_match('/^[a-zA-Z0-9_\-:]+$/', $tag) && strlen($tag) <= 100;
    }
}
