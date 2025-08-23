<?php

namespace App\Http\Resources\Api\V1\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class VendorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        $canViewFinancial = $user && ($user->hasRole('admin') || $user->hasRole('finance_manager'));
        $canViewAudit = $user && ($user->hasRole('admin') || $user->hasRole('compliance_manager'));
        $canViewSensitive = $user && $user->hasRole('admin');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'legal_name' => $this->legal_name,
            'tax_id' => $this->when($canViewSensitive, $this->tax_id),
            'registration_number' => $this->when($canViewSensitive, $this->registration_number),
            'vendor_type' => $this->vendor_type,
            'vendor_type_label' => $this->getVendorTypeLabel(),
            'industry' => $this->industry,
            'description' => $this->description,
            'contact_person' => $this->contact_person,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'payment_terms' => $this->payment_terms,
            'credit_limit' => $this->when($canViewFinancial, $this->credit_limit),
            'current_balance' => $this->when($canViewFinancial, $this->current_balance),
            'available_credit' => $this->when($canViewFinancial, $this->getAvailableCredit()),
            'credit_utilization' => $this->when($canViewFinancial, $this->getCreditUtilization()),
            'currency' => $this->currency,
            'tax_rate' => $this->tax_rate,
            'discount_rate' => $this->discount_rate,
            'rating' => $this->rating,
            'rating_stars' => $this->getRatingStars(),
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
            'is_preferred' => $this->is_preferred,
            'is_blacklisted' => $this->is_blacklisted,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'status_badge_class' => $this->getStatusBadgeClass(),
            'risk_level' => $this->risk_level,
            'risk_level_label' => $this->getRiskLevelLabel(),
            'risk_level_badge_class' => $this->getRiskLevelBadgeClass(),
            'compliance_status' => $this->compliance_status,
            'compliance_status_label' => $this->getComplianceStatusLabel(),
            'compliance_status_badge_class' => $this->getComplianceStatusBadgeClass(),
            'contract_start_date' => $this->contract_start_date?->format('Y-m-d'),
            'contract_end_date' => $this->contract_end_date?->format('Y-m-d'),
            'contract_days_remaining' => $this->getContractDaysRemaining(),
            'contract_status' => $this->getContractStatus(),
            'contract_terms' => $this->contract_terms,
            'insurance_coverage' => $this->insurance_coverage,
            'certifications' => $this->certifications,
            'licenses' => $this->licenses,
            'performance_metrics' => $this->performance_metrics,
            'quality_standards' => $this->quality_standards,
            'delivery_terms' => $this->delivery_terms,
            'warranty_terms' => $this->warranty_terms,
            'return_policy' => $this->return_policy,
            'notes' => $this->notes,
            'tags' => $this->tags,
            'logo' => $this->logo,
            'documents' => $this->when($canViewSensitive, $this->documents),
            'bank_account' => $this->when($canViewFinancial, $this->bank_account),
            'payment_methods' => $this->when($canViewFinancial, $this->payment_methods),
            'contact_history' => $this->when($canViewSensitive, $this->contact_history),
            'audit_frequency' => $this->when($canViewAudit, $this->audit_frequency),
            'last_audit_date' => $this->when($canViewAudit, $this->last_audit_date?->format('Y-m-d')),
            'next_audit_date' => $this->when($canViewAudit, $this->next_audit_date?->format('Y-m-d')),
            'days_until_audit' => $this->when($canViewAudit, $this->getDaysUntilAudit()),
            'audit_status' => $this->when($canViewAudit, $this->getAuditStatus()),
            'financial_stability' => $this->when($canViewFinancial, $this->financial_stability),
            'market_reputation' => $this->when($canViewSensitive, $this->market_reputation),
            'competitor_analysis' => $this->when($canViewSensitive, $this->competitor_analysis),
            'strategic_importance' => $this->when($canViewSensitive, $this->strategic_importance),
            'dependencies' => $this->when($canViewSensitive, $this->dependencies),
            'alternatives' => $this->when($canViewSensitive, $this->alternatives),
            'cost_benefit_analysis' => $this->when($canViewFinancial, $this->cost_benefit_analysis),
            'performance_reviews' => $this->when($canViewSensitive, $this->performance_reviews),
            'improvement_plans' => $this->when($canViewSensitive, $this->improvement_plans),
            'escalation_procedures' => $this->when($canViewSensitive, $this->escalation_procedures),
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
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'last_contact_date' => $this->getLastContactDate(),
            'total_orders' => $this->when($canViewFinancial, $this->getTotalOrders()),
            'total_spent' => $this->when($canViewFinancial, $this->getTotalSpent()),
            'average_order_value' => $this->when($canViewFinancial, $this->getAverageOrderValue()),
            'on_time_delivery_rate' => $this->getOnTimeDeliveryRate(),
            'quality_rating' => $this->getQualityRating(),
            'response_time_hours' => $this->getResponseTimeHours(),
            'compliance_score' => $this->when($canViewAudit, $this->getComplianceScore()),
            'risk_score' => $this->when($canViewAudit, $this->getRiskScore()),
            'strategic_value_score' => $this->when($canViewSensitive, $this->getStrategicValueScore()),
            'can_edit' => $user && ($user->hasRole('admin') || $user->hasRole('vendor_manager')),
            'can_delete' => $user && $user->hasRole('admin'),
            'can_approve' => $user && ($user->hasRole('admin') || $user->hasRole('vendor_manager')),
            'can_verify' => $user && ($user->hasRole('admin') || $user->hasRole('vendor_manager')),
            'can_blacklist' => $user && ($user->hasRole('admin') || $user->hasRole('compliance_manager')),
        ];
    }

    /**
     * Obtener el crédito disponible
     */
    private function getAvailableCredit(): ?float
    {
        if ($this->credit_limit === null) {
            return null;
        }
        return max(0, $this->credit_limit - ($this->current_balance ?? 0));
    }

    /**
     * Obtener la utilización del crédito
     */
    private function getCreditUtilization(): ?float
    {
        if ($this->credit_limit === null || $this->credit_limit == 0) {
            return null;
        }
        return round((($this->current_balance ?? 0) / $this->credit_limit) * 100, 2);
    }

    /**
     * Obtener las estrellas de calificación
     */
    private function getRatingStars(): string
    {
        if ($this->rating === null) {
            return 'Sin calificar';
        }
        
        $stars = str_repeat('★', floor($this->rating));
        $halfStar = $this->rating - floor($this->rating) >= 0.5 ? '☆' : '';
        $emptyStars = str_repeat('☆', 5 - floor($this->rating) - ($halfStar ? 1 : 0));
        
        return $stars . $halfStar . $emptyStars . ' (' . number_format($this->rating, 1) . ')';
    }

    /**
     * Obtener el estado del contrato
     */
    private function getContractStatus(): string
    {
        if (!$this->contract_start_date || !$this->contract_end_date) {
            return 'Sin contrato';
        }

        $now = Carbon::now();
        $startDate = Carbon::parse($this->contract_start_date);
        $endDate = Carbon::parse($this->contract_end_date);

        if ($now->lt($startDate)) {
            return 'Pendiente';
        } elseif ($now->between($startDate, $endDate)) {
            return 'Activo';
        } else {
            return 'Expirado';
        }
    }

    /**
     * Obtener días restantes del contrato
     */
    private function getContractDaysRemaining(): ?int
    {
        if (!$this->contract_end_date) {
            return null;
        }

        $endDate = Carbon::parse($this->contract_end_date);
        $now = Carbon::now();
        
        if ($now->gt($endDate)) {
            return 0;
        }
        
        return $now->diffInDays($endDate, false);
    }

    /**
     * Obtener el estado de auditoría
     */
    private function getAuditStatus(): string
    {
        if (!$this->next_audit_date) {
            return 'Sin programar';
        }

        $nextAudit = Carbon::parse($this->next_audit_date);
        $now = Carbon::now();
        $daysUntil = $now->diffInDays($nextAudit, false);

        if ($daysUntil < 0) {
            return 'Vencida';
        } elseif ($daysUntil <= 30) {
            return 'Próxima';
        } else {
            return 'Programada';
        }
    }

    /**
     * Obtener días hasta la próxima auditoría
     */
    private function getDaysUntilAudit(): ?int
    {
        if (!$this->next_audit_date) {
            return null;
        }

        $nextAudit = Carbon::parse($this->next_audit_date);
        $now = Carbon::now();
        
        return $now->diffInDays($nextAudit, false);
    }

    /**
     * Obtener la fecha del último contacto
     */
    private function getLastContactDate(): ?string
    {
        if (!$this->contact_history || !is_array($this->contact_history)) {
            return null;
        }

        $dates = array_column($this->contact_history, 'date');
        if (empty($dates)) {
            return null;
        }

        $latestDate = max(array_map('strtotime', $dates));
        return date('Y-m-d', $latestDate);
    }

    /**
     * Obtener el total de pedidos
     */
    private function getTotalOrders(): int
    {
        // Implementar lógica para contar pedidos del proveedor
        return 0;
    }

    /**
     * Obtener el total gastado
     */
    private function getTotalSpent(): float
    {
        // Implementar lógica para calcular total gastado
        return 0.0;
    }

    /**
     * Obtener el valor promedio de pedidos
     */
    private function getAverageOrderValue(): ?float
    {
        $totalOrders = $this->getTotalOrders();
        if ($totalOrders === 0) {
            return null;
        }
        return $this->getTotalSpent() / $totalOrders;
    }

    /**
     * Obtener la tasa de entrega a tiempo
     */
    private function getOnTimeDeliveryRate(): ?float
    {
        // Implementar lógica para calcular tasa de entrega a tiempo
        return null;
    }

    /**
     * Obtener la calificación de calidad
     */
    private function getQualityRating(): ?float
    {
        // Implementar lógica para calcular calificación de calidad
        return null;
    }

    /**
     * Obtener el tiempo de respuesta en horas
     */
    private function getResponseTimeHours(): ?float
    {
        // Implementar lógica para calcular tiempo de respuesta
        return null;
    }

    /**
     * Obtener el puntaje de cumplimiento
     */
    private function getComplianceScore(): ?float
    {
        // Implementar lógica para calcular puntaje de cumplimiento
        return null;
    }

    /**
     * Obtener el puntaje de riesgo
     */
    private function getRiskScore(): ?float
    {
        // Implementar lógica para calcular puntaje de riesgo
        return null;
    }

    /**
     * Obtener el puntaje de valor estratégico
     */
    private function getStrategicValueScore(): ?float
    {
        // Implementar lógica para calcular puntaje de valor estratégico
        return null;
    }
}
