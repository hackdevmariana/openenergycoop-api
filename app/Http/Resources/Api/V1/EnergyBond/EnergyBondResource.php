<?php

namespace App\Http\Resources\Api\V1\EnergyBond;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnergyBondResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'bond_type' => $this->bond_type,
            'bond_type_label' => $this->getBondTypeLabel(),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'priority' => $this->priority,
            'priority_label' => $this->getPriorityLabel(),
            'face_value' => $this->face_value,
            'formatted_face_value' => $this->getFormattedFaceValue(),
            'interest_rate' => $this->interest_rate,
            'formatted_interest_rate' => $this->getFormattedInterestRate(),
            'maturity_date' => $this->maturity_date?->toISOString(),
            'maturity_date_formatted' => $this->maturity_date?->format('d/m/Y'),
            'days_to_maturity' => $this->getDaysToMaturity(),
            'issue_date' => $this->issue_date?->toISOString(),
            'issue_date_formatted' => $this->issue_date?->format('d/m/Y'),
            'coupon_frequency' => $this->coupon_frequency,
            'coupon_frequency_label' => $this->getCouponFrequencyLabel(),
            'payment_method' => $this->payment_method,
            'payment_method_label' => $this->getPaymentMethodLabel(),
            'currency' => $this->currency,
            'total_units' => $this->total_units,
            'available_units' => $this->available_units,
            'sold_units' => $this->total_units - $this->available_units,
            'utilization_percentage' => $this->getUtilizationPercentage(),
            'minimum_investment' => $this->minimum_investment,
            'formatted_minimum_investment' => $this->getFormattedMinimumInvestment(),
            'maximum_investment' => $this->maximum_investment,
            'formatted_maximum_investment' => $this->getFormattedMaximumInvestment(),
            'early_redemption_allowed' => $this->early_redemption_allowed,
            'early_redemption_fee' => $this->early_redemption_fee,
            'transferable' => $this->transferable,
            'transfer_fee' => $this->transfer_fee,
            'collateral_required' => $this->collateral_required,
            'collateral_type' => $this->collateral_type,
            'collateral_value' => $this->collateral_value,
            'guarantee_provided' => $this->guarantee_provided,
            'guarantor_name' => $this->guarantor_name,
            'guarantee_amount' => $this->guarantee_amount,
            'risk_rating' => $this->risk_rating,
            'risk_rating_label' => $this->getRiskRatingLabel(),
            'credit_score_required' => $this->credit_score_required,
            'income_requirement' => $this->income_requirement,
            'employment_verification' => $this->employment_verification,
            'bank_statement_required' => $this->bank_statement_required,
            'tax_documentation_required' => $this->tax_documentation_required,
            'kyc_required' => $this->kyc_required,
            'aml_check_required' => $this->aml_check_required,
            'is_public' => $this->is_public,
            'is_featured' => $this->is_featured,
            'requires_approval' => $this->requires_approval,
            'is_template' => $this->is_template,
            'version' => $this->version,
            'sort_order' => $this->sort_order,
            'is_expired' => $this->isExpired(),
            'is_active' => $this->isActive(),
            'can_be_invested' => $this->canBeInvested(),
            'tags' => $this->tags,
            'notes' => $this->notes,
            'documents' => $this->documents,
            
            // Relationships
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                    'slug' => $this->organization->slug,
                ];
            }),
            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),
            'approved_by' => $this->whenLoaded('approvedBy', function () {
                return [
                    'id' => $this->approvedBy->id,
                    'name' => $this->approvedBy->name,
                    'email' => $this->approvedBy->email,
                ];
            }),
            'managed_by' => $this->whenLoaded('managedBy', function () {
                return [
                    'id' => $this->managedBy->id,
                    'name' => $this->managedBy->name,
                    'email' => $this->managedBy->email,
                ];
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'created_at_formatted' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->toISOString(),
            'updated_at_formatted' => $this->updated_at?->format('d/m/Y H:i'),
            'approved_at' => $this->approved_at?->toISOString(),
            'approved_at_formatted' => $this->approved_at?->format('d/m/Y H:i'),
            
            // Links
            'links' => [
                'self' => route('api.v1.energy-bonds.show', $this->id),
                'edit' => route('api.v1.energy-bonds.update', $this->id),
                'delete' => route('api.v1.energy-bonds.destroy', $this->id),
            ],
        ];
    }

    /**
     * Get bond type label
     */
    private function getBondTypeLabel(): string
    {
        return match($this->bond_type) {
            'solar' => 'Solar',
            'wind' => 'Eólico',
            'hydro' => 'Hidroeléctrico',
            'biomass' => 'Biomasa',
            'geothermal' => 'Geotérmico',
            'nuclear' => 'Nuclear',
            'hybrid' => 'Híbrido',
            'other' => 'Otro',
            default => $this->bond_type,
        };
    }

    /**
     * Get status label
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'draft' => 'Borrador',
            'pending' => 'Pendiente',
            'approved' => 'Aprobado',
            'active' => 'Activo',
            'inactive' => 'Inactivo',
            'expired' => 'Expirado',
            'cancelled' => 'Cancelado',
            'rejected' => 'Rechazado',
            default => $this->status,
        };
    }

    /**
     * Get priority label
     */
    private function getPriorityLabel(): string
    {
        return match($this->priority) {
            'low' => 'Baja',
            'medium' => 'Media',
            'high' => 'Alta',
            'urgent' => 'Urgente',
            'critical' => 'Crítica',
            default => $this->priority,
        };
    }

    /**
     * Get coupon frequency label
     */
    private function getCouponFrequencyLabel(): string
    {
        return match($this->coupon_frequency) {
            'monthly' => 'Mensual',
            'quarterly' => 'Trimestral',
            'semi_annually' => 'Semestral',
            'annually' => 'Anual',
            'at_maturity' => 'Al Vencimiento',
            default => $this->coupon_frequency,
        };
    }

    /**
     * Get payment method label
     */
    private function getPaymentMethodLabel(): string
    {
        return match($this->payment_method) {
            'bank_transfer' => 'Transferencia Bancaria',
            'credit_card' => 'Tarjeta de Crédito',
            'crypto' => 'Criptomonedas',
            'check' => 'Cheque',
            'cash' => 'Efectivo',
            'other' => 'Otro',
            default => $this->payment_method,
        };
    }

    /**
     * Get risk rating label
     */
    private function getRiskRatingLabel(): string
    {
        return match($this->risk_rating) {
            'aaa' => 'AAA - Riesgo Muy Bajo',
            'aa' => 'AA - Riesgo Bajo',
            'a' => 'A - Riesgo Bajo-Medio',
            'bbb' => 'BBB - Riesgo Medio',
            'bb' => 'BB - Riesgo Medio-Alto',
            'b' => 'B - Riesgo Alto',
            'ccc' => 'CCC - Riesgo Muy Alto',
            'cc' => 'CC - Riesgo Extremadamente Alto',
            'c' => 'C - Riesgo Extremadamente Alto',
            'd' => 'D - En Default',
            default => $this->risk_rating,
        };
    }

    /**
     * Get formatted face value
     */
    private function getFormattedFaceValue(): string
    {
        return number_format($this->face_value, 2) . ' ' . $this->currency;
    }

    /**
     * Get formatted interest rate
     */
    private function getFormattedInterestRate(): string
    {
        return number_format($this->interest_rate, 2) . '%';
    }

    /**
     * Get formatted minimum investment
     */
    private function getFormattedMinimumInvestment(): string
    {
        return number_format($this->minimum_investment, 2) . ' ' . $this->currency;
    }

    /**
     * Get formatted maximum investment
     */
    private function getFormattedMaximumInvestment(): ?string
    {
        if (!$this->maximum_investment) {
            return null;
        }
        return number_format($this->maximum_investment, 2) . ' ' . $this->currency;
    }

    /**
     * Get days to maturity
     */
    private function getDaysToMaturity(): ?int
    {
        if (!$this->maturity_date) {
            return null;
        }
        return now()->diffInDays($this->maturity_date, false);
    }

    /**
     * Get utilization percentage
     */
    private function getUtilizationPercentage(): float
    {
        if ($this->total_units === 0) {
            return 0;
        }
        return round((($this->total_units - $this->available_units) / $this->total_units) * 100, 2);
    }

    /**
     * Check if bond is expired
     */
    private function isExpired(): bool
    {
        if (!$this->maturity_date) {
            return false;
        }
        return now()->isAfter($this->maturity_date);
    }

    /**
     * Check if bond is active
     */
    private function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    /**
     * Check if bond can be invested
     */
    private function canBeInvested(): bool
    {
        return $this->status === 'active' 
            && !$this->isExpired() 
            && $this->available_units > 0;
    }
}
