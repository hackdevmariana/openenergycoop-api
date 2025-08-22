<?php

namespace App\Http\Resources\Api\V1\DiscountCode;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountCodeResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'value' => $this->value,
            'formatted_value' => $this->getFormattedValue(),
            'max_discount_amount' => $this->max_discount_amount,
            'min_order_amount' => $this->min_order_amount,
            'max_order_amount' => $this->max_order_amount,
            'usage_limit' => $this->usage_limit,
            'usage_limit_per_user' => $this->usage_limit_per_user,
            'usage_count' => $this->usage_count,
            'total_discount_amount' => $this->total_discount_amount,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'is_public' => $this->is_public,
            'is_first_time_only' => $this->is_first_time_only,
            'is_new_customer_only' => $this->is_new_customer_only,
            'is_returning_customer_only' => $this->is_returning_customer_only,
            'valid_from' => $this->valid_from?->toISOString(),
            'valid_until' => $this->valid_until?->toISOString(),
            'activated_at' => $this->activated_at?->toISOString(),
            'deactivated_at' => $this->deactivated_at?->toISOString(),
            'notes' => $this->notes,
            'tags' => $this->tags,
            'terms_conditions' => $this->terms_conditions,
            'restrictions' => $this->restrictions,
            'auto_apply' => $this->auto_apply,
            'priority' => $this->priority,
            'is_stackable' => $this->is_stackable,
            'max_stack_count' => $this->max_stack_count,
            'customer_groups' => $this->customer_groups,
            'geographic_restrictions' => $this->geographic_restrictions,
            'time_restrictions' => $this->time_restrictions,
            'applicable_products' => $this->applicable_products,
            'applicable_categories' => $this->applicable_categories,
            'excluded_products' => $this->excluded_products,
            'excluded_categories' => $this->excluded_categories,
            'attachments' => $this->attachments,
            
            // Campos calculados
            'days_until_expiry' => $this->valid_until ? now()->diffInDays($this->valid_until, false) : null,
            'is_expired' => $this->valid_until ? now()->isAfter($this->valid_until) : false,
            'is_expiring_soon' => $this->valid_until ? now()->diffInDays($this->valid_until, false) <= 7 : false,
            'is_active_now' => $this->isActiveNow(),
            'remaining_usage' => $this->usage_limit ? max(0, $this->usage_limit - $this->usage_count) : null,
            'usage_percentage' => $this->usage_limit ? round(($this->usage_count / $this->usage_limit) * 100, 1) : null,
            'effectiveness_score' => $this->getEffectivenessScore(),
            'discount_potential' => $this->getDiscountPotential(),
            
            // Relaciones
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
            
            // Metadatos
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Enlaces HATEOAS
            'links' => [
                'self' => route('api.v1.discount-codes.show', $this->id),
                'edit' => route('api.v1.discount-codes.update', $this->id),
                'delete' => route('api.v1.discount-codes.destroy', $this->id),
            ],
        ];
    }

    /**
     * Get the type label.
     */
    private function getTypeLabel(): string
    {
        return match($this->type) {
            'percentage' => 'Porcentaje',
            'fixed_amount' => 'Monto fijo',
            'free_shipping' => 'Envío gratis',
            'buy_one_get_one' => 'Compra uno, lleva otro',
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
            'expired' => 'Expirado',
            'suspended' => 'Suspendido',
            default => 'Desconocido'
        };
    }

    /**
     * Get formatted value.
     */
    private function getFormattedValue(): string
    {
        return match($this->type) {
            'percentage' => $this->value . '%',
            'fixed_amount' => '$' . number_format($this->value, 2),
            'free_shipping' => 'Envío gratis',
            'buy_one_get_one' => 'BOGO',
            default => (string) $this->value
        };
    }

    /**
     * Check if the discount code is active now.
     */
    private function isActiveNow(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->valid_from && now() < $this->valid_from) {
            return false;
        }

        if ($this->valid_until && now() > $this->valid_until) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Get effectiveness score (0-100).
     */
    private function getEffectivenessScore(): int
    {
        $score = 0;

        // Base score for being active
        if ($this->status === 'active') {
            $score += 30;
        }

        // Score for usage (more usage = higher score)
        if ($this->usage_limit && $this->usage_count > 0) {
            $usagePercentage = ($this->usage_count / $this->usage_limit) * 100;
            if ($usagePercentage > 80) {
                $score += 25;
            } elseif ($usagePercentage > 50) {
                $score += 20;
            } elseif ($usagePercentage > 20) {
                $score += 15;
            } else {
                $score += 10;
            }
        }

        // Score for being public
        if ($this->is_public) {
            $score += 15;
        }

        // Score for auto-apply
        if ($this->auto_apply) {
            $score += 10;
        }

        // Score for being stackable
        if ($this->is_stackable) {
            $score += 10;
        }

        // Score for having good validity period
        if ($this->valid_from && $this->valid_until) {
            $validityDays = now()->diffInDays($this->valid_until, false);
            if ($validityDays > 30) {
                $score += 10;
            } elseif ($validityDays > 7) {
                $score += 5;
            }
        }

        return min(100, $score);
    }

    /**
     * Get discount potential (estimated total savings).
     */
    private function getDiscountPotential(): ?float
    {
        if (!$this->usage_limit || !$this->value) {
            return null;
        }

        $remainingUsage = max(0, $this->usage_limit - $this->usage_count);
        
        if ($this->type === 'percentage') {
            // Estimate based on average order amount
            $estimatedOrderAmount = $this->min_order_amount ?: 100;
            return round(($estimatedOrderAmount * $this->value / 100) * $remainingUsage, 2);
        } elseif ($this->type === 'fixed_amount') {
            return round($this->value * $remainingUsage, 2);
        }

        return null;
    }
}
