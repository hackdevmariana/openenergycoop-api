<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'provider_id', 'product_id', 'contract_number', 'name', 'description',
        'type', 'status', 'total_value', 'monthly_payment', 'currency', 'deposit_amount',
        'deposit_paid', 'contracted_power', 'estimated_annual_consumption', 'guaranteed_supply_percentage',
        'green_energy_percentage', 'start_date', 'end_date', 'signed_date', 'activation_date',
        'terms_conditions', 'special_clauses', 'auto_renewal', 'renewal_period_months',
        'early_termination_fee', 'billing_frequency', 'estimated_co2_reduction',
        'sustainability_certifications', 'carbon_neutral', 'custom_fields', 'notes',
        'approved_at', 'approved_by', 'terminated_at', 'terminated_by', 'termination_reason'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_date' => 'date',
        'activation_date' => 'date',
        'approved_at' => 'datetime',
        'terminated_at' => 'datetime',
        'special_clauses' => 'array',
        'sustainability_certifications' => 'array',
        'custom_fields' => 'array',
        'deposit_paid' => 'boolean',
        'auto_renewal' => 'boolean',
        'carbon_neutral' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
