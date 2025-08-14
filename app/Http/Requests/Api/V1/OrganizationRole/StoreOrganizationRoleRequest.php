<?php

namespace App\Http\Requests\Api\V1\OrganizationRole;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="StoreOrganizationRoleRequest",
 *     title="Store Organization Role Request",
 *     description="Datos para crear un nuevo rol de organización",
 *     required={"organization_id", "name"},
 *     @OA\Property(
 *         property="organization_id",
 *         type="integer",
 *         description="ID de la organización",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Nombre del rol",
 *         example="Gestor de Proyectos"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         maxLength=255,
 *         description="Slug del rol (se genera automáticamente si no se proporciona)",
 *         example="gestor-proyectos"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Descripción del rol",
 *         example="Responsable de gestionar proyectos de la organización"
 *     ),
 *     @OA\Property(
 *         property="permissions",
 *         type="array",
 *         @OA\Items(type="string"),
 *         description="Lista de permisos del rol",
 *         example={"project.view", "project.create", "project.update"}
 *     )
 * )
 */
class StoreOrganizationRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\OrganizationRole::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'organization_id' => 'required|integer|exists:organizations,id',
            'name' => 'required|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:organization_roles,slug',
            'description' => 'sometimes|string',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'organization_id.required' => 'La organización es obligatoria.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.max' => 'El nombre del rol no puede tener más de 255 caracteres.',
            'slug.max' => 'El slug no puede tener más de 255 caracteres.',
            'slug.unique' => 'Ya existe un rol con este slug.',
            'permissions.array' => 'Los permisos deben ser una lista.',
        ];
    }
}
