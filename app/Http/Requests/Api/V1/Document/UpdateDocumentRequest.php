<?php

namespace App\Http\Requests\Api\V1\Document;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'file_path' => 'nullable|string|max:500',
            'file_type' => 'nullable|string|max:50',
            'file_size' => 'nullable|integer|min:0',
            'mime_type' => 'nullable|string|max:100',
            'visible' => 'nullable|boolean',
            'category_id' => 'nullable|exists:categories,id',
            'version' => 'nullable|string|max:20',
            'expires_at' => 'nullable|date',
            'requires_auth' => 'nullable|boolean',
            'allowed_roles' => 'nullable|array',
            'allowed_roles.*' => 'string',
            'thumbnail_path' => 'nullable|string|max:500',
            'language' => 'nullable|string|size:2',
            'organization_id' => 'nullable|exists:organizations,id',
            'is_draft' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'search_keywords' => 'nullable|array',
            'search_keywords.*' => 'string|max:50',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
            'language.size' => 'El idioma debe tener exactamente 2 caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir valores booleanos
        if ($this->has('visible')) {
            $this->merge(['visible' => $this->boolean('visible')]);
        }

        if ($this->has('is_draft')) {
            $this->merge(['is_draft' => $this->boolean('is_draft')]);
        }

        if ($this->has('requires_auth')) {
            $this->merge(['requires_auth' => $this->boolean('requires_auth')]);
        }
    }
}
