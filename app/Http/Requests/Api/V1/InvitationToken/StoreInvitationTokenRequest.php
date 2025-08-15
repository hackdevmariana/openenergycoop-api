<?php

namespace App\Http\Requests\Api\V1\InvitationToken;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="StoreInvitationTokenRequest",
 *     title="Store Invitation Token Request",
 *     description="Datos para crear un nuevo token de invitación",
 *     required={"organization_id", "organization_role_id"},
 *     @OA\Property(
 *         property="organization_id",
 *         type="integer",
 *         description="ID de la organización a la que se invita",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="organization_role_id",
 *         type="integer",
 *         description="ID del rol que se asignará al usuario invitado",
 *         example=2
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Email del usuario invitado (opcional)",
 *         example="usuario@example.com"
 *     ),
 *     @OA\Property(
 *         property="expires_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha de expiración del token (opcional, por defecto 7 días)",
 *         example="2024-02-15T10:30:00Z"
 *     )
 * )
 */
class StoreInvitationTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\InvitationToken::class);
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
            'organization_role_id' => 'required|integer|exists:organization_roles,id',
            'email' => 'nullable|email|max:255',
            'expires_at' => 'nullable|date|after:now',
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
            'organization_role_id.required' => 'El rol de organización es obligatorio.',
            'organization_role_id.exists' => 'El rol de organización seleccionado no existe.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.max' => 'El email no puede tener más de 255 caracteres.',
            'expires_at.date' => 'La fecha de expiración debe ser una fecha válida.',
            'expires_at.after' => 'La fecha de expiración debe ser posterior a la fecha actual.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verificar que el rol pertenezca a la organización
            if ($this->organization_id && $this->organization_role_id) {
                $role = \App\Models\OrganizationRole::find($this->organization_role_id);
                if ($role && $role->organization_id !== (int) $this->organization_id) {
                    $validator->errors()->add('organization_role_id', 'El rol seleccionado no pertenece a la organización especificada.');
                }
            }
        });
    }
}
