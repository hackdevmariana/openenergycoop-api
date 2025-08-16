<?php

namespace App\Http\Requests\Api\V1\FaqTopic;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqTopicRequest extends FormRequest
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
        $topicId = $this->route('faqTopic')->id ?? null;
        
        return [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:faq_topics,slug,' . $topicId,
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'organization_id' => 'nullable|exists:organizations,id',
            'language' => 'nullable|string|in:es,en,ca,eu,gl',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'slug.unique' => 'El slug ya está en uso.',
            'color.regex' => 'El color debe ser un código hexadecimal válido (ej: #FF0000).',
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
        if ($this->has('is_active')) {
            $this->merge(['is_active' => $this->boolean('is_active')]);
        }

        // Generar slug si se cambió el name pero no el slug
        if ($this->has('name') && !$this->has('slug')) {
            $this->merge([
                'slug' => \Str::slug($this->name)
            ]);
        }
    }
}
