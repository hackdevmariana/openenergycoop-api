<?php

namespace App\Http\Requests\Api\V1\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateCompanyRequest",
 *     title="Update Company Request",
 *     description="Datos para actualizar una empresa existente",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Nombre de la empresa",
 *         example="Energía Verde S.L."
 *     ),
 *     @OA\Property(
 *         property="cif",
 *         type="string",
 *         maxLength=9,
 *         description="CIF de la empresa",
 *         example="B12345678"
 *     ),
 *     @OA\Property(
 *         property="contact_person",
 *         type="string",
 *         maxLength=255,
 *         description="Persona de contacto",
 *         example="Juan Pérez García"
 *     ),
 *     @OA\Property(
 *         property="company_address",
 *         type="string",
 *         description="Dirección completa de la empresa",
 *         example="Calle Mayor 123, 28001 Madrid, España"
 *     )
 * )
 */
class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('company'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->route('company')->id;
        
        return [
            'name' => 'sometimes|string|max:255',
            'cif' => [
                'sometimes',
                'string',
                'max:9',
                Rule::unique('companies', 'cif')->ignore($companyId)
            ],
            'contact_person' => 'sometimes|string|max:255',
            'company_address' => 'sometimes|string',
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
            'name.max' => 'El nombre de la empresa no puede tener más de 255 caracteres.',
            'cif.max' => 'El CIF no puede tener más de 9 caracteres.',
            'cif.unique' => 'Ya existe otra empresa con este CIF.',
            'contact_person.max' => 'La persona de contacto no puede tener más de 255 caracteres.',
        ];
    }
}
