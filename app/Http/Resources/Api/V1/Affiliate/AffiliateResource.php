<?php

namespace App\Http\Resources\Api\V1\Affiliate;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AffiliateResource extends JsonResource
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
            'email' => $this->email,
            'company_name' => $this->company_name,
            'website' => $this->website,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'description' => $this->description,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'commission_rate' => $this->commission_rate,
            'formatted_commission_rate' => $this->commission_rate ? $this->commission_rate . '%' : null,
            'payment_terms' => $this->payment_terms,
            'contract_start_date' => $this->contract_start_date?->toISOString(),
            'contract_end_date' => $this->contract_end_date?->toISOString(),
            'is_verified' => $this->is_verified,
            'verified_at' => $this->verified_at?->toISOString(),
            'verification_notes' => $this->verification_notes,
            'performance_rating' => $this->performance_rating,
            'performance_rating_stars' => $this->performance_rating ? str_repeat('⭐', $this->performance_rating) : null,
            'rating_notes' => $this->rating_notes,
            'rating_updated_at' => $this->rating_updated_at?->toISOString(),
            'rate_change_reason' => $this->rate_change_reason,
            'rate_updated_at' => $this->rate_updated_at?->toISOString(),
            'notes' => $this->notes,
            'internal_notes' => $this->internal_notes,
            'tags' => $this->tags,
            'social_media' => $this->social_media,
            'banking_info' => $this->banking_info,
            'tax_info' => $this->tax_info,
            'attachments' => $this->attachments,
            
            // Campos calculados
            'days_until_contract_end' => $this->contract_end_date ? now()->diffInDays($this->contract_end_date, false) : null,
            'is_contract_expiring_soon' => $this->contract_end_date ? now()->diffInDays($this->contract_end_date, false) <= 30 : false,
            'is_contract_expired' => $this->contract_end_date ? now()->isAfter($this->contract_end_date) : false,
            'contract_status' => $this->getContractStatus(),
            'performance_level' => $this->getPerformanceLevel(),
            'verification_status' => $this->getVerificationStatus(),
            
            // Relaciones
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                    'slug' => $this->organization->slug,
                ];
            }),
            
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            
            // Metadatos
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Enlaces HATEOAS
            'links' => [
                'self' => route('api.v1.affiliates.show', $this->id),
                'edit' => route('api.v1.affiliates.update', $this->id),
                'delete' => route('api.v1.affiliates.destroy', $this->id),
            ],
        ];
    }

    /**
     * Get the type label.
     */
    private function getTypeLabel(): string
    {
        return match($this->type) {
            'partner' => 'Socio',
            'reseller' => 'Revendedor',
            'distributor' => 'Distribuidor',
            'consultant' => 'Consultor',
            'other' => 'Otro',
            default => 'Desconocido'
        };
    }

    /**
     * Get the status label.
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'active' => 'Activo',
            'inactive' => 'Inactivo',
            'pending' => 'Pendiente',
            'suspended' => 'Suspendido',
            'terminated' => 'Terminado',
            default => 'Desconocido'
        };
    }

    /**
     * Get the contract status.
     */
    private function getContractStatus(): string
    {
        if (!$this->contract_end_date) {
            return 'Sin contrato';
        }
        
        if (now()->isAfter($this->contract_end_date)) {
            return 'Expirado';
        }
        
        $daysLeft = now()->diffInDays($this->contract_end_date, false);
        
        if ($daysLeft <= 30) {
            return 'Expirando pronto';
        }
        
        if ($daysLeft <= 90) {
            return 'Expira en 3 meses';
        }
        
        return 'Vigente';
    }

    /**
     * Get the performance level.
     */
    private function getPerformanceLevel(): string
    {
        if (!$this->performance_rating) {
            return 'Sin calificación';
        }
        
        return match($this->performance_rating) {
            1 => 'Muy bajo',
            2 => 'Bajo',
            3 => 'Promedio',
            4 => 'Alto',
            5 => 'Excelente',
            default => 'Desconocido'
        };
    }

    /**
     * Get the verification status.
     */
    private function getVerificationStatus(): string
    {
        if (!$this->is_verified) {
            return 'No verificado';
        }
        
        if ($this->verified_at) {
            return 'Verificado el ' . $this->verified_at->format('d/m/Y');
        }
        
        return 'Verificado';
    }
}
