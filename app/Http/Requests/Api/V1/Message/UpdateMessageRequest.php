<?php

namespace App\Http\Requests\Api\V1\Message;

use App\Models\Message;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMessageRequest extends FormRequest
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
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'subject' => 'sometimes|required|string|max:255',
            'message' => 'sometimes|required|string|max:5000',
            'status' => 'sometimes|string|in:pending,read,replied,archived,spam',
            'priority' => 'sometimes|string|in:low,normal,high,urgent',
            'message_type' => 'sometimes|string|in:contact,support,complaint,suggestion',
            'internal_notes' => 'sometimes|nullable|string|max:2000',
            'assigned_to_user_id' => 'sometimes|nullable|exists:users,id',
            'organization_id' => 'sometimes|nullable|exists:organizations,id',
            'replied_by_user_id' => 'sometimes|nullable|exists:users,id',
            'read_at' => 'sometimes|nullable|date',
            'replied_at' => 'sometimes|nullable|date',
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
            'status.in' => 'El estado debe ser: pending, read, replied, archived o spam.',
            'priority.in' => 'La prioridad debe ser: low, normal, high o urgent.',
            'message_type.in' => 'El tipo de mensaje debe ser: contact, support, complaint o suggestion.',
            'internal_notes.max' => 'Las notas internas no pueden exceder 2000 caracteres.',
            'phone.max' => 'El teléfono no puede exceder 20 caracteres.',
            'assigned_to_user_id.exists' => 'El usuario asignado no existe.',
            'replied_by_user_id.exists' => 'El usuario que respondió no existe.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
            'read_at.date' => 'La fecha de lectura debe ser una fecha válida.',
            'replied_at.date' => 'La fecha de respuesta debe ser una fecha válida.',
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
            'status' => 'estado',
            'priority' => 'prioridad',
            'message_type' => 'tipo de mensaje',
            'internal_notes' => 'notas internas',
            'assigned_to_user_id' => 'usuario asignado',
            'replied_by_user_id' => 'usuario que respondió',
            'organization_id' => 'organización',
            'read_at' => 'fecha de lectura',
            'replied_at' => 'fecha de respuesta',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format phone number if provided
        if ($this->has('phone') && $this->phone) {
            $cleanPhone = preg_replace('/[^0-9+\-\s\(\)]/', '', $this->phone);
            $this->merge(['phone' => $cleanPhone]);
        }

        // Trim whitespace from text fields
        if ($this->has('name') && $this->name) {
            $this->merge(['name' => trim($this->name)]);
        }

        if ($this->has('subject')) {
            $this->merge(['subject' => trim($this->subject)]);
        }

        if ($this->has('message')) {
            $this->merge(['message' => trim($this->message)]);
        }

        if ($this->has('internal_notes') && $this->internal_notes) {
            $this->merge(['internal_notes' => trim($this->internal_notes)]);
        }

        // Auto-set timestamps when status changes
        if ($this->has('status')) {
            if ($this->status === 'replied') {
                if (!$this->has('replied_by_user_id') || !$this->replied_by_user_id) {
                    $this->merge(['replied_by_user_id' => auth()->id()]);
                }
                
                if (!$this->has('replied_at') || !$this->replied_at) {
                    $this->merge(['replied_at' => now()->toDateTimeString()]);
                }
            }

            if (in_array($this->status, ['read', 'replied', 'archived'])) {
                if (!$this->has('read_at') || !$this->read_at) {
                    $this->merge(['read_at' => now()->toDateTimeString()]);
                }
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate phone format if provided
            if ($this->has('phone') && $this->phone) {
                if (!$this->isValidPhoneFormat($this->phone)) {
                    $validator->errors()->add('phone', 'El formato del teléfono no es válido.');
                }
            }

            // Validate status transitions
            if ($this->has('status')) {
                $message = $this->route('message');
                if ($message && !$this->isValidStatusTransition($message->status, $this->status)) {
                    $validator->errors()->add('status', 'La transición de estado no es válida.');
                }
            }

            // Timestamps are auto-set in prepareForValidation
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
     * Check if status transition is valid.
     */
    private function isValidStatusTransition(string $currentStatus, string $newStatus): bool
    {
        // Define valid status transitions
        $validTransitions = [
            'pending' => ['read', 'replied', 'archived', 'spam'],
            'read' => ['replied', 'archived', 'spam'],
            'replied' => ['archived'],
            'archived' => ['pending', 'read'], // Can be unarchived
            'spam' => ['pending'], // Can be unmarked as spam
        ];

        // Same status is always valid
        if ($currentStatus === $newStatus) {
            return true;
        }

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }
}
