<?php

namespace App\Http\Requests\Api\V1\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateTeamRequest',
    title: 'Update Team Request',
    description: 'Datos para actualizar un equipo existente',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'name',
            type: 'string',
            maxLength: 255,
            example: 'Green Warriors Updated',
            description: 'Nombre del equipo'
        ),
        new OA\Property(
            property: 'slug',
            type: 'string',
            maxLength: 255,
            example: 'green-warriors-updated',
            description: 'Slug único del equipo'
        ),
        new OA\Property(
            property: 'description',
            type: 'string',
            nullable: true,
            example: 'Un equipo actualizado comprometido con la energía renovable',
            description: 'Descripción del equipo'
        ),
        new OA\Property(
            property: 'is_open',
            type: 'boolean',
            example: false,
            description: 'Si el equipo está abierto para que cualquiera se una'
        ),
        new OA\Property(
            property: 'max_members',
            type: 'integer',
            nullable: true,
            minimum: 1,
            maximum: 1000,
            example: 30,
            description: 'Número máximo de miembros (null = sin límite)'
        )
    ]
)]
class UpdateTeamRequest extends FormRequest
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
        $teamId = $this->route('team')->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('teams', 'slug')->ignore($teamId)
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'is_open' => ['sometimes', 'boolean'],
            'max_members' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:1000'],
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
            'slug.required' => 'El slug del equipo es obligatorio.',
            'slug.unique' => 'Ya existe un equipo con este slug.',
            'slug.regex' => 'El slug solo puede contener letras minúsculas, números y guiones.',
            'description.max' => 'La descripción no puede exceder 1000 caracteres.',
            'max_members.min' => 'El número máximo de miembros debe ser al menos 1.',
            'max_members.max' => 'El número máximo de miembros no puede exceder 1000.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generar slug si se actualiza el nombre pero no el slug
        if ($this->has('name') && !$this->has('slug')) {
            $this->merge([
                'slug' => Str::slug($this->name)
            ]);
        }

        // Convertir is_open a boolean si está presente
        if ($this->has('is_open')) {
            $this->merge([
                'is_open' => $this->boolean('is_open')
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $team = $this->route('team');

            // Validar que el slug sea único dentro de la organización
            if ($this->has('slug') && $team->organization_id) {
                $exists = \App\Models\Team::where('slug', $this->slug)
                    ->where('organization_id', $team->organization_id)
                    ->where('id', '!=', $team->id)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('slug', 'Ya existe un equipo con este slug en la organización.');
                }
            }

            // Validar que max_members no sea menor que el número actual de miembros
            if ($this->has('max_members') && $this->max_members !== null) {
                $currentMembersCount = $team->activeMemberships()->count();
                
                if ($this->max_members < $currentMembersCount) {
                    $validator->errors()->add(
                        'max_members', 
                        "El número máximo de miembros no puede ser menor que el número actual de miembros ({$currentMembersCount})."
                    );
                }
            }

            // Si se está cerrando el equipo (is_open = false), verificar que no haya solicitudes pendientes
            if ($this->has('is_open') && !$this->is_open && $team->is_open) {
                // Aquí podrías agregar lógica para manejar solicitudes de unión pendientes
                // Por ejemplo, rechazarlas automáticamente o requerir confirmación
            }
        });
    }
}
