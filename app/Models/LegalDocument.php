<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Traits\HasOrganization;
use App\Enums\AppEnums;
use Illuminate\Support\Collection;

class LegalDocument extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasOrganization;

    protected $fillable = [
        'customer_profile_id',
        'organization_id',
        'type',
        'version',
        'uploaded_at',
        'verified_at',
        'verifier_user_id',
        'notes',
        'expires_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'version' => 'string',
    ];

    /**
     * Tipos de documento disponibles
     */
    public const DOCUMENT_TYPES = AppEnums::LEGAL_DOCUMENT_TYPES;

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
             
        // Collection for document versions
        $this->addMediaCollection('document_versions')
             ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png'])
             ->useDisk('private');
    }

    /**
     * Register media conversions
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('legal_documents', 'document_versions');
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

    // Scopes adicionales
    public function scopeByVersion($query, $version)
    {
        return $query->where('version', $version);
    }

    public function scopeLatestVersion($query)
    {
        return $query->orderBy('version', 'desc')->orderBy('created_at', 'desc');
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')->where('expires_at', '<', now());
    }

    public function scopeExpiringWithin($query, int $days)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '>', now())
                    ->where('expires_at', '<=', now()->addDays($days));
    }

    // Methods
    public function markAsVerified(User $verifier, ?string $notes = null)
    {
        $this->update([
            'verified_at' => now(),
            'verifier_user_id' => $verifier->id,
            'notes' => $notes ?: $this->notes,
        ]);
    }

    /**
     * Upload a new version of this document
     */
    public function uploadNewVersion($file, ?string $version = null, ?string $notes = null): self
    {
        // Auto-increment version if not provided
        if (!$version) {
            $latestVersion = self::where('customer_profile_id', $this->customer_profile_id)
                                ->where('type', $this->type)
                                ->max('version');
            
            $version = $latestVersion ? ((float) $latestVersion + 0.1) : '1.0';
        }

        // Create new document version
        $newDocument = self::create([
            'customer_profile_id' => $this->customer_profile_id,
            'organization_id' => $this->organization_id,
            'type' => $this->type,
            'version' => (string) $version,
            'uploaded_at' => now(),
            'notes' => $notes,
        ]);

        // Add media to new version
        $newDocument->addMedia($file)
                   ->usingFileName($this->generateVersionedFileName($file, $version))
                   ->toMediaCollection('legal_documents');

        return $newDocument;
    }

    /**
     * Get all versions of this document type for the customer
     */
    public function getAllVersions(): Collection
    {
        return self::where('customer_profile_id', $this->customer_profile_id)
                  ->where('type', $this->type)
                  ->orderBy('version', 'desc')
                  ->orderBy('created_at', 'desc')
                  ->get();
    }

    /**
     * Get the latest version of this document type for the customer
     */
    public function getLatestVersion(): ?self
    {
        return self::where('customer_profile_id', $this->customer_profile_id)
                  ->where('type', $this->type)
                  ->latestVersion()
                  ->first();
    }

    /**
     * Check if this is the latest version
     */
    public function isLatestVersion(): bool
    {
        $latest = $this->getLatestVersion();
        return $latest && $latest->id === $this->id;
    }

    /**
     * Check if document is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if document is expiring soon
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expires_at && 
               $this->expires_at->isFuture() && 
               $this->expires_at->lte(now()->addDays($days));
    }

    /**
     * Generate versioned filename
     */
    private function generateVersionedFileName($file, string $version): string
    {
        $extension = $file->getClientOriginalExtension();
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        
        return "{$this->type}_{$baseName}_v{$version}.{$extension}";
    }

    /**
     * Get document URL with security token
     */
    public function getSecureUrl(?string $conversion = null): ?string
    {
        $media = $this->getFirstMedia('legal_documents');
        
        if (!$media) {
            return null;
        }

        if ($conversion) {
            return $media->getUrl($conversion);
        }

        return $media->getUrl();
    }

    /**
     * Archive old versions (soft delete or move to archive collection)
     */
    public function archiveOldVersions(): int
    {
        $oldVersions = self::where('customer_profile_id', $this->customer_profile_id)
                          ->where('type', $this->type)
                          ->where('id', '!=', $this->id)
                          ->get();

        foreach ($oldVersions as $oldVersion) {
            // Move media to archive collection
            $media = $oldVersion->getFirstMedia('legal_documents');
            if ($media) {
                $oldVersion->addMediaFromUrl($media->getUrl())
                          ->toMediaCollection('document_versions');
                $media->delete();
            }
        }

        return $oldVersions->count();
    }

    /**
     * Get type label
     */
    public function getTypeLabel(): string
    {
        return self::DOCUMENT_TYPES[$this->type] ?? $this->type;
    }
}
