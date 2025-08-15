<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ConsentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'consent_type',
        'consented_at',
        'ip_address',
        'user_agent',
        'version',
        'consent_document_url',
        'revoked_at',
        'consent_context',
    ];

    protected $casts = [
        'consented_at' => 'datetime',
        'revoked_at' => 'datetime',
        'consent_context' => 'array',
    ];

    /**
     * Tipos de consentimiento disponibles
     */
    public const CONSENT_TYPES = [
        'terms_and_conditions' => 'Términos y Condiciones',
        'privacy_policy' => 'Política de Privacidad',
        'cookies_policy' => 'Política de Cookies',
        'marketing_communications' => 'Comunicaciones de Marketing',
        'data_processing' => 'Procesamiento de Datos',
        'newsletter' => 'Newsletter',
        'analytics' => 'Analytics y Seguimiento',
        'third_party_sharing' => 'Compartir con Terceros',
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para obtener consentimientos activos (no revocados)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * Scope para obtener consentimientos revocados
     */
    public function scopeRevoked(Builder $query): Builder
    {
        return $query->whereNotNull('revoked_at');
    }

    /**
     * Scope para filtrar por tipo de consentimiento
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('consent_type', $type);
    }

    /**
     * Scope para obtener consentimientos por usuario
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Verificar si el consentimiento está activo
     */
    public function isActive(): bool
    {
        return is_null($this->revoked_at);
    }

    /**
     * Verificar si el consentimiento ha sido revocado
     */
    public function isRevoked(): bool
    {
        return !is_null($this->revoked_at);
    }

    /**
     * Revocar el consentimiento
     */
    public function revoke(): bool
    {
        return $this->update(['revoked_at' => now()]);
    }

    /**
     * Obtener el nombre legible del tipo de consentimiento
     */
    public function getConsentTypeNameAttribute(): string
    {
        return self::CONSENT_TYPES[$this->consent_type] ?? $this->consent_type;
    }

    /**
     * Verificar si el consentimiento es válido para una versión específica
     */
    public function isValidForVersion(?string $version = null): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($version && $this->version && $this->version !== $version) {
            return false;
        }

        return true;
    }

    /**
     * Obtener el último consentimiento activo de un usuario para un tipo específico
     */
    public static function getLatestConsentForUser(int $userId, string $consentType): ?self
    {
        return self::forUser($userId)
            ->ofType($consentType)
            ->active()
            ->latest('consented_at')
            ->first();
    }

    /**
     * Verificar si un usuario ha dado consentimiento para un tipo específico
     */
    public static function hasUserConsented(int $userId, string $consentType, ?string $version = null): bool
    {
        $consent = self::getLatestConsentForUser($userId, $consentType);
        
        if (!$consent) {
            return false;
        }

        return $consent->isValidForVersion($version);
    }

    /**
     * Registrar un nuevo consentimiento
     */
    public static function recordConsent(
        int $userId,
        string $consentType,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $version = null,
        ?string $documentUrl = null,
        ?array $context = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'consent_type' => $consentType,
            'consented_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'version' => $version,
            'consent_document_url' => $documentUrl,
            'consent_context' => $context,
        ]);
    }
}
