<?php

namespace App\Http\Requests\Api\V1\Article;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'text' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'slug' => 'nullable|string|max:255|unique:articles,slug',
            'featured_image' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'published_at' => 'nullable|date',
            'scheduled_at' => 'nullable|date|after:now',
            'status' => 'nullable|string|in:draft,review,published,archived',
            'is_draft' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
            'comment_enabled' => 'nullable|boolean',
            'language' => 'nullable|string|in:es,en,ca,eu,gl',
            'seo_focus_keyword' => 'nullable|string|max:100',
            'search_keywords' => 'nullable|array',
            'search_keywords.*' => 'string|max:50',
            'related_articles' => 'nullable|array',
            'related_articles.*' => 'integer|exists:articles,id',
            'internal_notes' => 'nullable|string|max:1000',
            'accessibility_notes' => 'nullable|string|max:500',
            'reading_level' => 'nullable|string|in:basic,intermediate,advanced',
            'organization_id' => 'nullable|exists:organizations,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede tener más de 255 caracteres.',
            'text.required' => 'El contenido del artículo es obligatorio.',
            'excerpt.max' => 'El resumen no puede tener más de 500 caracteres.',
            'slug.unique' => 'Ya existe un artículo con este slug.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'published_at.date' => 'La fecha de publicación debe ser una fecha válida.',
            'scheduled_at.after' => 'La fecha de programación debe ser posterior al momento actual.',
            'status.in' => 'El estado debe ser: borrador, en revisión, publicado o archivado.',
            'language.in' => 'El idioma debe ser uno de: es, en, ca, eu, gl.',
            'reading_level.in' => 'El nivel de lectura debe ser: básico, intermedio o avanzado.',
        ];
    }
}
