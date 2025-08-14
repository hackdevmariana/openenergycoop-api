<?php

namespace App\Http\Requests\Api\V1\Company;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="StoreCompanyRequest",
 *     title="Store Company Request",
 *     description="Datos para crear una nueva empresa",
 *     required={"name", "cif", "contact_person", "company_address"},
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
class StoreCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Company::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'cif' => 'required|string|max:9|unique:companies,cif',
            'contact_person' => 'required|string|max:255',
            'company_address' => 'required|string',
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
            'name.required' => 'El nombre de la empresa es obligatorio.',
            'name.max' => 'El nombre de la empresa no puede tener más de 255 caracteres.',
            'cif.required' => 'El CIF es obligatorio.',
            'cif.max' => 'El CIF no puede tener más de 9 caracteres.',
            'cif.unique' => 'Ya existe una empresa con este CIF.',
            'contact_person.required' => 'La persona de contacto es obligatoria.',
            'contact_person.max' => 'La persona de contacto no puede tener más de 255 caracteres.',
            'company_address.required' => 'La dirección de la empresa es obligatoria.',
        ];
    }
}
