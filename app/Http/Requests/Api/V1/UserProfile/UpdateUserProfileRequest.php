<?php

namespace App\Http\Requests\Api\V1\UserProfile;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateUserProfileRequest",
 *     title="Update User Profile Request",
 *     description="Datos para actualizar un perfil de usuario",
 *     @OA\Property(
 *         property="bio",
 *         type="string",
 *         maxLength=500,
 *         description="Biografía del usuario",
 *         example="Apasionado por la energía renovable y la sostenibilidad"
 *     ),
 *     @OA\Property(
 *         property="municipality_id",
 *         type="string",
 *         maxLength=255,
 *         description="ID del municipio",
 *         example="28001"
 *     ),
 *     @OA\Property(
 *         property="role_in_cooperative",
 *         type="string",
 *         enum={"miembro", "voluntario", "promotor", "coordinador"},
 *         description="Rol en la cooperativa",
 *         example="miembro"
 *     ),
 *     @OA\Property(
 *         property="newsletter_opt_in",
 *         type="boolean",
 *         description="Suscripción al newsletter",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="show_in_rankings",
 *         type="boolean",
 *         description="Mostrar en rankings públicos",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="birth_date",
 *         type="string",
 *         format="date",
 *         description="Fecha de nacimiento",
 *         example="1985-03-22"
 *     ),
 *     @OA\Property(
 *         property="team_id",
 *         type="string",
 *         maxLength=255,
 *         description="ID del equipo",
 *         example="equipo-verde"
 *     )
 * )
 */
class UpdateUserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // El usuario solo puede actualizar su propio perfil
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bio' => 'nullable|string|max:500',
            'municipality_id' => 'nullable|string|max:255',
            'role_in_cooperative' => 'nullable|in:miembro,voluntario,promotor,coordinador',
            'newsletter_opt_in' => 'boolean',
            'show_in_rankings' => 'boolean',
            'birth_date' => 'nullable|date|before:' . now()->subYears(13)->toDateString(),
            'team_id' => 'nullable|string|max:255',
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
            'bio.max' => 'La biografía no puede tener más de 500 caracteres.',
            'municipality_id.max' => 'El ID del municipio no puede tener más de 255 caracteres.',
            'role_in_cooperative.in' => 'El rol en la cooperativa debe ser uno de: miembro, voluntario, promotor, coordinador.',
            'newsletter_opt_in.boolean' => 'La suscripción al newsletter debe ser verdadero o falso.',
            'show_in_rankings.boolean' => 'La visibilidad en rankings debe ser verdadero o falso.',
            'birth_date.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'birth_date.before' => 'Debes tener al menos 13 años.',
            'team_id.max' => 'El ID del equipo no puede tener más de 255 caracteres.',
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @return array
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // Filtrar solo los campos que están presentes en la request
        return array_filter($validated, function ($value) {
            return $value !== null;
        });
    }
}
