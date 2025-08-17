<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Relations\HasMany;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación con los registros de consentimiento
     */
    public function consentLogs(): HasMany
    {
        return $this->hasMany(ConsentLog::class);
    }

    /**
     * Relación con los dispositivos del usuario
     */
    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    /**
     * Relación con las configuraciones del usuario
     */
    public function settings(): HasMany
    {
        return $this->hasMany(UserSettings::class);
    }

    /**
     * Obtener las suscripciones del usuario
     */
    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Reportes de energía del usuario
     */
    public function energyReports(): HasMany
    {
        return $this->hasMany(EnergyReport::class);
    }

    /**
     * Métricas de sostenibilidad del usuario
     */
    public function sustainabilityMetrics(): HasMany
    {
        return $this->hasMany(SustainabilityMetric::class);
    }

    /**
     * Indicadores de rendimiento del usuario
     */
    public function performanceIndicators(): HasMany
    {
        return $this->hasMany(PerformanceIndicator::class);
    }

    /**
     * Reportes creados por el usuario
     */
    public function createdReports(): HasMany
    {
        return $this->hasMany(EnergyReport::class, 'created_by_id');
    }

    /**
     * Pagos del usuario
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Facturas del usuario
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Transacciones del usuario
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Transacciones de wallet del usuario
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Reembolsos del usuario
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Pagos creados por el usuario (como admin)
     */
    public function createdPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'created_by_id');
    }

    /**
     * Facturas creadas por el usuario (como admin)
     */
    public function createdInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'created_by_id');
    }

    /**
     * Transacciones creadas por el usuario (como admin)
     */
    public function createdTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'created_by_id');
    }

    /**
     * Transacciones de wallet creadas por el usuario (como admin)
     */
    public function createdWalletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'created_by_id');
    }

    /**
     * Transacciones aprobadas por el usuario
     */
    public function approvedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'approved_by_id');
    }

    /**
     * Transacciones de wallet aprobadas por el usuario
     */
    public function approvedWalletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'approved_by_id');
    }

    /**
     * Reembolsos solicitados por el usuario (como admin)
     */
    public function requestedRefunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'requested_by_id');
    }

    /**
     * Reembolsos aprobados por el usuario
     */
    public function approvedRefunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'approved_by_id');
    }

    /**
     * Reembolsos procesados por el usuario
     */
    public function processedRefunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'processed_by_id');
    }

    /**
     * Obtener dispositivos activos del usuario
     */
    public function activeDevices(): HasMany
    {
        return $this->devices()->active();
    }

    /**
     * Obtener el dispositivo actual del usuario
     */
    public function currentDevice(): ?UserDevice
    {
        return $this->devices()->current()->first();
    }

    /**
     * Verificar si el usuario ha dado consentimiento para un tipo específico
     */
    public function hasConsented(string $consentType, ?string $version = null): bool
    {
        return ConsentLog::hasUserConsented($this->id, $consentType, $version);
    }

    /**
     * Registrar consentimiento del usuario
     */
    public function recordConsent(
        string $consentType,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $version = null,
        ?string $documentUrl = null,
        ?array $context = null
    ): ConsentLog {
        return ConsentLog::recordConsent(
            $this->id,
            $consentType,
            $ipAddress,
            $userAgent,
            $version,
            $documentUrl,
            $context
        );
    }

    /**
     * Obtener una configuración específica del usuario
     */
    public function getSetting(string $key, $default = null)
    {
        return UserSettings::getUserSetting($this->id, $key, $default);
    }

    /**
     * Establecer una configuración del usuario
     */
    public function setSetting(string $key, $value): UserSettings
    {
        return UserSettings::setUserSetting($this->id, $key, $value);
    }

    /**
     * Obtener todas las configuraciones del usuario
     */
    public function getAllSettings(): \Illuminate\Support\Collection
    {
        return UserSettings::getUserSettings($this->id);
    }

    /**
     * Registrar un dispositivo para el usuario
     */
    public function registerDevice(
        string $deviceType = 'web',
        ?string $deviceName = null,
        ?string $platform = null,
        ?string $userAgent = null,
        ?string $ipAddress = null,
        ?string $pushToken = null,
        bool $setCurrent = true
    ): UserDevice {
        return UserDevice::registerDevice(
            $this->id,
            $deviceType,
            $deviceName,
            $platform,
            $userAgent,
            $ipAddress,
            $pushToken,
            $setCurrent
        );
    }
}
