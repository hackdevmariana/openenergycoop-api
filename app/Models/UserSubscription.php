<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'energy_cooperative_id', 'provider_id', 'subscription_type', 'plan_name',
        'plan_description', 'service_category', 'included_services', 'status', 'start_date',
        'end_date', 'trial_end_date', 'next_billing_date', 'cancellation_date', 'last_renewed_at',
        'billing_frequency', 'price', 'currency', 'discount_percentage', 'discount_amount',
        'promo_code', 'energy_allowance_kwh', 'overage_rate_per_kwh', 'peak_hours_config',
        'includes_renewable_energy', 'renewable_percentage', 'preferences', 'notification_settings',
        'auto_renewal', 'renewal_reminder_days', 'current_period_usage_kwh', 'total_usage_kwh',
        'current_period_cost', 'total_cost_paid', 'billing_cycles_completed', 'loyalty_points',
        'benefits_earned', 'referral_credits', 'referrals_count', 'payment_method', 'payment_details',
        'last_payment_date', 'last_payment_amount', 'payment_status', 'cancellation_reason',
        'cancellation_feedback', 'eligible_for_reactivation', 'reactivation_deadline',
        'support_tickets_count', 'satisfaction_rating', 'special_notes', 'metadata',
        'integration_settings', 'external_subscription_id', 'tags', 'activated_at',
        'paused_at', 'suspended_at', 'created_by', 'managed_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'trial_end_date' => 'date',
        'next_billing_date' => 'date',
        'cancellation_date' => 'date',
        'last_payment_date' => 'date',
        'reactivation_deadline' => 'date',
        'last_renewed_at' => 'datetime',
        'activated_at' => 'datetime',
        'paused_at' => 'datetime',
        'suspended_at' => 'datetime',
        'included_services' => 'array',
        'peak_hours_config' => 'array',
        'preferences' => 'array',
        'notification_settings' => 'array',
        'benefits_earned' => 'array',
        'payment_details' => 'array',
        'metadata' => 'array',
        'integration_settings' => 'array',
        'tags' => 'array',
        'includes_renewable_energy' => 'boolean',
        'auto_renewal' => 'boolean',
        'eligible_for_reactivation' => 'boolean',
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'energy_allowance_kwh' => 'decimal:4',
        'overage_rate_per_kwh' => 'decimal:4',
        'renewable_percentage' => 'decimal:2',
        'current_period_usage_kwh' => 'decimal:4',
        'total_usage_kwh' => 'decimal:4',
        'current_period_cost' => 'decimal:2',
        'total_cost_paid' => 'decimal:2',
        'referral_credits' => 'decimal:2',
        'last_payment_amount' => 'decimal:2',
        'satisfaction_rating' => 'decimal:2',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function energyCooperative()
    {
        return $this->belongsTo(EnergyCooperative::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function managedBy()
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeAutoRenewal($query)
    {
        return $query->where('auto_renewal', true);
    }

    public function scopeRenewableEnergy($query)
    {
        return $query->where('includes_renewable_energy', true);
    }

    public function scopeByBillingFrequency($query, $frequency)
    {
        return $query->where('billing_frequency', $frequency);
    }

    public function scopeDueForRenewal($query, $days = 7)
    {
        return $query->where('next_billing_date', '<=', now()->addDays($days))
                    ->where('auto_renewal', true)
                    ->where('status', 'active');
    }

    // Métodos auxiliares
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isExpired()
    {
        return $this->status === 'expired';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isInTrial()
    {
        return $this->trial_end_date && now()->lt($this->trial_end_date);
    }

    public function hasOverage()
    {
        return $this->energy_allowance_kwh && 
               $this->current_period_usage_kwh > $this->energy_allowance_kwh;
    }

    public function getOverageAmount()
    {
        if (!$this->hasOverage()) {
            return 0;
        }

        $overage = $this->current_period_usage_kwh - $this->energy_allowance_kwh;
        return $overage * $this->overage_rate_per_kwh;
    }

    public function getRemainingAllowance()
    {
        if (!$this->energy_allowance_kwh) {
            return null;
        }

        return max(0, $this->energy_allowance_kwh - $this->current_period_usage_kwh);
    }

    public function canBeReactivated()
    {
        return $this->eligible_for_reactivation && 
               (!$this->reactivation_deadline || now()->lt($this->reactivation_deadline));
    }
}
