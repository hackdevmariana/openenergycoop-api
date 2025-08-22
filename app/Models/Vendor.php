<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'legal_name',
        'tax_id',
        'registration_number',
        'vendor_type',
        'industry',
        'description',
        'contact_person',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'payment_terms',
        'credit_limit',
        'current_balance',
        'currency',
        'tax_rate',
        'discount_rate',
        'rating',
        'is_active',
        'is_verified',
        'is_preferred',
        'is_blacklisted',
        'contract_start_date',
        'contract_end_date',
        'contract_terms',
        'insurance_coverage',
        'certifications',
        'licenses',
        'performance_metrics',
        'quality_standards',
        'delivery_terms',
        'warranty_terms',
        'return_policy',
        'notes',
        'tags',
        'logo',
        'documents',
        'bank_account',
        'payment_methods',
        'contact_history',
        'created_by',
        'approved_by',
        'approved_at',
        'status',
        'risk_level',
        'compliance_status',
        'audit_frequency',
        'last_audit_date',
        'next_audit_date',
        'financial_stability',
        'market_reputation',
        'competitor_analysis',
        'strategic_importance',
        'dependencies',
        'alternatives',
        'cost_benefit_analysis',
        'performance_reviews',
        'improvement_plans',
        'escalation_procedures',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'rating' => 'decimal:1',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'is_preferred' => 'boolean',
        'is_blacklisted' => 'boolean',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'contract_terms' => 'array',
        'insurance_coverage' => 'array',
        'certifications' => 'array',
        'licenses' => 'array',
        'performance_metrics' => 'array',
        'quality_standards' => 'array',
        'delivery_terms' => 'array',
        'warranty_terms' => 'array',
        'return_policy' => 'array',
        'tags' => 'array',
        'documents' => 'array',
        'bank_account' => 'array',
        'payment_methods' => 'array',
        'contact_history' => 'array',
        'approved_at' => 'datetime',
        'audit_frequency' => 'integer',
        'last_audit_date' => 'date',
        'next_audit_date' => 'date',
        'financial_stability' => 'array',
        'market_reputation' => 'array',
        'competitor_analysis' => 'array',
        'strategic_importance' => 'array',
        'dependencies' => 'array',
        'alternatives' => 'array',
        'cost_benefit_analysis' => 'array',
        'performance_reviews' => 'array',
        'improvement_plans' => 'array',
        'escalation_procedures' => 'array',
    ];

    // Enums
    const VENDOR_TYPE_SUPPLIER = 'supplier';
    const VENDOR_TYPE_SERVICE_PROVIDER = 'service_provider';
    const VENDOR_TYPE_CONTRACTOR = 'contractor';
    const VENDOR_TYPE_CONSULTANT = 'consultant';
    const VENDOR_TYPE_MANUFACTURER = 'manufacturer';
    const VENDOR_TYPE_DISTRIBUTOR = 'distributor';
    const VENDOR_TYPE_WHOLESALER = 'wholesaler';
    const VENDOR_TYPE_RETAILER = 'retailer';
    const VENDOR_TYPE_MAINTENANCE = 'maintenance';
    const VENDOR_TYPE_IT_SERVICES = 'it_services';
    const VENDOR_TYPE_FINANCIAL = 'financial';
    const VENDOR_TYPE_INSURANCE = 'insurance';
    const VENDOR_TYPE_LEGAL = 'legal';
    const VENDOR_TYPE_MARKETING = 'marketing';
    const VENDOR_TYPE_TRANSPORTATION = 'transportation';
    const VENDOR_TYPE_WASTE_MANAGEMENT = 'waste_management';
    const VENDOR_TYPE_SECURITY = 'security';
    const VENDOR_TYPE_CLEANING = 'cleaning';
    const VENDOR_TYPE_CATERING = 'catering';
    const VENDOR_TYPE_OTHER = 'other';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_PENDING = 'pending';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_TERMINATED = 'terminated';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const RISK_LEVEL_LOW = 'low';
    const RISK_LEVEL_MEDIUM = 'medium';
    const RISK_LEVEL_HIGH = 'high';
    const RISK_LEVEL_EXTREME = 'extreme';

    const COMPLIANCE_STATUS_COMPLIANT = 'compliant';
    const COMPLIANCE_STATUS_NON_COMPLIANT = 'non_compliant';
    const COMPLIANCE_STATUS_PENDING_REVIEW = 'pending_review';
    const COMPLIANCE_STATUS_UNDER_INVESTIGATION = 'under_investigation';
    const COMPLIANCE_STATUS_APPROVED = 'approved';
    const COMPLIANCE_STATUS_REJECTED = 'rejected';

    public static function getVendorTypes(): array
    {
        return [
            self::VENDOR_TYPE_SUPPLIER => 'Proveedor',
            self::VENDOR_TYPE_SERVICE_PROVIDER => 'Proveedor de Servicios',
            self::VENDOR_TYPE_CONTRACTOR => 'Contratista',
            self::VENDOR_TYPE_CONSULTANT => 'Consultor',
            self::VENDOR_TYPE_MANUFACTURER => 'Fabricante',
            self::VENDOR_TYPE_DISTRIBUTOR => 'Distribuidor',
            self::VENDOR_TYPE_WHOLESALER => 'Mayorista',
            self::VENDOR_TYPE_RETAILER => 'Minorista',
            self::VENDOR_TYPE_MAINTENANCE => 'Mantenimiento',
            self::VENDOR_TYPE_IT_SERVICES => 'Servicios IT',
            self::VENDOR_TYPE_FINANCIAL => 'Financiero',
            self::VENDOR_TYPE_INSURANCE => 'Seguros',
            self::VENDOR_TYPE_LEGAL => 'Legal',
            self::VENDOR_TYPE_MARKETING => 'Marketing',
            self::VENDOR_TYPE_TRANSPORTATION => 'Transporte',
            self::VENDOR_TYPE_WASTE_MANAGEMENT => 'Gestión de Residuos',
            self::VENDOR_TYPE_SECURITY => 'Seguridad',
            self::VENDOR_TYPE_CLEANING => 'Limpieza',
            self::VENDOR_TYPE_CATERING => 'Catering',
            self::VENDOR_TYPE_OTHER => 'Otro',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_INACTIVE => 'Inactivo',
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_SUSPENDED => 'Suspendido',
            self::STATUS_TERMINATED => 'Terminado',
            self::STATUS_UNDER_REVIEW => 'En Revisión',
            self::STATUS_APPROVED => 'Aprobado',
            self::STATUS_REJECTED => 'Rechazado',
        ];
    }

    public static function getRiskLevels(): array
    {
        return [
            self::RISK_LEVEL_LOW => 'Bajo',
            self::RISK_LEVEL_MEDIUM => 'Medio',
            self::RISK_LEVEL_HIGH => 'Alto',
            self::RISK_LEVEL_EXTREME => 'Extremo',
        ];
    }

    public static function getComplianceStatuses(): array
    {
        return [
            self::COMPLIANCE_STATUS_COMPLIANT => 'Cumple',
            self::COMPLIANCE_STATUS_NON_COMPLIANT => 'No Cumple',
            self::COMPLIANCE_STATUS_PENDING_REVIEW => 'Pendiente de Revisión',
            self::COMPLIANCE_STATUS_UNDER_INVESTIGATION => 'En Investigación',
            self::COMPLIANCE_STATUS_APPROVED => 'Aprobado',
            self::COMPLIANCE_STATUS_REJECTED => 'Rechazado',
        ];
    }

    // Relaciones
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function maintenanceTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class);
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopePreferred($query)
    {
        return $query->where('is_preferred', true);
    }

    public function scopeBlacklisted($query)
    {
        return $query->where('is_blacklisted', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('vendor_type', $type);
    }

    public function scopeByIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByRiskLevel($query, $riskLevel)
    {
        return $query->where('risk_level', $riskLevel);
    }

    public function scopeByComplianceStatus($query, $complianceStatus)
    {
        return $query->where('compliance_status', $complianceStatus);
    }

    public function scopeByRating($query, $minRating)
    {
        return $query->where('rating', '>=', $minRating);
    }

    public function scopeHighRating($query)
    {
        return $query->where('rating', '>=', 4.0);
    }

    public function scopeByLocation($query, $country = null, $state = null, $city = null)
    {
        if ($country) {
            $query->where('country', $country);
        }
        
        if ($state) {
            $query->where('state', $state);
        }
        
        if ($city) {
            $query->where('city', $city);
        }
        
        return $query;
    }

    public function scopeByContractStatus($query, $status = 'active')
    {
        if ($status === 'active') {
            return $query->where(function($q) {
                $q->whereNull('contract_end_date')
                  ->orWhere('contract_end_date', '>', now());
            });
        }
        
        if ($status === 'expired') {
            return $query->where('contract_end_date', '<', now());
        }
        
        if ($status === 'expiring_soon') {
            return $query->where('contract_end_date', '<=', now()->addDays(30))
                        ->where('contract_end_date', '>', now());
        }
        
        return $query;
    }

    public function scopeByPaymentTerms($query, $terms)
    {
        return $query->where('payment_terms', $terms);
    }

    public function scopeByCreditLimit($query, $minLimit, $maxLimit = null)
    {
        $query->where('credit_limit', '>=', $minLimit);
        
        if ($maxLimit) {
            $query->where('credit_limit', '<=', $maxLimit);
        }
        
        return $query;
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeHighRisk($query)
    {
        return $query->whereIn('risk_level', [self::RISK_LEVEL_HIGH, self::RISK_LEVEL_EXTREME]);
    }

    public function scopeCompliant($query)
    {
        return $query->where('compliance_status', self::COMPLIANCE_STATUS_COMPLIANT);
    }

    public function scopeNonCompliant($query)
    {
        return $query->where('compliance_status', self::COMPLIANCE_STATUS_NON_COMPLIANT);
    }

    public function scopeNeedsAudit($query)
    {
        return $query->where(function($q) {
            $q->whereNull('next_audit_date')
              ->orWhere('next_audit_date', '<=', now());
        });
    }

    // Métodos
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    public function isPreferred(): bool
    {
        return $this->is_preferred;
    }

    public function isBlacklisted(): bool
    {
        return $this->is_blacklisted;
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function isSupplier(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_SUPPLIER;
    }

    public function isServiceProvider(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_SERVICE_PROVIDER;
    }

    public function isContractor(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_CONTRACTOR;
    }

    public function isConsultant(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_CONSULTANT;
    }

    public function isManufacturer(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_MANUFACTURER;
    }

    public function isDistributor(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_DISTRIBUTOR;
    }

    public function isWholesaler(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_WHOLESALER;
    }

    public function isRetailer(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_RETAILER;
    }

    public function isMaintenance(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_MAINTENANCE;
    }

    public function isItServices(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_IT_SERVICES;
    }

    public function isFinancial(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_FINANCIAL;
    }

    public function isInsurance(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_INSURANCE;
    }

    public function isLegal(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_LEGAL;
    }

    public function isMarketing(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_MARKETING;
    }

    public function isTransportation(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_TRANSPORTATION;
    }

    public function isWasteManagement(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_WASTE_MANAGEMENT;
    }

    public function isSecurity(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_SECURITY;
    }

    public function isCleaning(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_CLEANING;
    }

    public function isCatering(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_CATERING;
    }

    public function isOther(): bool
    {
        return $this->vendor_type === self::VENDOR_TYPE_OTHER;
    }

    public function isLowRisk(): bool
    {
        return $this->risk_level === self::RISK_LEVEL_LOW;
    }

    public function isMediumRisk(): bool
    {
        return $this->risk_level === self::RISK_LEVEL_MEDIUM;
    }

    public function isHighRisk(): bool
    {
        return $this->risk_level === self::RISK_LEVEL_HIGH;
    }

    public function isExtremeRisk(): bool
    {
        return $this->risk_level === self::RISK_LEVEL_EXTREME;
    }

    public function isCompliant(): bool
    {
        return $this->compliance_status === self::COMPLIANCE_STATUS_COMPLIANT;
    }

    public function isNonCompliant(): bool
    {
        return $this->compliance_status === self::COMPLIANCE_STATUS_NON_COMPLIANT;
    }

    public function isPendingReview(): bool
    {
        return $this->compliance_status === self::COMPLIANCE_STATUS_PENDING_REVIEW;
    }

    public function isUnderInvestigation(): bool
    {
        return $this->compliance_status === self::COMPLIANCE_STATUS_UNDER_INVESTIGATION;
    }

    public function isComplianceApproved(): bool
    {
        return $this->compliance_status === self::COMPLIANCE_STATUS_APPROVED;
    }

    public function isComplianceRejected(): bool
    {
        return $this->compliance_status === self::COMPLIANCE_STATUS_REJECTED;
    }

    public function hasActiveContract(): bool
    {
        if (!$this->contract_start_date) {
            return false;
        }
        
        if (!$this->contract_end_date) {
            return true; // Sin fecha de fin, se considera activo
        }
        
        return now()->between($this->contract_start_date, $this->contract_end_date);
    }

    public function isContractExpiringSoon(int $days = 30): bool
    {
        if (!$this->contract_end_date) {
            return false;
        }
        
        return $this->contract_end_date->between(now(), now()->addDays($days));
    }

    public function isContractExpired(): bool
    {
        if (!$this->contract_end_date) {
            return false;
        }
        
        return $this->contract_end_date->isPast();
    }

    public function hasAvailableCredit(): bool
    {
        if (!$this->credit_limit) {
            return true;
        }
        
        return $this->current_balance < $this->credit_limit;
    }

    public function getAvailableCredit(): float
    {
        if (!$this->credit_limit) {
            return 0;
        }
        
        return max(0, $this->credit_limit - $this->current_balance);
    }

    public function getCreditUtilization(): float
    {
        if (!$this->credit_limit) {
            return 0;
        }
        
        return ($this->current_balance / $this->credit_limit) * 100;
    }

    public function getDaysUntilContractExpiry(): int
    {
        if (!$this->contract_end_date) {
            return 0;
        }
        
        if ($this->contract_end_date->isPast()) {
            return 0;
        }
        
        return now()->diffInDays($this->contract_end_date, false);
    }

    public function getDaysUntilNextAudit(): int
    {
        if (!$this->next_audit_date) {
            return 0;
        }
        
        if ($this->next_audit_date->isPast()) {
            return 0;
        }
        
        return now()->diffInDays($this->next_audit_date, false);
    }

    public function needsAudit(): bool
    {
        if (!$this->next_audit_date) {
            return true;
        }
        
        return $this->next_audit_date->isPast();
    }

    public function getFormattedRating(): string
    {
        if (!$this->rating) {
            return 'Sin calificación';
        }
        
        return number_format($this->rating, 1) . '/5.0';
    }

    public function getFormattedCreditLimit(): string
    {
        if (!$this->credit_limit) {
            return 'Sin límite';
        }
        
        return '$' . number_format($this->credit_limit, 2);
    }

    public function getFormattedCurrentBalance(): string
    {
        if (!$this->current_balance) {
            return '$0.00';
        }
        
        return '$' . number_format($this->current_balance, 2);
    }

    public function getFormattedAvailableCredit(): string
    {
        return '$' . number_format($this->getAvailableCredit(), 2);
    }

    public function getFormattedCreditUtilization(): string
    {
        return number_format($this->getCreditUtilization(), 1) . '%';
    }

    public function getFormattedTaxRate(): string
    {
        if (!$this->tax_rate) {
            return '0%';
        }
        
        return number_format($this->tax_rate, 2) . '%';
    }

    public function getFormattedDiscountRate(): string
    {
        if (!$this->discount_rate) {
            return '0%';
        }
        
        return number_format($this->discount_rate, 2) . '%';
    }

    public function getFormattedContractStartDate(): string
    {
        if (!$this->contract_start_date) {
            return 'No establecida';
        }
        
        return $this->contract_start_date->format('d/m/Y');
    }

    public function getFormattedContractEndDate(): string
    {
        if (!$this->contract_end_date) {
            return 'Sin fecha de fin';
        }
        
        return $this->contract_end_date->format('d/m/Y');
    }

    public function getFormattedLastAuditDate(): string
    {
        if (!$this->last_audit_date) {
            return 'Nunca auditado';
        }
        
        return $this->last_audit_date->format('d/m/Y');
    }

    public function getFormattedNextAuditDate(): string
    {
        if (!$this->next_audit_date) {
            return 'No programado';
        }
        
        return $this->next_audit_date->format('d/m/Y');
    }

    public function getFormattedVendorType(): string
    {
        return self::getVendorTypes()[$this->vendor_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedRiskLevel(): string
    {
        return self::getRiskLevels()[$this->risk_level] ?? 'Desconocido';
    }

    public function getFormattedComplianceStatus(): string
    {
        return self::getComplianceStatuses()[$this->compliance_status] ?? 'Desconocido';
    }

    public function getStatusBadgeClass(): string
    {
        if ($this->is_blacklisted) {
            return 'bg-red-100 text-red-800';
        }
        
        if (!$this->is_active) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if (!$this->is_verified) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        if ($this->is_preferred) {
            return 'bg-green-100 text-green-800';
        }
        
        return 'bg-blue-100 text-blue-800';
    }

    public function getVendorTypeBadgeClass(): string
    {
        return match($this->vendor_type) {
            self::VENDOR_TYPE_SUPPLIER => 'bg-blue-100 text-blue-800',
            self::VENDOR_TYPE_SERVICE_PROVIDER => 'bg-green-100 text-green-800',
            self::VENDOR_TYPE_CONTRACTOR => 'bg-yellow-100 text-yellow-800',
            self::VENDOR_TYPE_CONSULTANT => 'bg-purple-100 text-purple-800',
            self::VENDOR_TYPE_MANUFACTURER => 'bg-indigo-100 text-indigo-800',
            self::VENDOR_TYPE_DISTRIBUTOR => 'bg-pink-100 text-pink-800',
            self::VENDOR_TYPE_WHOLESALER => 'bg-cyan-100 text-cyan-800',
            self::VENDOR_TYPE_RETAILER => 'bg-teal-100 text-teal-800',
            self::VENDOR_TYPE_MAINTENANCE => 'bg-orange-100 text-orange-800',
            self::VENDOR_TYPE_IT_SERVICES => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getRiskLevelBadgeClass(): string
    {
        return match($this->risk_level) {
            self::RISK_LEVEL_LOW => 'bg-green-100 text-green-800',
            self::RISK_LEVEL_MEDIUM => 'bg-yellow-100 text-yellow-800',
            self::RISK_LEVEL_HIGH => 'bg-orange-100 text-orange-800',
            self::RISK_LEVEL_EXTREME => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getComplianceStatusBadgeClass(): string
    {
        return match($this->compliance_status) {
            self::COMPLIANCE_STATUS_COMPLIANT => 'bg-green-100 text-green-800',
            self::COMPLIANCE_STATUS_NON_COMPLIANT => 'bg-red-100 text-red-800',
            self::COMPLIANCE_STATUS_PENDING_REVIEW => 'bg-yellow-100 text-yellow-800',
            self::COMPLIANCE_STATUS_UNDER_INVESTIGATION => 'bg-orange-100 text-orange-800',
            self::COMPLIANCE_STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::COMPLIANCE_STATUS_REJECTED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getContractStatusBadgeClass(): string
    {
        if ($this->isContractExpired()) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->isContractExpiringSoon(7)) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->isContractExpiringSoon(30)) {
            return 'bg-orange-100 text-orange-800';
        }
        
        if ($this->hasActiveContract()) {
            return 'bg-green-100 text-green-800';
        }
        
        return 'bg-gray-100 text-gray-800';
    }

    public function getAuditStatusBadgeClass(): string
    {
        if ($this->needsAudit()) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->getDaysUntilNextAudit() <= 7) {
            return 'bg-orange-100 text-orange-800';
        }
        
        if ($this->getDaysUntilNextAudit() <= 30) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        return 'bg-green-100 text-green-800';
    }

    public function getCreditStatusBadgeClass(): string
    {
        if (!$this->credit_limit) {
            return 'bg-gray-100 text-gray-800';
        }
        
        $utilization = $this->getCreditUtilization();
        
        if ($utilization >= 90) {
            return 'bg-red-100 text-red-800';
        } elseif ($utilization >= 75) {
            return 'bg-orange-100 text-orange-800';
        } elseif ($utilization >= 50) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-green-100 text-green-800';
        }
    }
}
