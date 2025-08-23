<?php

namespace App\Http\Requests\Api\V1\Vendor;

use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorRequest extends FormRequest
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
        $vendorId = $this->route('vendor')->id ?? $this->route('id');
        
        return [
            'name' => 'sometimes|string|max:255',
            'legal_name' => 'sometimes|string|max:255',
            'tax_id' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('vendors', 'tax_id')->ignore($vendorId),
            ],
            'registration_number' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('vendors', 'registration_number')->ignore($vendorId),
            ],
            'vendor_type' => [
                'sometimes',
                'string',
                Rule::in(array_keys(Vendor::getVendorTypes())),
            ],
            'industry' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:65535',
            'contact_person' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('vendors', 'email')->ignore($vendorId),
            ],
            'phone' => 'sometimes|string|max:50',
            'website' => 'sometimes|url|max:255',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'country' => 'sometimes|string|max:100',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'payment_terms' => 'sometimes|string|max:255',
            'credit_limit' => 'sometimes|numeric|min:0|max:999999999.99',
            'current_balance' => 'sometimes|numeric|min:0|max:999999999.99',
            'currency' => 'sometimes|string|max:3',
            'tax_rate' => 'sometimes|numeric|min:0|max:100',
            'discount_rate' => 'sometimes|numeric|min:0|max:100',
            'rating' => 'sometimes|numeric|min:0|max:5',
            'is_active' => 'sometimes|boolean',
            'is_verified' => 'sometimes|boolean',
            'is_preferred' => 'sometimes|boolean',
            'is_blacklisted' => 'sometimes|boolean',
            'contract_start_date' => 'sometimes|date|before_or_equal:contract_end_date',
            'contract_end_date' => 'sometimes|date|after_or_equal:contract_start_date',
            'contract_terms' => 'sometimes|array',
            'insurance_coverage' => 'sometimes|array',
            'certifications' => 'sometimes|array',
            'licenses' => 'sometimes|array',
            'performance_metrics' => 'sometimes|array',
            'quality_standards' => 'sometimes|array',
            'delivery_terms' => 'sometimes|array',
            'warranty_terms' => 'sometimes|array',
            'return_policy' => 'sometimes|array',
            'notes' => 'sometimes|string|max:1000',
            'tags' => 'sometimes|array',
            'logo' => 'sometimes|string|max:255',
            'documents' => 'sometimes|array',
            'bank_account' => 'sometimes|array',
            'payment_methods' => 'sometimes|array',
            'contact_history' => 'sometimes|array',
            'status' => [
                'sometimes',
                'string',
                Rule::in(array_keys(Vendor::getStatuses())),
            ],
            'risk_level' => [
                'sometimes',
                'string',
                Rule::in(array_keys(Vendor::getRiskLevels())),
            ],
            'compliance_status' => [
                'sometimes',
                'string',
                Rule::in(array_keys(Vendor::getComplianceStatuses())),
            ],
            'audit_frequency' => 'sometimes|integer|min:1|max:365',
            'last_audit_date' => 'sometimes|date|before_or_equal:today',
            'next_audit_date' => 'sometimes|date|after_or_equal:today',
            'financial_stability' => 'sometimes|array',
            'market_reputation' => 'sometimes|array',
            'competitor_analysis' => 'sometimes|array',
            'strategic_importance' => 'sometimes|array',
            'dependencies' => 'sometimes|array',
            'alternatives' => 'sometimes|array',
            'cost_benefit_analysis' => 'sometimes|array',
            'performance_reviews' => 'sometimes|array',
            'improvement_plans' => 'sometimes|array',
            'escalation_procedures' => 'sometimes|array',
            'approved_by' => 'sometimes|integer|exists:users,id',
            'approved_at' => 'sometimes|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'legal_name.max' => 'La razón social no puede tener más de 255 caracteres.',
            'tax_id.unique' => 'La identificación fiscal ya está registrada.',
            'registration_number.unique' => 'El número de registro ya está registrado.',
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
            'approved_by.exists' => 'El usuario aprobador debe existir en el sistema.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
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
            $vendor = $this->route('vendor');
            
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
                $lastAudit = \Carbon\Carbon::parse($this->contract_start_date);
                $nextAudit = \Carbon\Carbon::parse($this->contract_end_date);
                
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
                $contactPerson = $this->input('contact_person', $vendor->contact_person);
                $email = $this->input('email', $vendor->email);
                $phone = $this->input('phone', $vendor->phone);
                
                if (empty($contactPerson) && empty($email) && empty($phone)) {
                    $validator->errors()->add('contact_person', 'Debe proporcionar al menos un método de contacto para un proveedor activo.');
                }
            }

            // Validar que se proporcione información financiera si se establece un límite de crédito
            if ($this->filled('credit_limit')) {
                $paymentTerms = $this->input('payment_terms', $vendor->payment_terms);
                if (empty($paymentTerms)) {
                    $validator->errors()->add('payment_terms', 'Debe especificar los términos de pago si se establece un límite de crédito.');
                }
            }

            // Validar que se proporcione información de auditoría si se establece una frecuencia
            if ($this->filled('audit_frequency')) {
                $nextAuditDate = $this->input('next_audit_date', $vendor->next_audit_date);
                if (empty($nextAuditDate)) {
                    $validator->errors()->add('next_audit_date', 'Debe especificar la próxima fecha de auditoría si se establece una frecuencia.');
                }
            }

            // Proteger campos críticos si el proveedor ya está aprobado
            if ($vendor->approved_at) {
                $criticalFields = ['vendor_type', 'tax_id', 'registration_number', 'legal_name'];
                foreach ($criticalFields as $field) {
                    if ($this->has($field) && $this->input($field) !== $vendor->$field) {
                        $validator->errors()->add($field, 'No se puede modificar este campo en un proveedor ya aprobado.');
                    }
                }
            }

            // Proteger el estado de verificación si el proveedor tiene contratos activos
            if ($vendor->hasActiveContracts() && $this->boolean('is_verified') === false) {
                $validator->errors()->add('is_verified', 'No se puede desverificar un proveedor con contratos activos.');
            }

            // Proteger el estado preferido si el proveedor tiene pedidos pendientes
            if ($vendor->hasPendingOrders() && $this->boolean('is_preferred') === false) {
                $validator->errors()->add('is_preferred', 'No se puede quitar el estado preferido a un proveedor con pedidos pendientes.');
            }

            // Proteger el estado activo si el proveedor tiene obligaciones financieras
            if ($vendor->hasFinancialObligations() && $this->boolean('is_active') === false) {
                $validator->errors()->add('is_active', 'No se puede desactivar un proveedor con obligaciones financieras pendientes.');
            }

            // Validar que la reducción del nivel de riesgo sea justificada
            if ($this->filled('risk_level') && $this->input('risk_level') !== $vendor->risk_level) {
                $currentRisk = $vendor->risk_level;
                $newRisk = $this->input('risk_level');
                
                if ($this->isRiskReduction($currentRisk, $newRisk)) {
                    if (!$this->filled('notes') || strlen($this->input('notes')) < 50) {
                        $validator->errors()->add('notes', 'Debe proporcionar una justificación detallada (mínimo 50 caracteres) para reducir el nivel de riesgo.');
                    }
                }
            }

            // Validar que la mejora del estado de cumplimiento sea justificada
            if ($this->filled('compliance_status') && $this->input('compliance_status') !== $vendor->compliance_status) {
                $currentStatus = $vendor->compliance_status;
                $newStatus = $this->input('compliance_status');
                
                if ($this->isComplianceImprovement($currentStatus, $newStatus)) {
                    if (!$this->filled('notes') || strlen($this->input('notes')) < 50) {
                        $validator->errors()->add('notes', 'Debe proporcionar una justificación detallada (mínimo 50 caracteres) para mejorar el estado de cumplimiento.');
                    }
                }
            }
        });
    }

    /**
     * Verificar si se está reduciendo el nivel de riesgo
     */
    private function isRiskReduction(string $currentRisk, string $newRisk): bool
    {
        $riskLevels = ['extreme' => 5, 'high' => 4, 'medium' => 3, 'low' => 2, 'minimal' => 1];
        return ($riskLevels[$currentRisk] ?? 0) > ($riskLevels[$newRisk] ?? 0);
    }

    /**
     * Verificar si se está mejorando el estado de cumplimiento
     */
    private function isComplianceImprovement(string $currentStatus, string $newStatus): bool
    {
        $complianceLevels = [
            'non_compliant' => 1,
            'needs_audit' => 2,
            'pending_review' => 3,
            'under_review' => 4,
            'compliant' => 5
        ];
        return ($complianceLevels[$currentStatus] ?? 0) < ($complianceLevels[$newStatus] ?? 0);
    }
}
