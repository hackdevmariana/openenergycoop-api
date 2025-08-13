<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\HasOrganization;

class LegalDocument extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasOrganization;

    protected $fillable = [
        'customer_profile_id',
        'organization_id',
        'type',
        'uploaded_at',
        'verified_at',
        'verifier_user_id',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    // Relaciones
    public function customerProfile()
    {
        return $this->belongsTo(CustomerProfile::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verifier_user_id');
    }

    // Media Collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('legal_documents')
             ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png'])
             ->useDisk('private');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopePendingVerification($query)
    {
        return $query->whereNull('verified_at');
    }

    // Methods
    public function markAsVerified(User $verifier)
    {
        $this->update([
            'verified_at' => now(),
            'verifier_user_id' => $verifier->id,
        ]);
    }
}
