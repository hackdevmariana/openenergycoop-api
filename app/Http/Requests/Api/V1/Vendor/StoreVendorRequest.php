<?php

namespace App\Http\Requests\Api\V1\Vendor;

use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVendorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'legal_name' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:255|unique:vendors,tax_id',
            'registration_number' => 'nullable|string|max:255|unique:vendors,registration_number',
            'vendor_type' => [
                'required',
                'string',
                Rule::in(array_keys(Vendor::getVendorTypes())),
            ],
            'industry' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:65535',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:vendors,email',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'payment_terms' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0|max:999999999.99',
            'current_balance' => 'nullable|numeric|min:0|max:999999999.99',
            'currency' => 'nullable|string|max:3',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'is_preferred' => 'boolean',
            'is_blacklisted' => 'boolean',
            'contract_start_date' => 'nullable|date|before_or_equal:contract_end_date',
            'contract_end_date' => 'nullable|date|after_or_equal:contract_start_date',
            'contract_terms' => 'nullable|array',
            'insurance_coverage' => 'nullable|array',
            'certifications' => 'nullable|array',
            'licenses' => 'nullable|array',
            'performance_metrics' => 'nullable|array',
            'quality_standards' => 'nullable|array',
            'delivery_terms' => 'nullable|array',
            'warranty_terms' => 'nullable|array',
            'return_policy' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'logo' => 'nullable|string|max:255',
            'documents' => 'nullable|array',
            'bank_account' => 'nullable|array',
            'payment_methods' => 'nullable|array',
            'contact_history' => 'nullable|array',
            'status' => [
                'nullable',
                'string',
                Rule::in(array_keys(Vendor::getStatuses())),
            ],
            'risk_level' => [
                'nullable',
                'string',
                Rule::in(array_keys(Vendor::getRiskLevels())),
            ],
            'compliance_status' => [
                'nullable',
                'string',
                Rule::in(array_keys(Vendor::getComplianceStatuses())),
            ],
            'audit_frequency' => 'nullable|integer|min:1|max:365',
            'last_audit_date' => 'nullable|date|before_or_equal:today',
            'next_audit_date' => 'nullable|date|after_or_equal:today',
            'financial_stability' => 'nullable|array',
            'market_reputation' => 'nullable|array',
            'competitor_analysis' => 'nullable|array',
            'strategic_importance' => 'nullable|array',
            'dependencies' => 'nullable|array',
            'alternatives' => 'nullable|array',
            'cost_benefit_analysis' => 'nullable|array',
            'performance_reviews' => 'nullable|array',
            'improvement_plans' => 'nullable|array',
            'escalation_procedures' => 'nullable|array',
            'created_by' => 'nullable|integer|exists:users,id',
            'approved_by' => 'nullable|integer|exists:users,id',
            'approved_at' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del proveedor es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'legal_name.required' => 'La razón social es obligatoria.',
            'legal_name.max' => 'La razón social no puede tener más de 255 caracteres.',
            'tax_id.unique' => 'La identificación fiscal ya está registrada.',
            'registration_number.unique' => 'El número de registro ya está registrado.',
            'vendor_type.required' => 'El tipo de proveedor es obligatorio.',
            'vendor_type.in' => 'El tipo de proveedor debe ser uno de los valores permitidos.',
            'industry.max' => 'La industria no puede tener más de 255 caracteres.',
            'description.max' => 'La descripción no puede tener más de 65535 caracteres.',
            'contact_person.max' => 'La persona de contacto no puede tener más de 255 caracteres.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'El email ya está registrado.',
            'phone.max' => 'El teléfono no puede tener más de 50 caracteres.',
            'website.url' => 'El sitio web debe tener un formato válido.',
            'address.max' => 'La dirección no puede tener más de 500 caracteres.',
            'city.max' => 'La ciudad no puede tener más de 100 caracteres.',
            'state.max' => 'El estado no puede tener más de 100 caracteres.',
            'postal_code.max' => 'El código postal no puede tener más de 20 caracteres.',
            'country.max' => 'El país no puede tener más de 100 caracteres.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180.',
            'payment_terms.max' => 'Los términos de pago no pueden tener más de 255 caracteres.',
            'credit_limit.min' => 'El límite de crédito debe ser al menos 0.',
            'credit_limit.max' => 'El límite de crédito no puede ser mayor a 999,999,999.99.',
            'current_balance.min' => 'El saldo actual debe ser al menos 0.',
            'current_balance.max' => 'El saldo actual no puede ser mayor a 999,999,999.99.',
            'currency.max' => 'La moneda no puede tener más de 3 caracteres.',
            'tax_rate.min' => 'La tasa de impuesto debe ser al menos 0.',
            'tax_rate.max' => 'La tasa de impuesto no puede ser mayor a 100.',
            'discount_rate.min' => 'La tasa de descuento debe ser al menos 0.',
            'discount_rate.max' => 'La tasa de descuento no puede ser mayor a 100.',
            'rating.min' => 'La calificación debe ser al menos 0.',
            'rating.max' => 'La calificación no puede ser mayor a 5.',
            'contract_start_date.before_or_equal' => 'La fecha de inicio del contrato debe ser anterior o igual a la fecha de fin.',
            'contract_end_date.after_or_equal' => 'La fecha de fin del contrato debe ser posterior o igual a la fecha de inicio.',
            'notes.max' => 'Las notas no pueden tener más de 1000 caracteres.',
            'logo.max' => 'La ruta del logo no puede tener más de 255 caracteres.',
            'status.in' => 'El estado debe ser uno de los valores permitidos.',
            'risk_level.in' => 'El nivel de riesgo debe ser uno de los valores permitidos.',
            'compliance_status.in' => 'El estado de cumplimiento debe ser uno de los valores permitidos.',
            'audit_frequency.min' => 'La frecuencia de auditoría debe ser al menos 1.',
            'audit_frequency.max' => 'La frecuencia de auditoría no puede ser mayor a 365.',
            'last_audit_date.before_or_equal' => 'La fecha de la última auditoría debe ser hoy o anterior.',
            'next_audit_date.after_or_equal' => 'La fecha de la próxima auditoría debe ser hoy o posterior.',
            'created_by.exists' => 'El usuario creador debe existir en el sistema.',
            'approved_by.exists' => 'El usuario aprobador debe existir en el sistema.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Asignar usuario creador si no se proporciona
        if (!$this->has('created_by')) {
            $this->merge(['created_by' => auth()->id()]);
        }

        // Valores por defecto
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'is_verified' => $this->boolean('is_verified', false),
            'is_preferred' => $this->boolean('is_preferred', false),
            'is_blacklisted' => $this->boolean('is_blacklisted', false),
            'status' => $this->input('status', 'pending'),
            'risk_level' => $this->input('risk_level', 'medium'),
            'compliance_status' => $this->input('compliance_status', 'pending_review'),
        ]);

        // Convertir campos de array
        $arrayFields = [
            'contract_terms', 'insurance_coverage', 'certifications', 'licenses',
            'performance_metrics', 'quality_standards', 'delivery_terms',
            'warranty_terms', 'return_policy', 'tags', 'documents',
            'bank_account', 'payment_methods', 'contact_history',
            'financial_stability', 'market_reputation', 'competitor_analysis',
            'strategic_importance', 'dependencies', 'alternatives',
            'cost_benefit_analysis', 'performance_reviews', 'improvement_plans',
            'escalation_procedures'
        ];

        foreach ($arrayFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => json_decode($this->input($field), true)]);
            }
        }

        // Convertir campos booleanos
        $booleanFields = ['is_active', 'is_verified', 'is_preferred', 'is_blacklisted'];
        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $this->merge([$field => (bool) $this->input($field)]);
            }
        }

        // Convertir campos numéricos
        $numericFields = [
            'credit_limit', 'current_balance', 'tax_rate', 'discount_rate',
            'rating', 'latitude', 'longitude', 'audit_frequency'
        ];
        foreach ($numericFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([$field => (float) $this->input($field)]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que el límite de crédito sea mayor que el saldo actual
            if ($this->filled('credit_limit') && $this->filled('current_balance')) {
                if ($this->credit_limit < $this->current_balance) {
                    $validator->errors()->add('credit_limit', 'El límite de crédito debe ser mayor o igual al saldo actual.');
                }
            }

            // Validar que la fecha de fin del contrato sea posterior a la fecha de inicio
            if ($this->filled('contract_start_date') && $this->filled('contract_end_date')) {
                $startDate = \Carbon\Carbon::parse($this->contract_start_date);
                $endDate = \Carbon\Carbon::parse($this->contract_end_date);
                
                if ($endDate->lte($startDate)) {
                    $validator->errors()->add('contract_end_date', 'La fecha de fin del contrato debe ser posterior a la fecha de inicio.');
                }
            }

            // Validar que la próxima fecha de auditoría sea posterior a la última
            if ($this->filled('last_audit_date') && $this->filled('next_audit_date')) {
                $lastAudit = \Carbon\Carbon::parse($this->last_audit_date);
                $nextAudit = \Carbon\Carbon::parse($this->next_audit_date);
                
                if ($nextAudit->lte($lastAudit)) {
                    $validator->errors()->add('next_audit_date', 'La próxima fecha de auditoría debe ser posterior a la última auditoría.');
                }
            }

            // Validar que la frecuencia de auditoría sea consistente con las fechas
            if ($this->filled('audit_frequency') && $this->filled('last_audit_date') && $this->filled('next_audit_date')) {
                $lastAudit = \Carbon\Carbon::parse($this->last_audit_date);
                $nextAudit = \Carbon\Carbon::parse($this->next_audit_date);
                $expectedNext = $lastAudit->addDays($this->audit_frequency);
                
                $tolerance = 7; // 7 días de tolerancia
                if ($nextAudit->diffInDays($expectedNext) > $tolerance) {
                    $validator->errors()->add('next_audit_date', "La próxima fecha de auditoría debe ser aproximadamente {$this->audit_frequency} días después de la última auditoría.");
                }
            }

            // Validar que el proveedor no esté en la lista negra si se marca como preferido
            if ($this->boolean('is_preferred') && $this->boolean('is_blacklisted')) {
                $validator->errors()->add('is_preferred', 'Un proveedor en la lista negra no puede ser marcado como preferido.');
            }

            // Validar que el proveedor esté verificado si se marca como preferido
            if ($this->boolean('is_preferred') && !$this->boolean('is_verified')) {
                $validator->errors()->add('is_preferred', 'Un proveedor debe estar verificado para ser marcado como preferido.');
            }

            // Validar que el proveedor esté activo si se marca como preferido
            if ($this->boolean('is_preferred') && !$this->boolean('is_active')) {
                $validator->errors()->add('is_preferred', 'Un proveedor debe estar activo para ser marcado como preferido.');
            }

            // Validar que el proveedor esté activo si se marca como verificado
            if ($this->boolean('is_verified') && !$this->boolean('is_active')) {
                $validator->errors()->add('is_verified', 'Un proveedor debe estar activo para ser marcado como verificado.');
            }

            // Validar que el proveedor esté activo si se aprueba
            if ($this->filled('approved_at') && !$this->boolean('is_active')) {
                $validator->errors()->add('approved_at', 'Un proveedor debe estar activo para ser aprobado.');
            }

            // Validar que el proveedor esté verificado si se aprueba
            if ($this->filled('approved_at') && !$this->boolean('is_verified')) {
                $validator->errors()->add('approved_at', 'Un proveedor debe estar verificado para ser aprobado.');
            }

            // Validar que la calificación sea consistente con el estado
            if ($this->filled('rating') && $this->boolean('is_blacklisted')) {
                if ($this->rating > 2.0) {
                    $validator->errors()->add('rating', 'Un proveedor en la lista negra no puede tener una calificación alta.');
                }
            }

            // Validar que el nivel de riesgo sea consistente con el estado de cumplimiento
            if ($this->filled('risk_level') && $this->filled('compliance_status')) {
                if ($this->risk_level === 'extreme' && $this->compliance_status === 'compliant') {
                    $validator->errors()->add('risk_level', 'Un proveedor con riesgo extremo no puede tener estado de cumplimiento "cumple".');
                }
            }

            // Validar que se proporcione información de contacto si el proveedor está activo
            if ($this->boolean('is_active')) {
                if (empty($this->contact_person) && empty($this->email) && empty($this->phone)) {
                    $validator->errors()->add('contact_person', 'Debe proporcionar al menos un método de contacto para un proveedor activo.');
                }
            }

            // Validar que se proporcione información financiera si se establece un límite de crédito
            if ($this->filled('credit_limit')) {
                if (empty($this->payment_terms)) {
                    $validator->errors()->add('payment_terms', 'Debe especificar los términos de pago si se establece un límite de crédito.');
                }
            }

            // Validar que se proporcione información de auditoría si se establece una frecuencia
            if ($this->filled('audit_frequency')) {
                if (empty($this->next_audit_date)) {
                    $validator->errors()->add('next_audit_date', 'Debe especificar la próxima fecha de auditoría si se establece una frecuencia.');
                }
            }
        });
    }
}
