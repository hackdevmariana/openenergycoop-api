<?php

namespace App\Http\Requests\Api\V1\Collaborator;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollaboratorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'logo' => 'required|string|max:255',
            'url' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
            'order' => 'nullable|integer|min:0',
            'collaborator_type' => 'nullable|string|in:partner,sponsor,member,supporter',
            'organization_id' => 'nullable|exists:organizations,id',
            'is_active' => 'nullable|boolean',
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
            'name.required' => 'El nombre es obligatorio.',
            'logo.required' => 'El logo es obligatorio.',
            'url.url' => 'El URL debe ser una dirección válida.',
            'collaborator_type.in' => 'El tipo de colaborador debe ser: partner, sponsor, member o supporter.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Valores por defecto
        $this->merge([
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
            'is_draft' => $this->has('is_draft') ? $this->boolean('is_draft') : true,
            'order' => $this->order ?? 0,
            'collaborator_type' => $this->collaborator_type ?? 'partner',
        ]);
    }
}
