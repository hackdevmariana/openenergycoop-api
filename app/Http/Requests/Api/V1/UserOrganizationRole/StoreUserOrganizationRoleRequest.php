<?php

namespace App\Http\Requests\Api\V1\UserOrganizationRole;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="StoreUserOrganizationRoleRequest",
 *     title="Store User Organization Role Request",
 *     description="Datos para crear una nueva asignación de rol de usuario en una organización",
 *     required={"user_id", "organization_id", "organization_role_id"},
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="ID del usuario",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="organization_id",
 *         type="integer",
 *         description="ID de la organización",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="organization_role_id",
 *         type="integer",
 *         description="ID del rol de organización",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="assigned_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha y hora de asignación (se establece automáticamente si no se proporciona)",
 *         example="2024-01-15T10:30:00Z"
 *     )
 * )
 */
class StoreUserOrganizationRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\UserOrganizationRole::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'organization_id' => 'required|integer|exists:organizations,id',
            'organization_role_id' => 'required|integer|exists:organization_roles,id',
            'assigned_at' => 'sometimes|date',
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
            'user_id.required' => 'El usuario es obligatorio.',
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'organization_id.required' => 'La organización es obligatoria.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
            'organization_role_id.required' => 'El rol de organización es obligatorio.',
            'organization_role_id.exists' => 'El rol de organización seleccionado no existe.',
            'assigned_at.date' => 'La fecha de asignación debe ser válida.',
        ];
    }
}
