<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class EnergyRightPreSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',                      // Usuario interesado
        'energy_installation_id',      // (opcional) instalación asociada, si aplica
        'zone_name',                   // Si no se asocia a una instalación específica
        'postal_code',
        'kwh_per_month_reserved',      // Derecho reservado por el usuario
        'price_per_kwh',               // Precio pactado por kWh (puede ser estándar)
        'status',                      // pending, confirmed, cancelled
        'signed_at',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'expires_at' => 'datetime',
        'kwh_per_month_reserved' => 'decimal:2',
        'price_per_kwh' => 'decimal:4',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constantes para el estado
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function installation(): BelongsTo
    {
        return $this->belongsTo(EnergyInstallation::class, 'energy_installation_id');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByZone($query, string $zoneName)
    {
        return $query->where('zone_name', $zoneName);
    }

    public function scopeByPostalCode($query, string $postalCode)
    {
        return $query->where('postal_code', $postalCode);
    }

    public function scopeWithInstallation($query)
    {
        return $query->whereNotNull('energy_installation_id');
    }

    public function scopeWithoutInstallation($query)
    {
        return $query->whereNull('energy_installation_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED)
                    ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('expires_at', '<=', now()->addDays($days))
                    ->where('expires_at', '>', now());
    }

    // Accessors
    public function getFullZoneNameAttribute(): string
    {
        if ($this->installation) {
            return $this->installation->name . ' (' . $this->installation->postal_code . ')';
        }
        return $this->zone_name . ' (' . $this->postal_code . ')';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_CONFIRMED => 'Confirmado',
            self::STATUS_CANCELLED => 'Cancelado',
            default => 'Desconocido'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => '#F59E0B',   // Naranja
            self::STATUS_CONFIRMED => '#10B981', // Verde
            self::STATUS_CANCELLED => '#EF4444', // Rojo
            default => '#6B7280'                 // Gris
        };
    }

    public function getTotalValueAttribute(): float
    {
        return $this->kwh_per_month_reserved * $this->price_per_kwh;
    }

    public function getTotalValueFormattedAttribute(): string
    {
        return number_format($this->total_value, 2) . ' €/mes';
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isFuture() && $this->expires_at->diffInDays(now()) <= 30;
    }

    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }
        return $this->expires_at->diffInDays(now(), false);
    }

    public function getExpirationStatusAttribute(): string
    {
        if (!$this->expires_at) {
            return 'no_expiration';
        }

        if ($this->is_expired) {
            return 'expired';
        }

        if ($this->is_expiring_soon) {
            return 'expiring_soon';
        }

        return 'active';
    }

    // Métodos de negocio
    public function confirm(): void
    {
        $this->status = self::STATUS_CONFIRMED;
        $this->signed_at = now();
        $this->save();
    }

    public function cancel(): void
    {
        $this->status = self::STATUS_CANCELLED;
        $this->save();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function hasInstallation(): bool
    {
        return !is_null($this->energy_installation_id);
    }

    public function canBeConfirmed(): bool
    {
        return $this->isPending() && !$this->is_expired;
    }

    public function canBeCancelled(): bool
    {
        return $this->isPending() || $this->isConfirmed();
    }

    public function getPreSaleSummary(): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : null,
            'installation' => $this->installation ? [
                'id' => $this->installation->id,
                'name' => $this->installation->name,
                'postal_code' => $this->installation->postal_code,
            ] : null,
            'zone' => $this->full_zone_name,
            'kwh_per_month_reserved' => $this->kwh_per_month_reserved,
            'price_per_kwh' => $this->price_per_kwh,
            'total_value' => $this->total_value,
            'total_value_formatted' => $this->total_value_formatted,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'signed_at' => $this->signed_at,
            'expires_at' => $this->expires_at,
            'is_expired' => $this->is_expired,
            'is_expiring_soon' => $this->is_expiring_soon,
            'days_until_expiration' => $this->days_until_expiration,
            'expiration_status' => $this->expiration_status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    // Métodos estáticos
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_CONFIRMED => 'Confirmado',
            self::STATUS_CANCELLED => 'Cancelado',
        ];
    }

    public static function getPreSalesByZone(string $zoneName): \Illuminate\Database\Eloquent\Collection
    {
        return self::byZone($zoneName)->with(['user', 'installation'])->get();
    }

    public static function getPreSalesByStatus(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return self::byStatus($status)->with(['user', 'installation'])->get();
    }

    public static function getSystemSummary(): array
    {
        return [
            'total_presales' => self::count(),
            'by_status' => [
                'pending' => self::pending()->count(),
                'confirmed' => self::confirmed()->count(),
                'cancelled' => self::cancelled()->count(),
            ],
            'with_installation' => self::withInstallation()->count(),
            'without_installation' => self::withoutInstallation()->count(),
            'active_presales' => self::active()->count(),
            'expired_presales' => self::expired()->count(),
            'expiring_soon' => self::expiringSoon()->count(),
            'total_kwh_reserved' => self::confirmed()->sum('kwh_per_month_reserved'),
            'total_value_reserved' => self::confirmed()->get()->sum('total_value'),
            'average_price_per_kwh' => self::confirmed()->avg('price_per_kwh'),
        ];
    }

    public static function getExpiringPreSales(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return self::expiringSoon($days)->with(['user', 'installation'])->get();
    }

    public static function getActivePreSales(): \Illuminate\Database\Eloquent\Collection
    {
        return self::active()->with(['user', 'installation'])->get();
    }

    public static function getTotalReservedEnergy(): float
    {
        return self::confirmed()->sum('kwh_per_month_reserved');
    }

    public static function getTotalReservedValue(): float
    {
        return self::confirmed()->get()->sum('total_value');
    }

    public static function getAveragePricePerKwh(): float
    {
        return self::confirmed()->avg('price_per_kwh') ?? 0;
    }
}