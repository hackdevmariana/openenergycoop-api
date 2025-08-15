<?php

namespace App\Http\Requests\Api\V1\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StoreTeamRequest',
    title: 'Store Team Request',
    description: 'Datos para crear un nuevo equipo',
    type: 'object',
    required: ['name'],
    properties: [
        new OA\Property(
            property: 'name',
            type: 'string',
            maxLength: 255,
            example: 'Green Warriors',
            description: 'Nombre del equipo'
        ),
        new OA\Property(
            property: 'slug',
            type: 'string',
            maxLength: 255,
            example: 'green-warriors',
            description: 'Slug único del equipo (se genera automáticamente si no se proporciona)'
        ),
        new OA\Property(
            property: 'description',
            type: 'string',
            nullable: true,
            example: 'Un equipo comprometido con la energía renovable y la sostenibilidad',
            description: 'Descripción del equipo'
        ),
        new OA\Property(
            property: 'organization_id',
            type: 'integer',
            nullable: true,
            example: 1,
            description: 'ID de la organización (opcional para equipos globales)'
        ),
        new OA\Property(
            property: 'is_open',
            type: 'boolean',
            example: true,
            description: 'Si el equipo está abierto para que cualquiera se una'
        ),
        new OA\Property(
            property: 'max_members',
            type: 'integer',
            nullable: true,
            minimum: 1,
            maximum: 1000,
            example: 25,
            description: 'Número máximo de miembros (null = sin límite)'
        )
    ]
)]
class StoreTeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                'unique:teams,slug'
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'is_open' => ['boolean'],
            'max_members' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del equipo es obligatorio.',
            'name.max' => 'El nombre del equipo no puede exceder 255 caracteres.',
            'slug.unique' => 'Ya existe un equipo con este slug.',
            'slug.regex' => 'El slug solo puede contener letras minúsculas, números y guiones.',
            'description.max' => 'La descripción no puede exceder 1000 caracteres.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
            'max_members.min' => 'El número máximo de miembros debe ser al menos 1.',
            'max_members.max' => 'El número máximo de miembros no puede exceder 1000.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generar slug si no se proporciona
        if (!$this->has('slug') && $this->has('name')) {
            $this->merge([
                'slug' => Str::slug($this->name)
            ]);
        }

        // Establecer created_by_user_id automáticamente
        $this->merge([
            'created_by_user_id' => auth()->id()
        ]);

        // Establecer valores por defecto
        $this->merge([
            'is_open' => $this->boolean('is_open', false)
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que el slug sea único dentro de la organización
            if ($this->has('slug') && $this->has('organization_id')) {
                $exists = \App\Models\Team::where('slug', $this->slug)
                    ->where('organization_id', $this->organization_id)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('slug', 'Ya existe un equipo con este slug en la organización seleccionada.');
                }
            }

            // Validar permisos de organización
            if ($this->has('organization_id') && $this->organization_id) {
                $user = auth()->user();
                $organization = \App\Models\Organization::find($this->organization_id);
                
                if ($organization && !$user->can('createTeamIn', $organization)) {
                    $validator->errors()->add('organization_id', 'No tienes permisos para crear equipos en esta organización.');
                }
            }
        });
    }
}
