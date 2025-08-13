<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasOrganization;

class CustomerProfileContactInfo extends Model
{
    use HasFactory, HasOrganization;

    protected $fillable = [
        'customer_profile_id',
        'organization_id',
        'billing_email',
        'technical_email',
        'address',
        'postal_code',
        'city',
        'province',
        'iban',
        'cups',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    // Relaciones
    public function customerProfile()
    {
        return $this->belongsTo(CustomerProfile::class);
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('valid_from', '<=', now())
                    ->where(function($q) {
                        $q->whereNull('valid_to')
                          ->orWhere('valid_to', '>=', now());
                    });
    }

    public function scopeByProvince($query, $province)
    {
        return $query->where('province', $province);
    }
}
