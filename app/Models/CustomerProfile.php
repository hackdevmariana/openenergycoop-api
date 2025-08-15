<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasOrganization;
use App\Enums\CommonEnums;
use App\Enums\AppEnums;

class CustomerProfile extends Model
{
    use HasFactory, HasOrganization;

    protected $fillable = [
        'user_id',
        'organization_id',
        'profile_type',
        'legal_id_type',
        'legal_id_number',
        'legal_name',
        'contract_type',
    ];

    protected $casts = [
        'profile_type' => 'string',
        'legal_id_type' => 'string',
        'contract_type' => 'string',
    ];

    /**
     * Tipos de perfil disponibles
     */
    public const PROFILE_TYPES = AppEnums::CUSTOMER_PROFILE_TYPES;

    /**
     * Tipos de identificación legal disponibles
     */
    public const LEGAL_ID_TYPES = AppEnums::LEGAL_ID_TYPES;

    /**
     * Tipos de contrato disponibles
     */
    public const CONTRACT_TYPES = AppEnums::CONTRACT_TYPES;

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contactInfo()
    {
        return $this->hasOne(CustomerProfileContactInfo::class);
    }

    public function legalDocuments()
    {
        return $this->hasMany(LegalDocument::class);
    }

    // Scopes
    public function scopeByProfileType($query, $type)
    {
        return $query->where('profile_type', $type);
    }

    public function scopeByContractType($query, $type)
    {
        return $query->where('contract_type', $type);
    }

    // Accessors
    public function getFullLegalNameAttribute()
    {
        return $this->legal_name ?? $this->user->name;
    }
}
