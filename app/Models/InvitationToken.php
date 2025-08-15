<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InvitationToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'email',
        'organization_role_id',
        'organization_id',
        'invited_by',
        'expires_at',
        'used_at',
        'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener la organización a la que se invita
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Obtener el rol de organización que se asignará
     */
    public function organizationRole(): BelongsTo
    {
        return $this->belongsTo(OrganizationRole::class);
    }

    /**
     * Obtener el usuario que generó la invitación
     */
    public function invitedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Scope para obtener tokens pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para obtener tokens no expirados
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope para obtener tokens válidos (pendientes y no expirados)
     */
    public function scopeValid($query)
    {
        return $query->pending()->notExpired();
    }

    /**
     * Verificar si el token está expirado
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Verificar si el token es válido para uso
     */
    public function isValid(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * Marcar el token como usado
     */
    public function markAsUsed(): void
    {
        $this->update([
            'status' => 'used',
            'used_at' => now(),
        ]);
    }

    /**
     * Marcar el token como revocado
     */
    public function revoke(): void
    {
        $this->update(['status' => 'revoked']);
    }

    /**
     * Generar un token único
     */
    public static function generateUniqueToken(): string
    {
        do {
            $token = Str::random(32);
        } while (static::where('token', $token)->exists());

        return $token;
    }

    /**
     * Crear un nuevo token de invitación
     */
    public static function createInvitation(
        int $organizationId,
        int $organizationRoleId,
        int $invitedBy,
        ?string $email = null,
        ?Carbon $expiresAt = null
    ): self {
        return static::create([
            'token' => static::generateUniqueToken(),
            'email' => $email,
            'organization_id' => $organizationId,
            'organization_role_id' => $organizationRoleId,
            'invited_by' => $invitedBy,
            'expires_at' => $expiresAt ?? now()->addDays(7), // Expira en 7 días por defecto
            'status' => 'pending',
        ]);
    }

    /**
     * Boot del modelo para manejar eventos
     */
    protected static function boot()
    {
        parent::boot();

        // Generar token automáticamente si no se proporciona
        static::creating(function ($invitation) {
            if (empty($invitation->token)) {
                $invitation->token = static::generateUniqueToken();
            }
        });

        // Actualizar estado a expirado si es necesario
        static::updating(function ($invitation) {
            if ($invitation->isExpired() && $invitation->status === 'pending') {
                $invitation->status = 'expired';
            }
        });
    }

    /**
     * Obtener la URL de invitación
     */
    public function getInvitationUrlAttribute(): string
    {
        return url("/invitation/{$this->token}");
    }
}
