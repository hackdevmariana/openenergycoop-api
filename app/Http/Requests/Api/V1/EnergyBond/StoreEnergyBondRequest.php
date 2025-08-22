<?php

namespace App\Http\Requests\Api\V1\EnergyBond;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnergyBondRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'bond_type' => ['required', 'string', Rule::in([
                'solar', 'wind', 'hydro', 'biomass', 'geothermal', 'nuclear', 'hybrid', 'other'
            ])],
            'status' => ['required', 'string', Rule::in([
                'draft', 'pending', 'approved', 'active', 'inactive', 'expired', 'cancelled', 'rejected'
            ])],
            'priority' => ['required', 'string', Rule::in([
                'low', 'medium', 'high', 'urgent', 'critical'
            ])],
            'face_value' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'maturity_date' => ['required', 'date', 'after:today'],
            'issue_date' => ['nullable', 'date', 'before_or_equal:maturity_date'],
            'coupon_frequency' => ['required', 'string', Rule::in([
                'monthly', 'quarterly', 'semi_annually', 'annually', 'at_maturity'
            ])],
            'payment_method' => ['required', 'string', Rule::in([
                'bank_transfer', 'credit_card', 'crypto', 'check', 'cash', 'other'
            ])],
            'currency' => ['required', 'string', 'size:3'],
            'total_units' => ['required', 'integer', 'min:1', 'max:999999999'],
            'available_units' => ['required', 'integer', 'min:0', 'max:total_units'],
            'minimum_investment' => ['required', 'numeric', 'min:0.01', 'max:face_value'],
            'maximum_investment' => ['nullable', 'numeric', 'min:minimum_investment', 'max:face_value'],
            'early_redemption_allowed' => ['boolean'],
            'early_redemption_fee' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'transferable' => ['boolean'],
            'transfer_fee' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'collateral_required' => ['boolean'],
            'collateral_type' => ['nullable', 'string', 'max:255'],
            'collateral_value' => ['nullable', 'numeric', 'min:0'],
            'guarantee_provided' => ['boolean'],
            'guarantor_name' => ['nullable', 'string', 'max:255'],
            'guarantee_amount' => ['nullable', 'numeric', 'min:0'],
            'risk_rating' => ['required', 'string', Rule::in([
                'aaa', 'aa', 'a', 'bbb', 'bb', 'b', 'ccc', 'cc', 'c', 'd'
            ])],
            'credit_score_required' => ['nullable', 'integer', 'min:300', 'max:850'],
            'income_requirement' => ['nullable', 'numeric', 'min:0'],
            'employment_verification' => ['boolean'],
            'bank_statement_required' => ['boolean'],
            'tax_documentation_required' => ['boolean'],
            'kyc_required' => ['boolean'],
            'aml_check_required' => ['boolean'],
            'is_public' => ['boolean'],
            'is_featured' => ['boolean'],
            'requires_approval' => ['boolean'],
            'is_template' => ['boolean'],
            'version' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'managed_by' => ['nullable', 'exists:users,id'],
            'approved_by' => ['nullable', 'exists:users,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:65535'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del bono es obligatorio.',
            'name.max' => 'El nombre del bono no puede exceder 255 caracteres.',
            'bond_type.required' => 'El tipo de bono es obligatorio.',
            'bond_type.in' => 'El tipo de bono seleccionado no es válido.',
            'status.required' => 'El estado del bono es obligatorio.',
            'status.in' => 'El estado seleccionado no es válido.',
            'priority.required' => 'La prioridad del bono es obligatoria.',
            'priority.in' => 'La prioridad seleccionada no es válida.',
            'face_value.required' => 'El valor nominal del bono es obligatorio.',
            'face_value.numeric' => 'El valor nominal debe ser un número.',
            'face_value.min' => 'El valor nominal debe ser mayor a 0.',
            'face_value.max' => 'El valor nominal no puede exceder 999,999,999.99.',
            'interest_rate.required' => 'La tasa de interés es obligatoria.',
            'interest_rate.numeric' => 'La tasa de interés debe ser un número.',
            'interest_rate.min' => 'La tasa de interés no puede ser negativa.',
            'interest_rate.max' => 'La tasa de interés no puede exceder 100%.',
            'maturity_date.required' => 'La fecha de vencimiento es obligatoria.',
            'maturity_date.date' => 'La fecha de vencimiento debe ser una fecha válida.',
            'maturity_date.after' => 'La fecha de vencimiento debe ser posterior a hoy.',
            'issue_date.before_or_equal' => 'La fecha de emisión debe ser anterior o igual a la fecha de vencimiento.',
            'coupon_frequency.required' => 'La frecuencia del cupón es obligatoria.',
            'coupon_frequency.in' => 'La frecuencia del cupón seleccionada no es válida.',
            'payment_method.required' => 'El método de pago es obligatorio.',
            'payment_method.in' => 'El método de pago seleccionado no es válido.',
            'currency.required' => 'La moneda es obligatoria.',
            'currency.size' => 'La moneda debe tener exactamente 3 caracteres.',
            'total_units.required' => 'El número total de unidades es obligatorio.',
            'total_units.integer' => 'El número total de unidades debe ser un número entero.',
            'total_units.min' => 'El número total de unidades debe ser al menos 1.',
            'total_units.max' => 'El número total de unidades no puede exceder 999,999,999.',
            'available_units.required' => 'El número de unidades disponibles es obligatorio.',
            'available_units.integer' => 'El número de unidades disponibles debe ser un número entero.',
            'available_units.min' => 'El número de unidades disponibles no puede ser negativo.',
            'available_units.max' => 'El número de unidades disponibles no puede exceder el total de unidades.',
            'minimum_investment.required' => 'La inversión mínima es obligatoria.',
            'minimum_investment.numeric' => 'La inversión mínima debe ser un número.',
            'minimum_investment.min' => 'La inversión mínima debe ser mayor a 0.',
            'minimum_investment.max' => 'La inversión mínima no puede exceder el valor nominal.',
            'maximum_investment.min' => 'La inversión máxima debe ser al menos igual a la inversión mínima.',
            'maximum_investment.max' => 'La inversión máxima no puede exceder el valor nominal.',
            'early_redemption_fee.min' => 'La comisión de redención anticipada no puede ser negativa.',
            'early_redemption_fee.max' => 'La comisión de redención anticipada no puede exceder 100%.',
            'transfer_fee.min' => 'La comisión de transferencia no puede ser negativa.',
            'transfer_fee.max' => 'La comisión de transferencia no puede exceder 100%.',
            'collateral_value.min' => 'El valor del colateral no puede ser negativo.',
            'guarantee_amount.min' => 'El monto de la garantía no puede ser negativo.',
            'risk_rating.required' => 'La calificación de riesgo es obligatoria.',
            'risk_rating.in' => 'La calificación de riesgo seleccionada no es válida.',
            'credit_score_required.min' => 'El puntaje de crédito requerido debe ser al menos 300.',
            'credit_score_required.max' => 'El puntaje de crédito requerido no puede exceder 850.',
            'income_requirement.min' => 'El requisito de ingresos no puede ser negativo.',
            'organization_id.exists' => 'La organización seleccionada no existe.',
            'managed_by.exists' => 'El usuario seleccionado para gestión no existe.',
            'approved_by.exists' => 'El usuario seleccionado para aprobación no existe.',
            'tags.array' => 'Las etiquetas deben ser un array.',
            'tags.*.string' => 'Cada etiqueta debe ser una cadena de texto.',
            'tags.*.max' => 'Cada etiqueta no puede exceder 100 caracteres.',
            'documents.array' => 'Los documentos deben ser un array.',
            'documents.*.file' => 'Cada documento debe ser un archivo válido.',
            'documents.*.mimes' => 'Los documentos deben ser de tipo: pdf, doc, docx, xls, xlsx, jpg, jpeg, png.',
            'documents.*.max' => 'Cada documento no puede exceder 10MB.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre del bono',
            'description' => 'descripción',
            'bond_type' => 'tipo de bono',
            'status' => 'estado',
            'priority' => 'prioridad',
            'face_value' => 'valor nominal',
            'interest_rate' => 'tasa de interés',
            'maturity_date' => 'fecha de vencimiento',
            'issue_date' => 'fecha de emisión',
            'coupon_frequency' => 'frecuencia del cupón',
            'payment_method' => 'método de pago',
            'currency' => 'moneda',
            'total_units' => 'total de unidades',
            'available_units' => 'unidades disponibles',
            'minimum_investment' => 'inversión mínima',
            'maximum_investment' => 'inversión máxima',
            'early_redemption_allowed' => 'redención anticipada permitida',
            'early_redemption_fee' => 'comisión de redención anticipada',
            'transferable' => 'transferible',
            'transfer_fee' => 'comisión de transferencia',
            'collateral_required' => 'colateral requerido',
            'collateral_type' => 'tipo de colateral',
            'collateral_value' => 'valor del colateral',
            'guarantee_provided' => 'garantía proporcionada',
            'guarantor_name' => 'nombre del garante',
            'guarantee_amount' => 'monto de la garantía',
            'risk_rating' => 'calificación de riesgo',
            'credit_score_required' => 'puntaje de crédito requerido',
            'income_requirement' => 'requisito de ingresos',
            'employment_verification' => 'verificación de empleo',
            'bank_statement_required' => 'estado de cuenta bancario requerido',
            'tax_documentation_required' => 'documentación fiscal requerida',
            'kyc_required' => 'KYC requerido',
            'aml_check_required' => 'verificación AML requerida',
            'is_public' => 'público',
            'is_featured' => 'destacado',
            'requires_approval' => 'requiere aprobación',
            'is_template' => 'es plantilla',
            'version' => 'versión',
            'sort_order' => 'orden de clasificación',
            'organization_id' => 'organización',
            'managed_by' => 'gestionado por',
            'approved_by' => 'aprobado por',
            'tags' => 'etiquetas',
            'notes' => 'notas',
            'documents' => 'documentos',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_public' => $this->boolean('is_public'),
            'is_featured' => $this->boolean('is_featured'),
            'requires_approval' => $this->boolean('requires_approval'),
            'is_template' => $this->boolean('is_template'),
            'early_redemption_allowed' => $this->boolean('early_redemption_allowed'),
            'transferable' => $this->boolean('transferable'),
            'collateral_required' => $this->boolean('collateral_required'),
            'guarantee_provided' => $this->boolean('guarantee_provided'),
            'employment_verification' => $this->boolean('employment_verification'),
            'bank_statement_required' => $this->boolean('bank_statement_required'),
            'tax_documentation_required' => $this->boolean('tax_documentation_required'),
            'kyc_required' => $this->boolean('kyc_required'),
            'aml_check_required' => $this->boolean('aml_check_required'),
        ]);
    }
}
