<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasOrganization;

class Contact extends Model
{
    use HasFactory, HasOrganization;

    protected $fillable = [
        'address',
        'icon_address',
        'phone',
        'icon_phone',
        'email',
        'icon_email',
        'latitude',
        'longitude',
        'contact_type',
        'business_hours',
        'additional_info',
        'organization_id',
        'is_draft',
        'is_primary',
        'created_by_user_id',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'business_hours' => 'array',
        'is_draft' => 'boolean',
        'is_primary' => 'boolean',
    ];

    public const CONTACT_TYPES = [
        'main' => 'Principal',
        'support' => 'Soporte',
        'sales' => 'Ventas',
        'media' => 'Medios',
        'technical' => 'Técnico',
        'billing' => 'Facturación',
        'emergency' => 'Emergencia',
    ];

    // Relationships
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_draft', false);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('contact_type', $type);
    }

    public function scopeWithLocation($query)
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    // Business Logic Methods
    public function isPublished(): bool
    {
        return !$this->is_draft;
    }

    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function getTypeLabel(): string
    {
        return self::CONTACT_TYPES[$this->contact_type] ?? $this->contact_type;
    }

    public function getFormattedAddress(): string
    {
        return $this->address ?? '';
    }

    public function getFormattedPhone(): string
    {
        if (!$this->phone) {
            return '';
        }
        
        // Format phone number (basic formatting)
        $phone = preg_replace('/[^0-9+]/', '', $this->phone);
        return $phone;
    }

    public function isBusinessHours(): bool
    {
        if (!$this->business_hours) {
            return true; // If no hours specified, assume always available
        }

        $now = now();
        $dayOfWeek = strtolower($now->format('l')); // monday, tuesday, etc.
        
        if (!isset($this->business_hours[$dayOfWeek])) {
            return false;
        }

        $hours = $this->business_hours[$dayOfWeek];
        if (!$hours || !isset($hours['open']) || !isset($hours['close'])) {
            return false;
        }

        $currentTime = $now->format('H:i');
        return $currentTime >= $hours['open'] && $currentTime <= $hours['close'];
    }

    /**
     * Boot method
     */
    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($contact) {
            // Ensure only one primary contact per type per organization
            if ($contact->is_primary) {
                static::where('organization_id', $contact->organization_id)
                      ->where('contact_type', $contact->contact_type)
                      ->where('is_primary', true)
                      ->update(['is_primary' => false]);
            }
        });

        static::updating(function ($contact) {
            // Ensure only one primary contact per type per organization
            if ($contact->is_primary && $contact->isDirty('is_primary')) {
                static::where('organization_id', $contact->organization_id)
                      ->where('contact_type', $contact->contact_type)
                      ->where('is_primary', true)
                      ->where('id', '!=', $contact->id)
                      ->update(['is_primary' => false]);
            }
        });
    }
}
