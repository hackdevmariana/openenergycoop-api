<?php

namespace App\Http\Resources\Api\V1\PreSaleOffer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PreSaleOfferResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'discount_percentage' => $this->discount_percentage,
            'fixed_discount_amount' => $this->fixed_discount_amount,
            'original_price' => $this->original_price,
            'formatted_original_price' => $this->formatCurrency($this->original_price),
            'price' => $this->price,
            'formatted_price' => $this->formatCurrency($this->price),
            'currency' => $this->currency ?? 'USD',
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'is_featured' => $this->is_featured,
            'is_limited_time' => $this->is_limited_time,
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'max_quantity' => $this->max_quantity,
            'min_quantity' => $this->min_quantity,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'terms_conditions' => $this->terms_conditions,
            'organization_id' => $this->organization_id,
            
            // Campos calculados
            'days_until_start' => $this->start_date ? now()->diffInDays($this->start_date, false) : null,
            'days_until_end' => $this->end_date ? now()->diffInDays($this->end_date, false) : null,
            'is_active_now' => $this->isActiveNow(),
            'is_expired' => $this->isExpired(),
            'is_starting_soon' => $this->isStartingSoon(),
            'is_ending_soon' => $this->isEndingSoon(),
            'discount_amount' => $this->calculateDiscountAmount(),
            'formatted_discount_amount' => $this->formatCurrency($this->calculateDiscountAmount()),
            'savings_percentage' => $this->calculateSavingsPercentage(),
            'offer_age_days' => now()->diffInDays($this->created_at),
            'is_high_discount' => $this->discount_percentage > 50,
            'is_low_discount' => $this->discount_percentage < 10,
            'has_quantity_limits' => $this->max_quantity !== null || $this->min_quantity !== null,
            'quantity_available' => $this->max_quantity ? $this->max_quantity : 'unlimited',
            
            // Relaciones
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                    'slug' => $this->organization->slug,
                ];
            }),
            
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug,
                    'price' => $this->product->price,
                ];
            }),
            
            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),
            
            // Arrays y campos personalizados
            'tags' => $this->tags,
            'requirements' => $this->requirements,
            'benefits' => $this->benefits,
            'restrictions' => $this->restrictions,
            'custom_fields' => $this->custom_fields,
            'attachments' => $this->attachments,
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
            
            // Metadatos
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'activated_at' => $this->activated_at?->toISOString(),
            'deactivated_at' => $this->deactivated_at?->toISOString(),
            
            // Enlaces HATEOAS
            'links' => [
                'self' => route('api.v1.pre-sale-offers.show', $this->id),
                'edit' => route('api.v1.pre-sale-offers.update', $this->id),
                'delete' => route('api.v1.pre-sale-offers.destroy', $this->id),
            ],
        ];
    }

    /**
     * Get the type label.
     */
    private function getTypeLabel(): string
    {
        return match($this->type) {
            'discount' => 'Porcentaje de Descuento',
            'fixed_amount' => 'Monto Fijo de Descuento',
            'free_shipping' => 'Envío Gratis',
            'buy_one_get_one' => 'Compra Uno Lleva Otro',
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
            'active' => 'Activa',
            'inactive' => 'Inactiva',
            'draft' => 'Borrador',
            'expired' => 'Expirada',
            default => 'Desconocido'
        };
    }

    /**
     * Format currency amount.
     */
    private function formatCurrency(?float $amount): string
    {
        if ($amount === null) {
            return '$0.00';
        }
        
        $currency = $this->currency ?? 'USD';
        $symbol = match($currency) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'MXN' => '$',
            default => '$'
        };
        
        return $symbol . number_format($amount, 2);
    }

    /**
     * Check if the offer is active now.
     */
    private function isActiveNow(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();
        
        if ($this->start_date && $now < $this->start_date) {
            return false;
        }
        
        if ($this->end_date && $now > $this->end_date) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if the offer is expired.
     */
    private function isExpired(): bool
    {
        return $this->end_date && now() > $this->end_date;
    }

    /**
     * Check if the offer is starting soon (within 7 days).
     */
    private function isStartingSoon(): bool
    {
        return $this->start_date && now()->diffInDays($this->start_date, false) <= 7 && now() < $this->start_date;
    }

    /**
     * Check if the offer is ending soon (within 7 days).
     */
    private function isEndingSoon(): bool
    {
        return $this->end_date && now()->diffInDays($this->end_date, false) <= 7 && now() < $this->end_date;
    }

    /**
     * Calculate the discount amount.
     */
    private function calculateDiscountAmount(): ?float
    {
        if ($this->type === 'discount' && $this->discount_percentage && $this->original_price) {
            return ($this->original_price * $this->discount_percentage) / 100;
        }
        
        if ($this->type === 'fixed_amount' && $this->fixed_discount_amount) {
            return $this->fixed_discount_amount;
        }
        
        if ($this->original_price && $this->price) {
            return $this->original_price - $this->price;
        }
        
        return null;
    }

    /**
     * Calculate the savings percentage.
     */
    private function calculateSavingsPercentage(): ?float
    {
        if ($this->original_price && $this->price && $this->original_price > 0) {
            return round((($this->original_price - $this->price) / $this->original_price) * 100, 1);
        }
        
        if ($this->discount_percentage) {
            return $this->discount_percentage;
        }
        
        return null;
    }
}
