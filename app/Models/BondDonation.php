<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BondDonation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bond_id',
        'benefactor_id',
        'benefactor_type',
        'amount_kwh',
        'donated_at',
        'notes',
        'payment_reference',
    ];

    protected $casts = [
        'donated_at' => 'datetime',
        'amount_kwh' => 'decimal:4',
    ];

    // Relaciones
    public function bond(): BelongsTo
    {
        return $this->belongsTo(EnergyBond::class, 'bond_id');
    }

    public function benefactor(): BelongsTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('donated_at', '>=', now()->subDays($days));
    }

    public function scopeByAmount($query, $minAmount = 0)
    {
        return $query->where('amount_kwh', '>=', $minAmount);
    }

    // Métodos
    public function getFormattedAmount(): string
    {
        return number_format($this->amount_kwh, 2) . ' kWh';
    }

    public function getDonorName(): string
    {
        if ($this->benefactor_type === User::class) {
            return $this->benefactor->name ?? 'Usuario';
        }
        
        if ($this->benefactor_type === Organization::class) {
            return $this->benefactor->name ?? 'Organización';
        }
        
        return 'Desconocido';
    }
}
