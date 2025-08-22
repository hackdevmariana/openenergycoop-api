<?php

namespace App\Http\Resources\Api\V1\SaleOrder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleOrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'customer_city' => $this->customer_city,
            'customer_state' => $this->customer_state,
            'customer_country' => $this->customer_country,
            'customer_postal_code' => $this->customer_postal_code,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'payment_status' => $this->payment_status,
            'payment_status_label' => $this->getPaymentStatusLabel(),
            'payment_method' => $this->payment_method,
            'subtotal' => $this->subtotal,
            'formatted_subtotal' => $this->formatCurrency($this->subtotal),
            'tax_amount' => $this->tax_amount,
            'formatted_tax_amount' => $this->formatCurrency($this->tax_amount),
            'shipping_amount' => $this->shipping_amount,
            'formatted_shipping_amount' => $this->formatCurrency($this->shipping_amount),
            'discount_amount' => $this->discount_amount,
            'formatted_discount_amount' => $this->formatCurrency($this->discount_amount),
            'total' => $this->total,
            'formatted_total' => $this->formatCurrency($this->total),
            'currency' => $this->currency ?? 'USD',
            'notes' => $this->notes,
            'internal_notes' => $this->internal_notes,
            'is_urgent' => $this->is_urgent,
            'shipping_method' => $this->shipping_method,
            'tracking_number' => $this->tracking_number,
            'expected_delivery_date' => $this->expected_delivery_date?->toISOString(),
            'discount_code' => $this->discount_code,
            
            // Campos calculados
            'days_until_delivery' => $this->expected_delivery_date ? now()->diffInDays($this->expected_delivery_date, false) : null,
            'is_overdue' => $this->expected_delivery_date ? now()->isAfter($this->expected_delivery_date) : false,
            'is_delivery_soon' => $this->expected_delivery_date ? now()->diffInDays($this->expected_delivery_date, false) <= 3 : false,
            'profit_margin' => $this->calculateProfitMargin(),
            'order_age_days' => now()->diffInDays($this->created_at),
            'is_high_value' => $this->total > 1000,
            'is_low_value' => $this->total < 100,
            'has_discount' => $this->discount_amount > 0,
            'discount_percentage' => $this->subtotal > 0 ? round(($this->discount_amount / $this->subtotal) * 100, 1) : 0,
            
            // Relaciones
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                    'slug' => $this->organization->slug,
                ];
            }),
            
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                ];
            }),
            
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'formatted_unit_price' => $this->formatCurrency($item->unit_price),
                        'total_price' => $item->total_price,
                        'formatted_total_price' => $this->formatCurrency($item->total_price),
                        'sku' => $item->sku,
                        'description' => $item->description,
                        'options' => $item->options,
                    ];
                });
            }),
            
            'payments' => $this->whenLoaded('payments', function () {
                return $this->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'formatted_amount' => $this->formatCurrency($payment->amount),
                        'method' => $payment->method,
                        'status' => $payment->status,
                        'transaction_id' => $payment->transaction_id,
                        'created_at' => $payment->created_at->toISOString(),
                    ];
                });
            }),
            
            // Direcciones
            'billing_address' => $this->billing_address,
            'shipping_address' => $this->shipping_address,
            'custom_fields' => $this->custom_fields,
            'tags' => $this->tags,
            'attachments' => $this->attachments,
            
            // Metadatos
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Enlaces HATEOAS
            'links' => [
                'self' => route('api.v1.sale-orders.show', $this->id),
                'edit' => route('api.v1.sale-orders.update', $this->id),
                'delete' => route('api.v1.sale-orders.destroy', $this->id),
            ],
        ];
    }

    /**
     * Get the status label.
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'processing' => 'En Proceso',
            'shipped' => 'Enviado',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            default => 'Desconocido'
        };
    }

    /**
     * Get the payment status label.
     */
    private function getPaymentStatusLabel(): string
    {
        return match($this->payment_status) {
            'pending' => 'Pendiente',
            'processing' => 'Procesando',
            'paid' => 'Pagado',
            'failed' => 'Fallido',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
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
     * Calculate profit margin (example calculation).
     */
    private function calculateProfitMargin(): ?float
    {
        // Esta es una implementación de ejemplo
        // En un sistema real, se calcularía basándose en el costo real de los productos
        if ($this->subtotal && $this->subtotal > 0) {
            // Simular un margen del 30% como ejemplo
            $estimatedCost = $this->subtotal * 0.7;
            $profit = $this->total - $estimatedCost;
            return round(($profit / $this->total) * 100, 1);
        }
        
        return null;
    }
}
