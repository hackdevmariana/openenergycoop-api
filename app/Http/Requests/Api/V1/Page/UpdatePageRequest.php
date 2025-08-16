<?php

namespace App\Http\Requests\Api\V1\Page;

use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
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
        $page = $this->route('page');
        
        return [
            'title' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-]+$/',
                Rule::unique('pages')->ignore($page->id)->where(function ($query) use ($page) {
                    return $query->where('organization_id', $this->organization_id ?? $page->organization_id)
                                 ->where('language', $this->language ?? $page->language);
                }),
            ],
            'route' => 'nullable|string|max:255|regex:/^\/[a-zA-Z0-9\-\/]*$/',
            'language' => 'sometimes|string|in:es,en,ca,eu,gl',
            'organization_id' => 'sometimes|nullable|exists:organizations,id',
            'is_draft' => 'sometimes|boolean',
            'template' => [
                'sometimes',
                'string',
                Rule::in(array_keys(Page::TEMPLATES)),
            ],
            'meta_data' => 'sometimes|nullable|array',
            'meta_data.title' => 'nullable|string|max:255',
            'meta_data.description' => 'nullable|string|max:500',
            'meta_data.keywords' => 'nullable|string|max:255',
            'cache_duration' => 'sometimes|nullable|integer|min:0|max:1440',
            'requires_auth' => 'sometimes|boolean',
            'allowed_roles' => 'sometimes|nullable|array',
            'allowed_roles.*' => 'string|max:50',
            'parent_id' => 'sometimes|nullable|exists:pages,id',
            'sort_order' => 'sometimes|nullable|integer|min:0',
            'published_at' => 'sometimes|nullable|date',
            'search_keywords' => 'sometimes|nullable|array',
            'search_keywords.*' => 'string|max:100',
            'internal_notes' => 'sometimes|nullable|string|max:2000',
            'accessibility_notes' => 'sometimes|nullable|string|max:1000',
            'reading_level' => 'sometimes|nullable|string|in:beginner,intermediate,advanced',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.max' => 'El título no puede exceder 255 caracteres.',
            'slug.unique' => 'Ya existe una página con este slug en la organización e idioma especificados.',
            'slug.regex' => 'El slug solo puede contener letras minúsculas, números y guiones.',
            'route.regex' => 'La ruta debe comenzar con / y contener solo caracteres válidos.',
            'language.in' => 'El idioma debe ser uno de: es, en, ca, eu, gl.',
            'template.in' => 'La plantilla seleccionada no es válida.',
            'meta_data.title.max' => 'El título de metadatos no puede exceder 255 caracteres.',
            'meta_data.description.max' => 'La descripción de metadatos no puede exceder 500 caracteres.',
            'cache_duration.min' => 'La duración del caché debe ser mínimo 0 minutos.',
            'cache_duration.max' => 'La duración del caché no puede exceder 1440 minutos (24 horas).',
            'parent_id.exists' => 'La página padre seleccionada no existe.',
            'sort_order.min' => 'El orden de clasificación debe ser mínimo 0.',
            'reading_level.in' => 'El nivel de lectura debe ser: beginner, intermediate o advanced.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'título',
            'slug' => 'slug',
            'route' => 'ruta',
            'language' => 'idioma',
            'organization_id' => 'organización',
            'template' => 'plantilla',
            'meta_data' => 'metadatos',
            'cache_duration' => 'duración del caché',
            'requires_auth' => 'requiere autenticación',
            'allowed_roles' => 'roles permitidos',
            'parent_id' => 'página padre',
            'sort_order' => 'orden de clasificación',
            'published_at' => 'fecha de publicación',
            'search_keywords' => 'palabras clave de búsqueda',
            'internal_notes' => 'notas internas',
            'accessibility_notes' => 'notas de accesibilidad',
            'reading_level' => 'nivel de lectura',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug from title if title is being updated but slug is not provided
        if ($this->has('title') && !$this->has('slug')) {
            $this->merge(['slug' => \Str::slug($this->title)]);
        }

        // Clean and format route
        if ($this->has('route') && $this->route) {
            $route = '/' . ltrim($this->route, '/');
            $this->merge(['route' => $route]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $page = $this->route('page');

            // Validate parent-child relationship
            if ($this->has('parent_id') && $this->parent_id) {
                $this->validateParentRelationship($validator, $page);
            }

            // Validate route uniqueness
            if ($this->has('route') && $this->route) {
                $this->validateRouteUniqueness($validator, $page);
            }

            // Validate template-specific requirements
            if ($this->has('template')) {
                $this->validateTemplateRequirements($validator);
            }

            // Validate meta data structure
            if ($this->has('meta_data') && $this->meta_data) {
                $this->validateMetaData($validator);
            }

            // Validate publishing constraints
            if ($this->has('is_draft') && $this->is_draft === false) {
                $this->validatePublishingRequirements($validator, $page);
            }
        });
    }

    /**
     * Validate parent-child relationship.
     */
    private function validateParentRelationship($validator, $page): void
    {
        $parent = Page::find($this->parent_id);
        
        if ($parent) {
            // Prevent setting self as parent
            if ($parent->id === $page->id) {
                $validator->errors()->add('parent_id', 'Una página no puede ser su propia página padre.');
            }

            // Check if parent is in the same organization and language
            $organizationId = $this->organization_id ?? $page->organization_id;
            $language = $this->language ?? $page->language;

            if ($parent->organization_id !== $organizationId) {
                $validator->errors()->add('parent_id', 'La página padre debe pertenecer a la misma organización.');
            }

            if ($parent->language !== $language) {
                $validator->errors()->add('parent_id', 'La página padre debe estar en el mismo idioma.');
            }

            // Check for circular reference
            $this->checkCircularReference($validator, $parent, $page);
        }
    }

    /**
     * Check for circular references in parent-child relationship.
     */
    private function checkCircularReference($validator, $parent, $currentPage): void
    {
        $visited = [$currentPage->id];
        $current = $parent;

        while ($current && $current->parent_id) {
            if (in_array($current->parent_id, $visited)) {
                $validator->errors()->add('parent_id', 'Esta relación crearía una referencia circular.');
                break;
            }
            
            $visited[] = $current->id;
            $current = $current->parent;
        }
    }

    /**
     * Validate route uniqueness.
     */
    private function validateRouteUniqueness($validator, $page): void
    {
        $organizationId = $this->organization_id ?? $page->organization_id;
        
        $exists = Page::where('route', $this->route)
            ->where('organization_id', $organizationId)
            ->where('id', '!=', $page->id)
            ->exists();

        if ($exists) {
            $validator->errors()->add('route', 'Ya existe una página con esta ruta en la organización.');
        }
    }

    /**
     * Validate template-specific requirements.
     */
    private function validateTemplateRequirements($validator): void
    {
        // Landing pages should not have parent
        if ($this->template === 'landing' && $this->parent_id) {
            $validator->errors()->add('template', 'Las páginas de aterrizaje no pueden tener página padre.');
        }

        // Contact pages should have specific meta data
        if ($this->template === 'contact') {
            if (!$this->meta_data || !isset($this->meta_data['contact_info'])) {
                $validator->errors()->add('meta_data', 'Las páginas de contacto deben incluir información de contacto en los metadatos.');
            }
        }
    }

    /**
     * Validate meta data structure.
     */
    private function validateMetaData($validator): void
    {
        $metaData = $this->meta_data;
        $template = $this->template ?? $this->route('page')->template;

        // Check for required fields based on template
        if ($template === 'article_list') {
            if (!isset($metaData['articles_per_page']) || !is_numeric($metaData['articles_per_page'])) {
                $validator->errors()->add('meta_data.articles_per_page', 'Las páginas de lista de artículos deben especificar el número de artículos por página.');
            }
        }

        // Validate common meta data fields
        if (isset($metaData['canonical_url']) && !filter_var($metaData['canonical_url'], FILTER_VALIDATE_URL)) {
            $validator->errors()->add('meta_data.canonical_url', 'La URL canónica debe ser una URL válida.');
        }
    }

    /**
     * Validate publishing requirements.
     */
    private function validatePublishingRequirements($validator, $page): void
    {
        // Merge current data with updates to check complete page state
        $updatedData = array_merge($page->toArray(), $this->validated());

        // Check if title exists
        if (empty($updatedData['title'])) {
            $validator->errors()->add('title', 'El título es requerido para publicar la página.');
        }

        // Check if slug exists
        if (empty($updatedData['slug'])) {
            $validator->errors()->add('slug', 'El slug es requerido para publicar la página.');
        }

        // Check if page has components (only if it's not a redirect page)
        if ($updatedData['template'] !== 'redirect' && $page->components()->count() === 0) {
            $validator->errors()->add('is_draft', 'La página debe tener al menos un componente para ser publicada.');
        }
    }
}
