<?php

namespace App\Http\Requests\Api\V1\Provider;

use Illuminate\Foundation\Http\FormRequest;

class StoreProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Autorización manejada por middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'company_id' => 'required|exists:companies,id',
            'type' => 'required|in:renewable,traditional,hybrid',
            'rating' => 'nullable|numeric|min:0|max:5',
            'total_products' => 'nullable|integer|min:0',
            'sustainability_score' => 'nullable|integer|min:0|max:100',
            'verification_status' => 'nullable|in:pending,verified,rejected',
            'is_active' => 'nullable|boolean',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:255',
            'contact_info' => 'nullable|array',
            'contact_info.email' => 'nullable|email|max:255',
            'contact_info.phone' => 'nullable|string|max:50',
            'contact_info.website' => 'nullable|url|max:255',
            'metadata' => 'nullable|array',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del proveedor es obligatorio.',
            'company_id.required' => 'La empresa asociada es obligatoria.',
            'company_id.exists' => 'La empresa seleccionada no existe.',
            'type.required' => 'El tipo de proveedor es obligatorio.',
            'type.in' => 'El tipo debe ser: renovable, tradicional o híbrido.',
            'rating.numeric' => 'La calificación debe ser un número.',
            'rating.max' => 'La calificación no puede ser mayor a 5.',
            'sustainability_score.integer' => 'La puntuación de sostenibilidad debe ser un número entero.',
            'sustainability_score.max' => 'La puntuación de sostenibilidad no puede ser mayor a 100.',
            'verification_status.in' => 'El estado de verificación debe ser: pendiente, verificado o rechazado.',
            'contact_info.email.email' => 'El email debe tener un formato válido.',
            'contact_info.website.url' => 'La página web debe tener un formato válido.',
            'tag_ids.*.exists' => 'Una o más etiquetas seleccionadas no existen.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'company_id' => 'empresa',
            'type' => 'tipo',
            'rating' => 'calificación',
            'total_products' => 'total de productos',
            'sustainability_score' => 'puntuación de sostenibilidad',
            'verification_status' => 'estado de verificación',
            'is_active' => 'activo',
            'certifications' => 'certificaciones',
            'contact_info' => 'información de contacto',
            'tag_ids' => 'etiquetas'
        ];
    }
}
