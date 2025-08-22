<?php

namespace App\Http\Resources\Api\V1\BondDonation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BondDonationResource extends JsonResource
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
            'donor_id' => $this->donor_id,
            'donor_name' => $this->donor_name,
            'donor_email' => $this->donor_email,
            'donor_phone' => $this->donor_phone,
            'donor_address' => $this->donor_address,
            'donor_city' => $this->donor_city,
            'donor_state' => $this->donor_state,
            'donor_country' => $this->donor_country,
            'donor_postal_code' => $this->donor_postal_code,
            'energy_bond_id' => $this->energy_bond_id,
            'organization_id' => $this->organization_id,
            'campaign_id' => $this->campaign_id,
            'donation_type' => $this->donation_type,
            'donation_type_label' => $this->getDonationTypeLabel(),
            'amount' => $this->amount,
            'formatted_amount' => $this->getFormattedAmount(),
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'payment_method_label' => $this->getPaymentMethodLabel(),
            'payment_status' => $this->payment_status,
            'payment_status_label' => $this->getPaymentStatusLabel(),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'is_anonymous' => $this->is_anonymous,
            'is_public' => $this->is_public,
            'message' => $this->message,
            'dedication_name' => $this->dedication_name,
            'dedication_message' => $this->dedication_message,
            'recurring_frequency' => $this->recurring_frequency,
            'recurring_frequency_label' => $this->getRecurringFrequencyLabel(),
            'recurring_start_date' => $this->recurring_start_date?->toISOString(),
            'recurring_start_date_formatted' => $this->recurring_start_date?->format('d/m/Y'),
            'recurring_end_date' => $this->recurring_end_date?->toISOString(),
            'recurring_end_date_formatted' => $this->recurring_end_date?->format('d/m/Y'),
            'tax_receipt_required' => $this->tax_receipt_required,
            'tax_receipt_sent' => $this->tax_receipt_sent,
            'tax_receipt_number' => $this->tax_receipt_number,
            'tax_receipt_date' => $this->tax_receipt_date?->toISOString(),
            'tax_receipt_date_formatted' => $this->tax_receipt_date?->format('d/m/Y'),
            'notes' => $this->notes,
            'internal_notes' => $this->internal_notes,
            'tags' => $this->tags,
            'attachments' => $this->attachments,
            
            // Timestamps
            'donation_date' => $this->donation_date?->toISOString(),
            'donation_date_formatted' => $this->donation_date?->format('d/m/Y'),
            'confirmed_at' => $this->confirmed_at?->toISOString(),
            'confirmed_at_formatted' => $this->confirmed_at?->format('d/m/Y H:i'),
            'rejected_at' => $this->rejected_at?->toISOString(),
            'rejected_at_formatted' => $this->rejected_at?->format('d/m/Y H:i'),
            'processed_at' => $this->processed_at?->toISOString(),
            'processed_at_formatted' => $this->processed_at?->format('d/m/Y H:i'),
            'refunded_at' => $this->refunded_at?->toISOString(),
            'refunded_at_formatted' => $this->refunded_at?->format('d/m/Y H:i'),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'cancelled_at_formatted' => $this->cancelled_at?->format('d/m/Y H:i'),
            'thank_you_sent_at' => $this->thank_you_sent_at?->toISOString(),
            'thank_you_sent_at_formatted' => $this->thank_you_sent_at?->format('d/m/Y H:i'),
            'made_public_at' => $this->made_public_at?->toISOString(),
            'made_public_at_formatted' => $this->made_public_at?->format('d/m/Y H:i'),
            'made_private_at' => $this->made_private_at?->toISOString(),
            'made_private_at_formatted' => $this->made_private_at?->format('d/m/Y H:i'),
            'created_at' => $this->created_at?->toISOString(),
            'created_at_formatted' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->toISOString(),
            'updated_at_formatted' => $this->updated_at?->format('d/m/Y H:i'),
            
            // Additional fields
            'refund_amount' => $this->refund_amount,
            'formatted_refund_amount' => $this->getFormattedRefundAmount(),
            'rejection_reason' => $this->rejection_reason,
            'thank_you_message' => $this->thank_you_message,
            'thank_you_email_sent' => $this->thank_you_email_sent,
            'confirmed_by' => $this->confirmed_by,
            'rejected_by' => $this->rejected_by,
            'processed_by' => $this->processed_by,
            'refunded_by' => $this->refunded_by,
            'cancelled_by' => $this->cancelled_by,
            'thank_you_sent_by' => $this->thank_you_sent_by,
            'made_public_by' => $this->made_public_by,
            'made_private_by' => $this->made_private_by,
            
            // Relationships
            'donor' => $this->whenLoaded('donor', function () {
                return [
                    'id' => $this->donor->id,
                    'name' => $this->donor->name,
                    'email' => $this->donor->email,
                ];
            }),
            'energy_bond' => $this->whenLoaded('energyBond', function () {
                return [
                    'id' => $this->energyBond->id,
                    'title' => $this->energyBond->title,
                    'bond_type' => $this->energyBond->bond_type,
                    'face_value' => $this->energyBond->face_value,
                ];
            }),
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                    'slug' => $this->organization->slug,
                ];
            }),
            'campaign' => $this->whenLoaded('campaign', function () {
                return [
                    'id' => $this->campaign->id,
                    'name' => $this->campaign->name,
                    'description' => $this->campaign->description,
                ];
            }),
            
            // Links
            'links' => [
                'self' => route('api.v1.bond-donations.show', $this->id),
                'edit' => route('api.v1.bond-donations.update', $this->id),
                'delete' => route('api.v1.bond-donations.destroy', $this->id),
            ],
        ];
    }

    /**
     * Get donation type label
     */
    private function getDonationTypeLabel(): string
    {
        return match($this->donation_type) {
            'one_time' => 'Una vez',
            'recurring' => 'Recurrente',
            'matching' => 'Coincidente',
            'challenge' => 'Desafío',
            'memorial' => 'Memorial',
            'honor' => 'En honor',
            'corporate' => 'Corporativa',
            'foundation' => 'Fundación',
            'other' => 'Otro',
            default => $this->donation_type,
        };
    }

    /**
     * Get payment method label
     */
    private function getPaymentMethodLabel(): string
    {
        return match($this->payment_method) {
            'credit_card' => 'Tarjeta de crédito',
            'debit_card' => 'Tarjeta de débito',
            'bank_transfer' => 'Transferencia bancaria',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'check' => 'Cheque',
            'cash' => 'Efectivo',
            'crypto' => 'Criptomoneda',
            'other' => 'Otro',
            default => $this->payment_method,
        };
    }

    /**
     * Get payment status label
     */
    private function getPaymentStatusLabel(): string
    {
        return match($this->payment_status) {
            'pending' => 'Pendiente',
            'processing' => 'Procesando',
            'completed' => 'Completado',
            'failed' => 'Fallido',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            default => $this->payment_status,
        };
    }

    /**
     * Get status label
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmada',
            'processed' => 'Procesada',
            'rejected' => 'Rechazada',
            'refunded' => 'Reembolsada',
            'cancelled' => 'Cancelada',
            default => $this->status,
        };
    }

    /**
     * Get recurring frequency label
     */
    private function getRecurringFrequencyLabel(): ?string
    {
        if (!$this->recurring_frequency) {
            return null;
        }

        return match($this->recurring_frequency) {
            'weekly' => 'Semanal',
            'monthly' => 'Mensual',
            'quarterly' => 'Trimestral',
            'annually' => 'Anual',
            default => $this->recurring_frequency,
        };
    }

    /**
     * Get formatted amount
     */
    private function getFormattedAmount(): string
    {
        $currency = $this->currency ?? 'USD';
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
        ];
        
        $symbol = $symbols[$currency] ?? $currency . ' ';
        return $symbol . number_format($this->amount, 2);
    }

    /**
     * Get formatted refund amount
     */
    private function getFormattedRefundAmount(): ?string
    {
        if (!$this->refund_amount) {
            return null;
        }

        $currency = $this->currency ?? 'USD';
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
        ];
        
        $symbol = $symbols[$currency] ?? $currency . ' ';
        return $symbol . number_format($this->refund_amount, 2);
    }
}
