<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class EnergyBond extends Model
{
    use HasFactory;

    protected $fillable = [
        'bond_number',
        'name',
        'description',
        'bond_type',
        'status',
        'face_value',
        'current_value',
        'interest_rate',
        'interest_frequency',
        'issue_date',
        'maturity_date',
        'first_interest_date',
        'last_interest_payment_date',
        'next_interest_payment_date',
        'total_interest_payments',
        'paid_interest_payments',
        'total_interest_paid',
        'outstanding_principal',
        'minimum_investment',
        'maximum_investment',
        'total_units_available',
        'units_issued',
        'units_reserved',
        'unit_price',
        'payment_schedule',
        'is_tax_free',
        'tax_rate',
        'is_guaranteed',
        'guarantor_name',
        'guarantee_terms',
        'is_collateralized',
        'collateral_description',
        'collateral_value',
        'risk_level',
        'credit_rating',
        'risk_disclosure',
        'is_public',
        'is_featured',
        'priority_order',
        'terms_conditions',
        'disclosure_documents',
        'legal_documents',
        'financial_reports',
        'performance_metrics',
        'tags',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'managed_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'maturity_date' => 'date',
        'first_interest_date' => 'date',
        'last_interest_payment_date' => 'date',
        'next_interest_payment_date' => 'date',
        'face_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_interest_paid' => 'decimal:2',
        'outstanding_principal' => 'decimal:2',
        'minimum_investment' => 'decimal:2',
        'maximum_investment' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'collateral_value' => 'decimal:2',
        'is_tax_free' => 'boolean',
        'is_guaranteed' => 'boolean',
        'is_collateralized' => 'boolean',
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
        'approved_at' => 'datetime',
        'terms_conditions' => 'array',
        'disclosure_documents' => 'array',
        'legal_documents' => 'array',
        'financial_reports' => 'array',
        'performance_metrics' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const BOND_TYPE_SOLAR = 'solar';
    const BOND_TYPE_WIND = 'wind';
    const BOND_TYPE_HYDRO = 'hydro';
    const BOND_TYPE_BIOMASS = 'biomass';
    const BOND_TYPE_GEOTHERMAL = 'geothermal';
    const BOND_TYPE_HYBRID = 'hybrid';
    const BOND_TYPE_OTHER = 'other';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REDEEMED = 'redeemed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PENDING_APPROVAL = 'pending_approval';

    const INTEREST_FREQUENCY_MONTHLY = 'monthly';
    const INTEREST_FREQUENCY_QUARTERLY = 'quarterly';
    const INTEREST_FREQUENCY_SEMI_ANNUALLY = 'semi_annually';
    const INTEREST_FREQUENCY_ANNUALLY = 'annually';

    const PAYMENT_SCHEDULE_MONTHLY = 'monthly';
    const PAYMENT_SCHEDULE_QUARTERLY = 'quarterly';
    const PAYMENT_SCHEDULE_SEMI_ANNUALLY = 'semi_annually';
    const PAYMENT_SCHEDULE_ANNUALLY = 'annually';
    const PAYMENT_SCHEDULE_AT_MATURITY = 'at_maturity';

    const RISK_LEVEL_LOW = 'low';
    const RISK_LEVEL_MEDIUM = 'medium';
    const RISK_LEVEL_HIGH = 'high';
    const RISK_LEVEL_VERY_HIGH = 'very_high';

    const CREDIT_RATING_AAA = 'aaa';
    const CREDIT_RATING_AA = 'aa';
    const CREDIT_RATING_A = 'a';
    const CREDIT_RATING_BBB = 'bbb';
    const CREDIT_RATING_BB = 'bb';
    const CREDIT_RATING_B = 'b';
    const CREDIT_RATING_CCC = 'ccc';
    const CREDIT_RATING_CC = 'cc';
    const CREDIT_RATING_C = 'c';
    const CREDIT_RATING_D = 'd';

    public static function getBondTypes(): array
    {
        return [
            self::BOND_TYPE_SOLAR => 'Solar',
            self::BOND_TYPE_WIND => 'Eólica',
            self::BOND_TYPE_HYDRO => 'Hidroeléctrica',
            self::BOND_TYPE_BIOMASS => 'Biomasa',
            self::BOND_TYPE_GEOTHERMAL => 'Geotérmica',
            self::BOND_TYPE_HYBRID => 'Híbrida',
            self::BOND_TYPE_OTHER => 'Otra',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_INACTIVE => 'Inactivo',
            self::STATUS_EXPIRED => 'Expirado',
            self::STATUS_REDEEMED => 'Redimido',
            self::STATUS_CANCELLED => 'Cancelado',
            self::STATUS_PENDING_APPROVAL => 'Pendiente de Aprobación',
        ];
    }

    public static function getInterestFrequencies(): array
    {
        return [
            self::INTEREST_FREQUENCY_MONTHLY => 'Mensual',
            self::INTEREST_FREQUENCY_QUARTERLY => 'Trimestral',
            self::INTEREST_FREQUENCY_SEMI_ANNUALLY => 'Semestral',
            self::INTEREST_FREQUENCY_ANNUALLY => 'Anual',
        ];
    }

    public static function getPaymentSchedules(): array
    {
        return [
            self::PAYMENT_SCHEDULE_MONTHLY => 'Mensual',
            self::PAYMENT_SCHEDULE_QUARTERLY => 'Trimestral',
            self::PAYMENT_SCHEDULE_SEMI_ANNUALLY => 'Semestral',
            self::PAYMENT_SCHEDULE_ANNUALLY => 'Anual',
            self::PAYMENT_SCHEDULE_AT_MATURITY => 'Al Vencimiento',
        ];
    }

    public static function getRiskLevels(): array
    {
        return [
            self::RISK_LEVEL_LOW => 'Bajo',
            self::RISK_LEVEL_MEDIUM => 'Medio',
            self::RISK_LEVEL_HIGH => 'Alto',
            self::RISK_LEVEL_VERY_HIGH => 'Muy Alto',
        ];
    }

    public static function getCreditRatings(): array
    {
        return [
            self::CREDIT_RATING_AAA => 'AAA',
            self::CREDIT_RATING_AA => 'AA',
            self::CREDIT_RATING_A => 'A',
            self::CREDIT_RATING_BBB => 'BBB',
            self::CREDIT_RATING_BB => 'BB',
            self::CREDIT_RATING_B => 'B',
            self::CREDIT_RATING_CCC => 'CCC',
            self::CREDIT_RATING_CC => 'CC',
            self::CREDIT_RATING_C => 'C',
            self::CREDIT_RATING_D => 'D',
        ];
    }

    // Relaciones
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function managedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    public function donations(): HasMany
    {
        return $this->hasMany(BondDonation::class, 'energy_bond_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(EnergyBondStatusLog::class, 'entity_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('bond_type', $type);
    }

    public function scopeByRiskLevel($query, $riskLevel)
    {
        return $query->where('risk_level', $riskLevel);
    }

    public function scopeByCreditRating($query, $creditRating)
    {
        return $query->where('credit_rating', $creditRating);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('maturity_date', '<=', now()->addDays($days))
                    ->where('status', self::STATUS_ACTIVE);
    }

    public function scopeHighYield($query, $minRate = 5.0)
    {
        return $query->where('interest_rate', '>=', $minRate);
    }

    // Métodos de validación
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function isRedeemed(): bool
    {
        return $this->status === self::STATUS_REDEEMED;
    }

    public function isPublic(): bool
    {
        return $this->is_public;
    }

    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    public function isTaxFree(): bool
    {
        return $this->is_tax_free;
    }

    public function isGuaranteed(): bool
    {
        return $this->is_guaranteed;
    }

    public function isCollateralized(): bool
    {
        return $this->is_collateralized;
    }

    // Métodos de cálculo
    public function getRemainingDays(): int
    {
        if (!$this->maturity_date) {
            return 0;
        }
        
        return now()->diffInDays($this->maturity_date, false);
    }

    public function getNextInterestPaymentDays(): int
    {
        if (!$this->next_interest_payment_date) {
            return 0;
        }
        
        return now()->diffInDays($this->next_interest_payment_date, false);
    }

    public function getAvailableUnits(): int
    {
        return $this->total_units_available - $this->units_issued - $this->units_reserved;
    }

    public function getUtilizationPercentage(): float
    {
        if ($this->total_units_available === 0) {
            return 0;
        }
        
        return (($this->units_issued + $this->units_reserved) / $this->total_units_available) * 100;
    }

    public function getTotalValue(): float
    {
        return $this->current_value * $this->total_units_available;
    }

    public function getIssuedValue(): float
    {
        return $this->current_value * $this->units_issued;
    }

    // Métodos de formato
    public function getFormattedFaceValue(): string
    {
        return '$' . number_format($this->face_value, 2);
    }

    public function getFormattedCurrentValue(): string
    {
        return '$' . number_format($this->current_value, 2);
    }

    public function getFormattedInterestRate(): string
    {
        return number_format($this->interest_rate, 2) . '%';
    }

    public function getFormattedIssueDate(): string
    {
        return $this->issue_date ? $this->issue_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedMaturityDate(): string
    {
        return $this->maturity_date ? $this->maturity_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedBondType(): string
    {
        return self::getBondTypes()[$this->bond_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedRiskLevel(): string
    {
        return self::getRiskLevels()[$this->risk_level] ?? 'Desconocido';
    }

    public function getFormattedCreditRating(): string
    {
        return self::getCreditRatings()[$this->credit_rating] ?? 'N/A';
    }

    // Clases de badges para Filament
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'gray',
            self::STATUS_EXPIRED => 'danger',
            self::STATUS_REDEEMED => 'info',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_PENDING_APPROVAL => 'warning',
            default => 'gray',
        };
    }

    public function getBondTypeBadgeClass(): string
    {
        return match ($this->bond_type) {
            self::BOND_TYPE_SOLAR => 'warning',
            self::BOND_TYPE_WIND => 'info',
            self::BOND_TYPE_HYDRO => 'primary',
            self::BOND_TYPE_BIOMASS => 'success',
            self::BOND_TYPE_GEOTHERMAL => 'danger',
            self::BOND_TYPE_HYBRID => 'purple',
            self::BOND_TYPE_OTHER => 'gray',
            default => 'gray',
        };
    }

    public function getRiskLevelBadgeClass(): string
    {
        return match ($this->risk_level) {
            self::RISK_LEVEL_LOW => 'success',
            self::RISK_LEVEL_MEDIUM => 'warning',
            self::RISK_LEVEL_HIGH => 'danger',
            self::RISK_LEVEL_VERY_HIGH => 'danger',
            default => 'gray',
        };
    }

    public function getCreditRatingBadgeClass(): string
    {
        return match ($this->credit_rating) {
            self::CREDIT_RATING_AAA, self::CREDIT_RATING_AA, self::CREDIT_RATING_A => 'success',
            self::CREDIT_RATING_BBB, self::CREDIT_RATING_BB, self::CREDIT_RATING_B => 'warning',
            self::CREDIT_RATING_CCC, self::CREDIT_RATING_CC, self::CREDIT_RATING_C => 'danger',
            self::CREDIT_RATING_D => 'danger',
            default => 'gray',
        };
    }
}
