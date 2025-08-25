<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class BondDonation extends Model
{
    use HasFactory;

    protected $fillable = [
        'donation_number',
        'donor_id',
        'energy_bond_id',
        'donation_type',
        'status',
        'donation_amount',
        'bond_units',
        'unit_price_at_donation',
        'total_value_at_donation',
        'current_value',
        'donation_date',
        'approval_date',
        'completion_date',
        'expiry_date',
        'donation_purpose',
        'impact_description',
        'recipient_organization',
        'recipient_beneficiaries',
        'project_description',
        'project_status',
        'project_budget',
        'project_spent',
        'project_start_date',
        'project_end_date',
        'project_milestones',
        'project_outcomes',
        'project_challenges',
        'project_lessons_learned',
        'is_anonymous',
        'is_recurring',
        'recurrence_frequency',
        'next_recurrence_date',
        'recurrence_count',
        'max_recurrences',
        'is_matched',
        'matching_ratio',
        'matching_amount',
        'matching_organization',
        'matching_terms',
        'is_tax_deductible',
        'tax_deduction_reference',
        'tax_deduction_amount',
        'tax_deduction_notes',
        'donor_preferences',
        'communication_preferences',
        'reporting_preferences',
        'recognition_preferences',
        'special_instructions',
        'internal_notes',
        'tags',
        'created_by',
        'approved_by',
        'processed_by',
    ];

    protected $casts = [
        'donation_date' => 'date',
        'approval_date' => 'date',
        'completion_date' => 'date',
        'expiry_date' => 'date',
        'project_start_date' => 'date',
        'project_end_date' => 'date',
        'next_recurrence_date' => 'date',
        'donation_amount' => 'decimal:2',
        'unit_price_at_donation' => 'decimal:2',
        'total_value_at_donation' => 'decimal:2',
        'current_value' => 'decimal:2',
        'project_budget' => 'decimal:2',
        'project_spent' => 'decimal:2',
        'matching_ratio' => 'decimal:2',
        'matching_amount' => 'decimal:2',
        'tax_deduction_amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
        'is_recurring' => 'boolean',
        'is_matched' => 'boolean',
        'is_tax_deductible' => 'boolean',
        'follow_up_required' => 'boolean',
        'donor_preferences' => 'array',
        'communication_preferences' => 'array',
        'reporting_preferences' => 'array',
        'recognition_preferences' => 'array',
        'tags' => 'array',
    ];

    // Enums
    const DONATION_TYPE_CHARITABLE = 'charitable';
    const DONATION_TYPE_EDUCATIONAL = 'educational';
    const DONATION_TYPE_ENVIRONMENTAL = 'environmental';
    const DONATION_TYPE_COMMUNITY = 'community';
    const DONATION_TYPE_RESEARCH = 'research';
    const DONATION_TYPE_OTHER = 'other';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_EXPIRED = 'expired';

    const PROJECT_STATUS_NOT_STARTED = 'not_started';
    const PROJECT_STATUS_IN_PROGRESS = 'in_progress';
    const PROJECT_STATUS_COMPLETED = 'completed';
    const PROJECT_STATUS_ON_HOLD = 'on_hold';
    const PROJECT_STATUS_CANCELLED = 'cancelled';

    const RECURRENCE_FREQUENCY_MONTHLY = 'monthly';
    const RECURRENCE_FREQUENCY_QUARTERLY = 'quarterly';
    const RECURRENCE_FREQUENCY_SEMI_ANNUALLY = 'semi_annually';
    const RECURRENCE_FREQUENCY_ANNUALLY = 'annually';

    public static function getDonationTypes(): array
    {
        return [
            self::DONATION_TYPE_CHARITABLE => 'Caritativa',
            self::DONATION_TYPE_EDUCATIONAL => 'Educativa',
            self::DONATION_TYPE_ENVIRONMENTAL => 'Ambiental',
            self::DONATION_TYPE_COMMUNITY => 'Comunitaria',
            self::DONATION_TYPE_RESEARCH => 'Investigación',
            self::DONATION_TYPE_OTHER => 'Otra',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_APPROVED => 'Aprobada',
            self::STATUS_REJECTED => 'Rechazada',
            self::STATUS_CANCELLED => 'Cancelada',
            self::STATUS_COMPLETED => 'Completada',
            self::STATUS_EXPIRED => 'Expirada',
        ];
    }

    public static function getProjectStatuses(): array
    {
        return [
            self::PROJECT_STATUS_NOT_STARTED => 'No Iniciado',
            self::PROJECT_STATUS_IN_PROGRESS => 'En Progreso',
            self::PROJECT_STATUS_COMPLETED => 'Completado',
            self::PROJECT_STATUS_ON_HOLD => 'En Espera',
            self::PROJECT_STATUS_CANCELLED => 'Cancelado',
        ];
    }

    public static function getRecurrenceFrequencies(): array
    {
        return [
            self::RECURRENCE_FREQUENCY_MONTHLY => 'Mensual',
            self::RECURRENCE_FREQUENCY_QUARTERLY => 'Trimestral',
            self::RECURRENCE_FREQUENCY_SEMI_ANNUALLY => 'Semestral',
            self::RECURRENCE_FREQUENCY_ANNUALLY => 'Anual',
        ];
    }

    // Relaciones
    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function energyBond(): BelongsTo
    {
        return $this->belongsTo(EnergyBond::class, 'energy_bond_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('donation_date', '>=', now()->subDays($days));
    }

    public function scopeByAmount($query, $minAmount = 0)
    {
        return $query->where('donation_amount', '>=', $minAmount);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('donation_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeAnonymous($query)
    {
        return $query->where('is_anonymous', true);
    }

    public function scopeTaxDeductible($query)
    {
        return $query->where('is_tax_deductible', true);
    }

    public function scopeMatched($query)
    {
        return $query->where('is_matched', true);
    }

    public function scopeByProjectStatus($query, $projectStatus)
    {
        return $query->where('project_status', $projectStatus);
    }

    // Métodos de validación
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function isAnonymous(): bool
    {
        return $this->is_anonymous;
    }

    public function isRecurring(): bool
    {
        return $this->is_recurring;
    }

    public function isMatched(): bool
    {
        return $this->is_matched;
    }

    public function isTaxDeductible(): bool
    {
        return $this->is_tax_deductible;
    }

    // Métodos de cálculo
    public function getMatchingAmount(): float
    {
        if (!$this->is_matched || !$this->matching_ratio) {
            return 0;
        }
        
        return $this->donation_amount * ($this->matching_ratio / 100);
    }

    public function getTotalValue(): float
    {
        return $this->donation_amount + $this->getMatchingAmount();
    }

    public function getProjectProgress(): float
    {
        if (!$this->project_budget || $this->project_budget == 0) {
            return 0;
        }
        
        return ($this->project_spent / $this->project_budget) * 100;
    }

    public function getDaysUntilExpiry(): int
    {
        if (!$this->expiry_date) {
            return 0;
        }
        
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getDaysSinceDonation(): int
    {
        if (!$this->donation_date) {
            return 0;
        }
        
        return now()->diffInDays($this->donation_date);
    }

    // Métodos de formato
    public function getFormattedDonationAmount(): string
    {
        return '$' . number_format($this->donation_amount, 2);
    }

    public function getFormattedCurrentValue(): string
    {
        return '$' . number_format($this->current_value, 2);
    }

    public function getFormattedTotalValue(): string
    {
        return '$' . number_format($this->getTotalValue(), 2);
    }

    public function getFormattedMatchingAmount(): string
    {
        return '$' . number_format($this->getMatchingAmount(), 2);
    }

    public function getFormattedDonationDate(): string
    {
        return $this->donation_date ? $this->donation_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedApprovalDate(): string
    {
        return $this->approval_date ? $this->approval_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedCompletionDate(): string
    {
        return $this->completion_date ? $this->completion_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedExpiryDate(): string
    {
        return $this->expiry_date ? $this->expiry_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedDonationType(): string
    {
        return self::getDonationTypes()[$this->donation_type] ?? 'Desconocido';
    }

    public function getFormattedStatus(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getFormattedProjectStatus(): string
    {
        return $this->project_status ? self::getProjectStatuses()[$this->project_status] ?? 'Desconocido' : 'N/A';
    }

    public function getFormattedRecurrenceFrequency(): string
    {
        return $this->recurrence_frequency ? self::getRecurrenceFrequencies()[$this->recurrence_frequency] ?? 'Desconocido' : 'N/A';
    }

    public function getDonorName(): string
    {
        if ($this->is_anonymous) {
            return 'Anónimo';
        }
        
        return $this->donor->name ?? 'Usuario';
    }

    // Clases de badges para Filament
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_CANCELLED => 'gray',
            self::STATUS_COMPLETED => 'info',
            self::STATUS_EXPIRED => 'danger',
            default => 'gray',
        };
    }

    public function getDonationTypeBadgeClass(): string
    {
        return match ($this->donation_type) {
            self::DONATION_TYPE_CHARITABLE => 'success',
            self::DONATION_TYPE_EDUCATIONAL => 'info',
            self::DONATION_TYPE_ENVIRONMENTAL => 'warning',
            self::DONATION_TYPE_COMMUNITY => 'primary',
            self::DONATION_TYPE_RESEARCH => 'purple',
            self::DONATION_TYPE_OTHER => 'gray',
            default => 'gray',
        };
    }

    public function getProjectStatusBadgeClass(): string
    {
        if (!$this->project_status) {
            return 'gray';
        }
        
        return match ($this->project_status) {
            self::PROJECT_STATUS_NOT_STARTED => 'gray',
            self::PROJECT_STATUS_IN_PROGRESS => 'warning',
            self::PROJECT_STATUS_COMPLETED => 'success',
            self::PROJECT_STATUS_ON_HOLD => 'info',
            self::PROJECT_STATUS_CANCELLED => 'danger',
            default => 'gray',
        };
    }
}
