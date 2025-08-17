<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyCooperative extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'description', 'mission_statement', 'vision_statement',
        'legal_name', 'tax_id', 'registration_number', 'legal_form', 'status',
        'contact_info', 'address', 'city', 'state_province', 'postal_code', 'country',
        'latitude', 'longitude', 'founder_id', 'administrator_id', 'founded_date',
        'registration_date', 'activation_date', 'max_members', 'current_members',
        'membership_fee', 'membership_fee_frequency', 'open_enrollment', 'enrollment_requirements',
        'energy_types', 'total_capacity_kw', 'available_capacity_kw', 'allows_energy_sharing',
        'allows_trading', 'sharing_fee_percentage', 'currency', 'payment_methods',
        'requires_deposit', 'deposit_amount', 'total_energy_shared_kwh', 'total_cost_savings_eur',
        'total_co2_reduction_kg', 'total_projects', 'average_member_satisfaction',
        'settings', 'notifications_config', 'timezone', 'language', 'certifications',
        'sustainability_goals', 'achievements', 'metadata', 'notes', 'is_featured',
        'visibility_level', 'last_activity_at', 'verified_at', 'verified_by'
    ];

    protected $casts = [
        'founded_date' => 'date',
        'registration_date' => 'date',
        'activation_date' => 'date',
        'verified_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'contact_info' => 'array',
        'energy_types' => 'array',
        'payment_methods' => 'array',
        'settings' => 'array',
        'notifications_config' => 'array',
        'certifications' => 'array',
        'sustainability_goals' => 'array',
        'metadata' => 'array',
        'open_enrollment' => 'boolean',
        'allows_energy_sharing' => 'boolean',
        'allows_trading' => 'boolean',
        'requires_deposit' => 'boolean',
        'is_featured' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'total_capacity_kw' => 'decimal:4',
        'available_capacity_kw' => 'decimal:4',
        'sharing_fee_percentage' => 'decimal:2',
        'membership_fee' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'total_energy_shared_kwh' => 'decimal:4',
        'total_cost_savings_eur' => 'decimal:2',
        'total_co2_reduction_kg' => 'decimal:2',
        'average_member_satisfaction' => 'decimal:2',
    ];

    // Relaciones
    public function founder()
    {
        return $this->belongsTo(User::class, 'founder_id');
    }

    public function administrator()
    {
        return $this->belongsTo(User::class, 'administrator_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function energySharings()
    {
        return $this->hasMany(EnergySharing::class);
    }

    public function energyReports()
    {
        return $this->hasMany(EnergyReport::class);
    }

    public function sustainabilityMetrics()
    {
        return $this->hasMany(SustainabilityMetric::class);
    }

    public function performanceIndicators()
    {
        return $this->hasMany(PerformanceIndicator::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'user_subscriptions')
                    ->wherePivot('status', 'active')
                    ->withPivot(['start_date', 'end_date', 'subscription_type']);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeOpenEnrollment($query)
    {
        return $query->where('open_enrollment', true);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    // Métodos auxiliares
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isVerified()
    {
        return !is_null($this->verified_at);
    }

    public function hasCapacity()
    {
        return $this->available_capacity_kw > 0;
    }

    public function isFull()
    {
        return $this->max_members && $this->current_members >= $this->max_members;
    }

    public function canAcceptNewMembers()
    {
        return $this->isActive() && $this->open_enrollment && !$this->isFull();
    }
}
