<?php

namespace App\Http\Requests\Api\V1\FormSubmission;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormSubmissionRequest extends FormRequest
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
            'status' => 'sometimes|string|in:pending,processed,archived,spam',
            'processing_notes' => 'nullable|string|max:1000',
            'processed_at' => 'nullable|date',
            'processed_by_user_id' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.in' => 'El estado debe ser: pending, processed, archived o spam.',
            'processing_notes.max' => 'Las notas de procesamiento no pueden exceder 1000 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'status' => 'estado',
            'processing_notes' => 'notas de procesamiento',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean processing notes if provided
        if ($this->has('processing_notes') && $this->processing_notes) {
            $this->merge(['processing_notes' => trim($this->processing_notes)]);
        }

        // Auto-set processed metadata when status changes to processed
        if ($this->has('status') && $this->status === 'processed') {
            $this->merge([
                'processed_at' => now(),
                'processed_by_user_id' => auth()->id(),
            ]);
        }

        // Clear processed metadata when status changes back to pending
        if ($this->has('status') && $this->status === 'pending') {
            $this->merge([
                'processed_at' => null,
                'processed_by_user_id' => null,
            ]);
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
                $submission = $this->route('formSubmission');
                if ($submission && !$this->isValidStatusTransition($submission->status, $this->status)) {
                    $validator->errors()->add('status', 'La transición de estado no es válida.');
                }
            }

            // Validate processing notes when marking as processed
            if ($this->status === 'processed' && !$this->processing_notes) {
                $validator->errors()->add('processing_notes', 'Las notas de procesamiento son requeridas cuando se marca como procesado.');
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
            'pending' => ['processed', 'archived', 'spam'],
            'processed' => ['archived', 'pending'], // Can be reopened
            'archived' => ['pending'], // Can be reopened
            'spam' => [], // Once spam, cannot change (except by admin override)
        ];

        // Same status is always valid
        if ($currentStatus === $newStatus) {
            return true;
        }

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }
}
