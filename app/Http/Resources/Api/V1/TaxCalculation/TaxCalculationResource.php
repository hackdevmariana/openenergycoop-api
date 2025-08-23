<?php

namespace App\Http\Resources\Api\V1\TaxCalculation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxCalculationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'calculation_number' => $this->calculation_number,
            'name' => $this->name,
            'description' => $this->description,
            'tax_type' => $this->tax_type,
            'tax_type_label' => $this->getTaxTypeLabel(),
            'calculation_type' => $this->calculation_type,
            'calculation_type_label' => $this->getCalculationTypeLabel(),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'priority' => $this->priority,
            'priority_label' => $this->getPriorityLabel(),
            'entity_id' => $this->entity_id,
            'entity_type' => $this->entity_type,
            'entity' => $this->whenLoaded('entity'),
            'transaction_id' => $this->transaction_id,
            'transaction_type' => $this->transaction_type,
            'transaction' => $this->whenLoaded('transaction'),
            'tax_period_start' => $this->tax_period_start?->format('Y-m-d'),
            'tax_period_end' => $this->tax_period_end?->format('Y-m-d'),
            'calculation_date' => $this->calculation_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'payment_date' => $this->payment_date?->format('Y-m-d'),
            'taxable_amount' => $this->taxable_amount,
            'taxable_amount_formatted' => $this->getFormattedAmount($this->taxable_amount),
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'tax_amount_formatted' => $this->getFormattedAmount($this->tax_amount),
            'tax_base_amount' => $this->tax_base_amount,
            'tax_base_amount_formatted' => $this->getFormattedAmount($this->tax_base_amount),
            'exemption_amount' => $this->exemption_amount,
            'exemption_amount_formatted' => $this->getFormattedAmount($this->exemption_amount),
            'deduction_amount' => $this->deduction_amount,
            'deduction_amount_formatted' => $this->getFormattedAmount($this->deduction_amount),
            'credit_amount' => $this->credit_amount,
            'credit_amount_formatted' => $this->getFormattedAmount($this->credit_amount),
            'net_tax_amount' => $this->net_tax_amount,
            'net_tax_amount_formatted' => $this->getFormattedAmount($this->net_tax_amount),
            'penalty_amount' => $this->penalty_amount,
            'penalty_amount_formatted' => $this->getFormattedAmount($this->penalty_amount),
            'interest_amount' => $this->interest_amount,
            'interest_amount_formatted' => $this->getFormattedAmount($this->interest_amount),
            'total_amount_due' => $this->total_amount_due,
            'total_amount_due_formatted' => $this->getFormattedAmount($this->total_amount_due),
            'amount_paid' => $this->amount_paid,
            'amount_paid_formatted' => $this->getFormattedAmount($this->amount_paid),
            'amount_remaining' => $this->amount_remaining,
            'amount_remaining_formatted' => $this->getFormattedAmount($this->amount_remaining),
            'currency' => $this->currency,
            'exchange_rate' => $this->exchange_rate,
            'tax_jurisdiction' => $this->tax_jurisdiction,
            'tax_authority' => $this->tax_authority,
            'tax_registration_number' => $this->tax_registration_number,
            'tax_filing_frequency' => $this->tax_filing_frequency,
            'tax_filing_method' => $this->tax_filing_method,
            'is_estimated' => $this->is_estimated,
            'is_final' => $this->is_final,
            'is_amended' => $this->is_amended,
            'amendment_reason' => $this->amendment_reason,
            'calculation_notes' => $this->calculation_notes,
            'review_notes' => $this->review_notes,
            'approval_notes' => $this->approval_notes,
            'calculation_details' => $this->calculation_details,
            'tax_breakdown' => $this->tax_breakdown,
            'supporting_documents' => $this->supporting_documents,
            'audit_trail' => $this->audit_trail,
            'tags' => $this->tags,
            'notes' => $this->notes,
            
            // Campos calculados
            'days_overdue' => $this->getDaysOverdue(),
            'effective_tax_rate' => $this->getEffectiveTaxRate(),
            'total_amount_with_penalty_and_interest' => $this->getTotalAmountWithPenaltyAndInterest(),
            'payment_percentage' => $this->getPaymentPercentage(),
            'is_overdue' => $this->isOverdue(),
            'is_due_soon' => $this->isDueSoon(),
            'is_fully_paid' => $this->isFullyPaid(),
            'is_partially_paid' => $this->isPartiallyPaid(),
            'is_unpaid' => $this->isUnpaid(),
            
            // Campos de estado
            'is_draft' => $this->isDraft(),
            'is_calculated' => $this->isCalculated(),
            'is_reviewed' => $this->isReviewed(),
            'is_approved' => $this->isApproved(),
            'is_applied' => $this->isApplied(),
            'is_cancelled' => $this->isCancelled(),
            'is_error' => $this->isError(),
            'is_high_priority' => $this->isHighPriority(),
            
            // Campos de prioridad
            'is_low_priority' => $this->isLowPriority(),
            'is_medium_priority' => $this->isMediumPriority(),
            'is_critical_priority' => $this->isCriticalPriority(),
            
            // Campos de tipo
            'is_income_tax' => $this->isIncomeTax(),
            'is_sales_tax' => $this->isSalesTax(),
            'is_property_tax' => $this->isPropertyTax(),
            'is_excise_tax' => $this->isExciseTax(),
            'is_customs_duty' => $this->isCustomsDuty(),
            'is_other_tax' => $this->isOtherTax(),
            
            // Campos de cálculo
            'is_automatic_calculation' => $this->isAutomaticCalculation(),
            'is_manual_calculation' => $this->isManualCalculation(),
            'is_estimated_calculation' => $this->isEstimatedCalculation(),
            'is_final_calculation' => $this->isFinalCalculation(),
            
            // Campos de formato
            'tax_period_duration' => $this->getTaxPeriodDuration(),
            'calculation_age' => $this->getCalculationAge(),
            'due_date_formatted' => $this->getDueDateFormatted(),
            'payment_date_formatted' => $this->getPaymentDateFormatted(),
            
            // Campos de UI
            'status_badge_class' => $this->getStatusBadgeClass(),
            'priority_badge_class' => $this->getPriorityBadgeClass(),
            'tax_type_badge_class' => $this->getTaxTypeBadgeClass(),
            'calculation_type_badge_class' => $this->getCalculationTypeBadgeClass(),
            'overdue_badge_class' => $this->getOverdueBadgeClass(),
            
            // Relaciones
            'calculated_by' => $this->whenLoaded('calculatedBy'),
            'reviewed_by' => $this->whenLoaded('reviewedBy'),
            'approved_by' => $this->whenLoaded('approvedBy'),
            'applied_by' => $this->whenLoaded('appliedBy'),
            'created_by' => $this->whenLoaded('createdBy'),
            
            // Permisos
            'can_edit' => $this->canEdit(),
            'can_delete' => $this->canDelete(),
            'can_duplicate' => $this->canDuplicate(),
            'can_approve' => $this->canApprove(),
            'can_validate' => $this->canValidate(),
            'can_apply' => $this->canApply(),
            'can_cancel' => $this->canCancel(),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
    
    /**
     * Obtener la etiqueta del tipo de impuesto
     */
    protected function getTaxTypeLabel(): ?string
    {
        $types = $this->resource->getTaxTypes();
        return $types[$this->tax_type] ?? null;
    }
    
    /**
     * Obtener la etiqueta del tipo de cálculo
     */
    protected function getCalculationTypeLabel(): ?string
    {
        $types = $this->resource->getCalculationTypes();
        return $types[$this->calculation_type] ?? null;
    }
    
    /**
     * Obtener la etiqueta del estado
     */
    protected function getStatusLabel(): ?string
    {
        $statuses = $this->resource->getStatuses();
        return $statuses[$this->status] ?? null;
    }
    
    /**
     * Obtener la etiqueta de la prioridad
     */
    protected function getPriorityLabel(): ?string
    {
        $priorities = $this->resource->getPriorities();
        return $priorities[$this->priority] ?? null;
    }
    
    /**
     * Formatear monto monetario
     */
    protected function getFormattedAmount(?float $amount): ?string
    {
        if ($amount === null) {
            return null;
        }
        
        return number_format($amount, 2, ',', '.') . ' ' . ($this->currency ?? 'EUR');
    }
    
    /**
     * Obtener la duración del período fiscal
     */
    protected function getTaxPeriodDuration(): ?string
    {
        if (!$this->tax_period_start || !$this->tax_period_end) {
            return null;
        }
        
        $start = \Carbon\Carbon::parse($this->tax_period_start);
        $end = \Carbon\Carbon::parse($this->tax_period_end);
        $days = $start->diffInDays($end);
        
        if ($days === 0) {
            return '1 día';
        } elseif ($days === 1) {
            return '2 días';
        } elseif ($days < 30) {
            return $days . ' días';
        } elseif ($days < 365) {
            $months = round($days / 30);
            return $months . ' mes' . ($months > 1 ? 'es' : '');
        } else {
            $years = round($days / 365, 1);
            return $years . ' año' . ($years > 1 ? 's' : '');
        }
    }
    
    /**
     * Obtener la edad del cálculo
     */
    protected function getCalculationAge(): ?string
    {
        if (!$this->calculation_date) {
            return null;
        }
        
        $calculationDate = \Carbon\Carbon::parse($this->calculation_date);
        $now = \Carbon\Carbon::now();
        $days = $calculationDate->diffInDays($now);
        
        if ($days === 0) {
            return 'Hoy';
        } elseif ($days === 1) {
            return 'Ayer';
        } elseif ($days < 7) {
            return 'Hace ' . $days . ' días';
        } elseif ($days < 30) {
            $weeks = round($days / 7);
            return 'Hace ' . $weeks . ' semana' . ($weeks > 1 ? 's' : '');
        } elseif ($days < 365) {
            $months = round($days / 30);
            return 'Hace ' . $months . ' mes' . ($months > 1 ? 'es' : '');
        } else {
            $years = round($days / 365);
            return 'Hace ' . $years . ' año' . ($years > 1 ? 's' : '');
        }
    }
    
    /**
     * Obtener la fecha de vencimiento formateada
     */
    protected function getDueDateFormatted(): ?string
    {
        if (!$this->due_date) {
            return null;
        }
        
        $dueDate = \Carbon\Carbon::parse($this->due_date);
        $now = \Carbon\Carbon::now();
        
        if ($dueDate->isPast()) {
            $days = $dueDate->diffInDays($now);
            if ($days === 0) {
                return 'Vencido hoy';
            } elseif ($days === 1) {
                return 'Vencido ayer';
            } else {
                return 'Vencido hace ' . $days . ' días';
            }
        } elseif ($dueDate->isToday()) {
            return 'Vence hoy';
        } elseif ($dueDate->isTomorrow()) {
            return 'Vence mañana';
        } else {
            $days = $now->diffInDays($dueDate);
            if ($days < 7) {
                return 'Vence en ' . $days . ' días';
            } elseif ($days < 30) {
                $weeks = round($days / 7);
                return 'Vence en ' . $weeks . ' semana' . ($weeks > 1 ? 's' : '');
            } else {
                return 'Vence el ' . $dueDate->format('d/m/Y');
            }
        }
    }
    
    /**
     * Obtener la fecha de pago formateada
     */
    protected function getPaymentDateFormatted(): ?string
    {
        if (!$this->payment_date) {
            return null;
        }
        
        $paymentDate = \Carbon\Carbon::parse($this->payment_date);
        $now = \Carbon\Carbon::now();
        $days = $paymentDate->diffInDays($now);
        
        if ($days === 0) {
            return 'Pagado hoy';
        } elseif ($days === 1) {
            return 'Pagado ayer';
        } elseif ($days < 7) {
            return 'Pagado hace ' . $days . ' días';
        } elseif ($days < 30) {
            $weeks = round($days / 7);
            return 'Pagado hace ' . $weeks . ' semana' . ($weeks > 1 ? 's' : '');
        } else {
            return 'Pagado el ' . $paymentDate->format('d/m/Y');
        }
    }
    
    /**
     * Obtener la clase CSS del badge de estado
     */
    protected function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'calculated' => 'bg-blue-100 text-blue-800',
            'reviewed' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'applied' => 'bg-purple-100 text-purple-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'error' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
    
    /**
     * Obtener la clase CSS del badge de prioridad
     */
    protected function getPriorityBadgeClass(): string
    {
        return match($this->priority) {
            'low' => 'bg-green-100 text-green-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'high' => 'bg-orange-100 text-orange-800',
            'critical' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
    
    /**
     * Obtener la clase CSS del badge de tipo de impuesto
     */
    protected function getTaxTypeBadgeClass(): string
    {
        return match($this->tax_type) {
            'income_tax' => 'bg-blue-100 text-blue-800',
            'sales_tax' => 'bg-green-100 text-green-800',
            'property_tax' => 'bg-purple-100 text-purple-800',
            'excise_tax' => 'bg-orange-100 text-orange-800',
            'customs_duty' => 'bg-indigo-100 text-indigo-800',
            'other_tax' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
    
    /**
     * Obtener la clase CSS del badge de tipo de cálculo
     */
    protected function getCalculationTypeBadgeClass(): string
    {
        return match($this->calculation_type) {
            'automatic' => 'bg-green-100 text-green-800',
            'manual' => 'bg-blue-100 text-blue-800',
            'estimated' => 'bg-yellow-100 text-yellow-800',
            'final' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
    
    /**
     * Obtener la clase CSS del badge de vencimiento
     */
    protected function getOverdueBadgeClass(): string
    {
        if ($this->isOverdue()) {
            return 'bg-red-100 text-red-800';
        } elseif ($this->isDueSoon()) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-green-100 text-green-800';
        }
    }
    
    /**
     * Verificar si se puede editar
     */
    protected function canEdit(): bool
    {
        return !$this->isApplied() && !$this->isCancelled() && !$this->isError();
    }
    
    /**
     * Verificar si se puede eliminar
     */
    protected function canDelete(): bool
    {
        return $this->isDraft() || $this->isCancelled();
    }
    
    /**
     * Verificar si se puede duplicar
     */
    protected function canDuplicate(): bool
    {
        return true; // Siempre se puede duplicar
    }
    
    /**
     * Verificar si se puede aprobar
     */
    protected function canApprove(): bool
    {
        return $this->isReviewed() && !$this->isApproved() && !$this->isCancelled();
    }
    
    /**
     * Verificar si se puede validar
     */
    protected function canValidate(): bool
    {
        return $this->isCalculated() && !$this->isReviewed() && !$this->isCancelled();
    }
    
    /**
     * Verificar si se puede aplicar
     */
    protected function canApply(): bool
    {
        return $this->isApproved() && !$this->isApplied() && !$this->isCancelled();
    }
    
    /**
     * Verificar si se puede cancelar
     */
    protected function canCancel(): bool
    {
        return !$this->isApplied() && !$this->isCancelled();
    }
}
