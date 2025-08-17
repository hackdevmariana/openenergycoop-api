<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarbonCredit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'provider_id', 'credit_id', 'credit_type', 'project_name', 'project_description',
        'project_type', 'project_country', 'project_location', 'total_credits', 'available_credits',
        'retired_credits', 'transferred_credits', 'status', 'vintage_year', 'credit_period_start',
        'credit_period_end', 'purchase_price_per_credit', 'current_market_price', 'registry_id',
        'serial_number', 'blockchain_hash', 'additionality_demonstrated', 'methodology',
        'verifier_name', 'verification_date', 'actual_co2_reduced', 'leakage_percentage',
        'permanence_risk_assessment', 'monitoring_frequency', 'transaction_history',
        'retirement_reason', 'retirement_date', 'retired_by', 'original_owner_id',
        'last_transfer_date'
    ];

    protected $casts = [
        'vintage_year' => 'date',
        'credit_period_start' => 'date',
        'credit_period_end' => 'date',
        'verification_date' => 'date',
        'retirement_date' => 'date',
        'last_transfer_date' => 'date',
        'additionality_demonstrated' => 'boolean',
        'transaction_history' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function originalOwner()
    {
        return $this->belongsTo(User::class, 'original_owner_id');
    }
}
