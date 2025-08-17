<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergySharing extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_user_id', 'consumer_user_id', 'energy_cooperative_id', 'sharing_code',
        'title', 'description', 'sharing_type', 'status', 'energy_amount_kwh',
        'energy_delivered_kwh', 'energy_remaining_kwh', 'energy_source', 'is_renewable',
        'renewable_percentage', 'sharing_start_datetime', 'sharing_end_datetime',
        'proposal_expiry_datetime', 'duration_hours', 'time_slots', 'flexible_timing',
        'price_per_kwh', 'total_amount', 'platform_fee', 'cooperative_fee', 'net_amount',
        'currency', 'payment_method', 'quality_score', 'reliability_score', 'delivery_efficiency',
        'interruptions_count', 'average_voltage', 'frequency_stability', 'max_distance_km',
        'actual_distance_km', 'grid_connection_details', 'requires_grid_approval',
        'grid_operator', 'connection_type', 'provider_preferences', 'consumer_preferences',
        'technical_requirements', 'special_conditions', 'allows_partial_delivery',
        'min_delivery_kwh', 'co2_reduction_kg', 'environmental_impact_score',
        'sustainability_metrics', 'certified_green_energy', 'certification_number',
        'monitoring_data', 'last_monitoring_update', 'monitoring_frequency_minutes',
        'real_time_tracking', 'alerts_configuration', 'dispute_reason', 'dispute_resolution',
        'mediator_id', 'dispute_opened_at', 'dispute_resolved_at', 'provider_rating',
        'consumer_rating', 'provider_feedback', 'consumer_feedback', 'would_repeat',
        'payment_due_date', 'payment_completed_at', 'payment_status', 'payment_transaction_id',
        'payment_details', 'metadata', 'integration_data', 'external_reference', 'tags',
        'notes', 'proposed_at', 'accepted_at', 'started_at', 'completed_at', 'cancelled_at'
    ];

    protected $casts = [
        'sharing_start_datetime' => 'datetime',
        'sharing_end_datetime' => 'datetime',
        'proposal_expiry_datetime' => 'datetime',
        'last_monitoring_update' => 'datetime',
        'dispute_opened_at' => 'datetime',
        'dispute_resolved_at' => 'datetime',
        'payment_due_date' => 'datetime',
        'payment_completed_at' => 'datetime',
        'proposed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'time_slots' => 'array',
        'grid_connection_details' => 'array',
        'provider_preferences' => 'array',
        'consumer_preferences' => 'array',
        'technical_requirements' => 'array',
        'sustainability_metrics' => 'array',
        'monitoring_data' => 'array',
        'alerts_configuration' => 'array',
        'payment_details' => 'array',
        'metadata' => 'array',
        'integration_data' => 'array',
        'tags' => 'array',
        'is_renewable' => 'boolean',
        'flexible_timing' => 'boolean',
        'requires_grid_approval' => 'boolean',
        'allows_partial_delivery' => 'boolean',
        'certified_green_energy' => 'boolean',
        'real_time_tracking' => 'boolean',
        'would_repeat' => 'boolean',
        'energy_amount_kwh' => 'decimal:4',
        'energy_delivered_kwh' => 'decimal:4',
        'energy_remaining_kwh' => 'decimal:4',
        'renewable_percentage' => 'decimal:2',
        'price_per_kwh' => 'decimal:4',
        'total_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'cooperative_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'reliability_score' => 'decimal:2',
        'delivery_efficiency' => 'decimal:2',
        'average_voltage' => 'decimal:2',
        'frequency_stability' => 'decimal:4',
        'max_distance_km' => 'decimal:2',
        'actual_distance_km' => 'decimal:2',
        'min_delivery_kwh' => 'decimal:4',
        'co2_reduction_kg' => 'decimal:4',
        'environmental_impact_score' => 'decimal:2',
        'provider_rating' => 'decimal:2',
        'consumer_rating' => 'decimal:2',
    ];

    // Relaciones
    public function providerUser()
    {
        return $this->belongsTo(User::class, 'provider_user_id');
    }

    public function consumerUser()
    {
        return $this->belongsTo(User::class, 'consumer_user_id');
    }

    public function energyCooperative()
    {
        return $this->belongsTo(EnergyCooperative::class);
    }

    public function mediator()
    {
        return $this->belongsTo(User::class, 'mediator_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'proposed');
    }

    public function scopeDisputed($query)
    {
        return $query->where('status', 'disputed');
    }

    public function scopeRenewable($query)
    {
        return $query->where('is_renewable', true);
    }

    public function scopeCertifiedGreen($query)
    {
        return $query->where('certified_green_energy', true);
    }

    public function scopeByProvider($query, $userId)
    {
        return $query->where('provider_user_id', $userId);
    }

    public function scopeByConsumer($query, $userId)
    {
        return $query->where('consumer_user_id', $userId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('provider_user_id', $userId)
              ->orWhere('consumer_user_id', $userId);
        });
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('status', ['accepted', 'active']);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('sharing_start_datetime', [$startDate, $endDate]);
    }

    public function scopeExpiringSoon($query, $hours = 24)
    {
        return $query->where('proposal_expiry_datetime', '<=', now()->addHours($hours))
                    ->where('status', 'proposed');
    }

    // Métodos auxiliares
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isPending()
    {
        return $this->status === 'proposed';
    }

    public function isDisputed()
    {
        return $this->status === 'disputed';
    }

    public function isExpired()
    {
        return $this->status === 'expired';
    }

    public function canBeAccepted()
    {
        return $this->status === 'proposed' && 
               $this->proposal_expiry_datetime > now();
    }

    public function canBeStarted()
    {
        return $this->status === 'accepted' && 
               $this->sharing_start_datetime <= now();
    }

    public function isFullyDelivered()
    {
        return $this->energy_delivered_kwh >= $this->energy_amount_kwh;
    }

    public function getDeliveryPercentage()
    {
        if ($this->energy_amount_kwh == 0) {
            return 0;
        }

        return ($this->energy_delivered_kwh / $this->energy_amount_kwh) * 100;
    }

    public function getRemainingEnergy()
    {
        return max(0, $this->energy_amount_kwh - $this->energy_delivered_kwh);
    }

    public function hasDispute()
    {
        return $this->status === 'disputed';
    }

    public function canBeRated()
    {
        return $this->status === 'completed';
    }

    public function getAverageRating()
    {
        $ratings = array_filter([$this->provider_rating, $this->consumer_rating]);
        
        if (empty($ratings)) {
            return null;
        }

        return array_sum($ratings) / count($ratings);
    }

    public function isUserInvolved($userId)
    {
        return $this->provider_user_id == $userId || $this->consumer_user_id == $userId;
    }

    public function getUserRole($userId)
    {
        if ($this->provider_user_id == $userId) {
            return 'provider';
        }

        if ($this->consumer_user_id == $userId) {
            return 'consumer';
        }

        return null;
    }
}
