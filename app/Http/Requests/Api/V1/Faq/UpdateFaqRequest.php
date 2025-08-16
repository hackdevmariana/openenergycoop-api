<?php

namespace App\Http\Requests\Api\V1\Faq;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
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
            'topic_id' => 'nullable|exists:faq_topics,id',
            'question' => 'sometimes|required|string|max:500',
            'answer' => 'sometimes|required|string|max:10000',
            'position' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'organization_id' => 'nullable|exists:organizations,id',
            'language' => 'nullable|string|in:es,en,ca,eu,gl',
            'is_draft' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'question.required' => 'La pregunta es obligatoria.',
            'answer.required' => 'La respuesta es obligatoria.',
            'topic_id.exists' => 'El tema seleccionado no existe.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
            'language.in' => 'El idioma debe ser: es, en, ca, eu o gl.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir valores booleanos
        if ($this->has('is_featured')) {
            $this->merge(['is_featured' => $this->boolean('is_featured')]);
        }

        if ($this->has('is_draft')) {
            $this->merge(['is_draft' => $this->boolean('is_draft')]);
        }
    }
}
