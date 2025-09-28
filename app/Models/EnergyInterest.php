<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnergyInterest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',                   // puede ser nullable si solo recoges nombre/email
        'zone_name',
        'postal_code',
        'type',                      // 'consumer', 'producer', 'mixed'
        'estimated_production_kwh_day',  // solo si es productor o mixto
        'requested_kwh_day',         // solo si es consumidor o mixto
        'contact_name',              // nombre de contacto si no hay usuario
        'contact_email',             // email de contacto si no hay usuario
        'contact_phone',             // teléfono de contacto
        'notes',                     // notas adicionales
        'status',                    // 'pending', 'approved', 'rejected', 'active'
    ];

    protected $casts = [
        'estimated_production_kwh_day' => 'decimal:2',
        'requested_kwh_day' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constantes para el tipo
    const TYPE_CONSUMER = 'consumer';
    const TYPE_PRODUCER = 'producer';
    const TYPE_MIXED = 'mixed';

    // Constantes para el estado
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ACTIVE = 'active';

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function energyZoneSummary(): BelongsTo
    {
        return $this->belongsTo(EnergyZoneSummary::class, 'zone_name', 'zone_name');
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

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

    public function scopeWithUser($query)
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeWithoutUser($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // Accessors
    public function getContactNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }
        return $this->attributes['contact_name'] ?? '';
    }

    public function getContactEmailAttribute(): string
    {
        if ($this->user) {
            return $this->user->email;
        }
        return $this->attributes['contact_email'] ?? '';
    }

    public function getFullZoneNameAttribute(): string
    {
        return $this->zone_name . ' (' . $this->postal_code . ')';
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CONSUMER => 'Consumidor',
            self::TYPE_PRODUCER => 'Productor',
            self::TYPE_MIXED => 'Mixto',
            default => 'Desconocido'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_APPROVED => 'Aprobado',
            self::STATUS_REJECTED => 'Rechazado',
            self::STATUS_ACTIVE => 'Activo',
            default => 'Desconocido'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => '#F59E0B',   // Naranja
            self::STATUS_APPROVED => '#10B981',  // Verde
            self::STATUS_REJECTED => '#EF4444',  // Rojo
            self::STATUS_ACTIVE => '#3B82F6',    // Azul
            default => '#6B7280'                 // Gris
        };
    }

    // Métodos de negocio
    public function isConsumer(): bool
    {
        return $this->type === self::TYPE_CONSUMER || $this->type === self::TYPE_MIXED;
    }

    public function isProducer(): bool
    {
        return $this->type === self::TYPE_PRODUCER || $this->type === self::TYPE_MIXED;
    }

    public function isMixed(): bool
    {
        return $this->type === self::TYPE_MIXED;
    }

    public function hasUser(): bool
    {
        return !is_null($this->user_id);
    }

    public function approve(): void
    {
        $this->status = self::STATUS_APPROVED;
        $this->save();
    }

    public function reject(): void
    {
        $this->status = self::STATUS_REJECTED;
        $this->save();
    }

    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    public function getEnergySummary(): array
    {
        $summary = [
            'type' => $this->type_label,
            'status' => $this->status_label,
            'zone' => $this->full_zone_name,
            'contact' => [
                'name' => $this->contact_name,
                'email' => $this->contact_email,
                'phone' => $this->contact_phone,
            ],
        ];

        if ($this->isProducer()) {
            $summary['production'] = [
                'estimated_kwh_day' => $this->estimated_production_kwh_day,
            ];
        }

        if ($this->isConsumer()) {
            $summary['consumption'] = [
                'requested_kwh_day' => $this->requested_kwh_day,
            ];
        }

        return $summary;
    }

    // Métodos estáticos
    public static function getTypes(): array
    {
        return [
            self::TYPE_CONSUMER => 'Consumidor',
            self::TYPE_PRODUCER => 'Productor',
            self::TYPE_MIXED => 'Mixto',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_APPROVED => 'Aprobado',
            self::STATUS_REJECTED => 'Rechazado',
            self::STATUS_ACTIVE => 'Activo',
        ];
    }

    public static function getInterestsByZone(string $zoneName): \Illuminate\Database\Eloquent\Collection
    {
        return self::byZone($zoneName)->with('user')->get();
    }

    public static function getInterestsByType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return self::byType($type)->with('user')->get();
    }

    public static function getSystemSummary(): array
    {
        return [
            'total_interests' => self::count(),
            'by_type' => [
                'consumer' => self::byType(self::TYPE_CONSUMER)->count(),
                'producer' => self::byType(self::TYPE_PRODUCER)->count(),
                'mixed' => self::byType(self::TYPE_MIXED)->count(),
            ],
            'by_status' => [
                'pending' => self::pending()->count(),
                'approved' => self::approved()->count(),
                'active' => self::active()->count(),
                'rejected' => self::byStatus(self::STATUS_REJECTED)->count(),
            ],
            'with_user' => self::withUser()->count(),
            'without_user' => self::withoutUser()->count(),
            'total_production_interest' => self::whereIn('type', [self::TYPE_PRODUCER, self::TYPE_MIXED])
                ->sum('estimated_production_kwh_day'),
            'total_consumption_interest' => self::whereIn('type', [self::TYPE_CONSUMER, self::TYPE_MIXED])
                ->sum('requested_kwh_day'),
        ];
    }
}
