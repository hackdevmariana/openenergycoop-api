<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnergyMeter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'installation_id',
        'consumption_point_id',
        'user_id',
        'type',
        'location',
        'serial_number',
        'active',
        'delta_kwh',
        'meterable_id',
        'meterable_type',
        'manufacturer',
        'model',
        'firmware_version',
        'calibration_date',
        'next_calibration_date',
        'accuracy_class',
        'measurement_range',
        'communication_protocol',
        'last_communication',
        'battery_level',
        'signal_strength',
        'maintenance_required',
        'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
        'delta_kwh' => 'decimal:4',
        'calibration_date' => 'datetime',
        'next_calibration_date' => 'datetime',
        'last_communication' => 'datetime',
        'battery_level' => 'decimal:2',
        'signal_strength' => 'decimal:2',
        'maintenance_required' => 'boolean',
        'measurement_range' => 'array',
    ];

    // Enums
    const TYPE_CONSUMPTION = 'consumption';
    const TYPE_PRODUCTION = 'production';
    const TYPE_STORAGE = 'storage';
    const TYPE_DISTRIBUTION = 'distribution';

    const ACCURACY_CLASS_A = 'A';
    const ACCURACY_CLASS_B = 'B';
    const ACCURACY_CLASS_C = 'C';
    const ACCURACY_CLASS_D = 'D';

    const COMMUNICATION_PROTOCOL_MODBUS = 'modbus';
    const COMMUNICATION_PROTOCOL_DLMS = 'dlms';
    const COMMUNICATION_PROTOCOL_MQTT = 'mqtt';
    const COMMUNICATION_PROTOCOL_HTTP = 'http';
    const COMMUNICATION_PROTOCOL_4G = '4g';
    const COMMUNICATION_PROTOCOL_LORA = 'lora';

    public static function getTypes(): array
    {
        return [
            self::TYPE_CONSUMPTION => 'Consumo',
            self::TYPE_PRODUCTION => 'Producción',
            self::TYPE_STORAGE => 'Almacenamiento',
            self::TYPE_DISTRIBUTION => 'Distribución',
        ];
    }

    public static function getAccuracyClasses(): array
    {
        return [
            self::ACCURACY_CLASS_A => 'Clase A (0.5%)',
            self::ACCURACY_CLASS_B => 'Clase B (1.0%)',
            self::ACCURACY_CLASS_C => 'Clase C (2.0%)',
            self::ACCURACY_CLASS_D => 'Clase D (5.0%)',
        ];
    }

    public static function getCommunicationProtocols(): array
    {
        return [
            self::COMMUNICATION_PROTOCOL_MODBUS => 'Modbus',
            self::COMMUNICATION_PROTOCOL_DLMS => 'DLMS',
            self::COMMUNICATION_PROTOCOL_MQTT => 'MQTT',
            self::COMMUNICATION_PROTOCOL_HTTP => 'HTTP',
            self::COMMUNICATION_PROTOCOL_4G => '4G',
            self::COMMUNICATION_PROTOCOL_LORA => 'LoRa',
        ];
    }

    // Relaciones
    public function installation(): BelongsTo
    {
        return $this->belongsTo(EnergyInstallation::class);
    }

    public function consumptionPoint(): BelongsTo
    {
        return $this->belongsTo(ConsumptionPoint::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function meterable(): MorphTo
    {
        return $this->morphTo();
    }

    public function readings(): HasMany
    {
        return $this->hasMany(EnergyReading::class, 'meter_id');
    }

    public function forecasts(): HasMany
    {
        return $this->hasMany(EnergyForecast::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByInstallation($query, $installationId)
    {
        return $query->where('installation_id', $installationId);
    }

    public function scopeByConsumptionPoint($query, $consumptionPointId)
    {
        return $query->where('consumption_point_id', $consumptionPointId);
    }

    public function scopeByAccuracyClass($query, $accuracyClass)
    {
        return $query->where('accuracy_class', $accuracyClass);
    }

    public function scopeByCommunicationProtocol($query, $protocol)
    {
        return $query->where('communication_protocol', $protocol);
    }

    public function scopeNeedsCalibration($query)
    {
        return $query->where('next_calibration_date', '<=', now());
    }

    public function scopeMaintenanceRequired($query)
    {
        return $query->where('maintenance_required', true);
    }

    public function scopeLowBattery($query, $threshold = 20)
    {
        return $query->where('battery_level', '<=', $threshold);
    }

    public function scopeWeakSignal($query, $threshold = 30)
    {
        return $query->where('signal_strength', '<=', $threshold);
    }

    // Métodos
    public function isConsumption(): bool
    {
        return $this->type === self::TYPE_CONSUMPTION;
    }

    public function isProduction(): bool
    {
        return $this->type === self::TYPE_PRODUCTION;
    }

    public function isStorage(): bool
    {
        return $this->type === self::TYPE_STORAGE;
    }

    public function isDistribution(): bool
    {
        return $this->type === self::TYPE_DISTRIBUTION;
    }

    public function needsCalibration(): bool
    {
        if (!$this->next_calibration_date) {
            return false;
        }
        
        return $this->next_calibration_date->isPast();
    }

    public function isLowBattery(): bool
    {
        return $this->battery_level <= 20;
    }

    public function hasWeakSignal(): bool
    {
        return $this->signal_strength <= 30;
    }

    public function isCommunicating(): bool
    {
        if (!$this->last_communication) {
            return false;
        }
        
        // Considerar que no se ha comunicado en las últimas 24 horas
        return $this->last_communication->diffInHours(now()) <= 24;
    }

    public function getTotalReadings(): int
    {
        return $this->readings()->count();
    }

    public function getTotalEnergy(): float
    {
        return $this->readings()->sum('delta_kwh');
    }

    public function getLastReading(): ?EnergyReading
    {
        return $this->readings()->latest('timestamp')->first();
    }

    public function getLastReadingValue(): float
    {
        $lastReading = $this->getLastReading();
        return $lastReading ? $lastReading->kwh : 0;
    }

    public function getLastReadingTimestamp(): ?string
    {
        $lastReading = $this->getLastReading();
        return $lastReading ? $lastReading->timestamp : null;
    }

    public function getDailyEnergy(): float
    {
        $today = now()->startOfDay();
        return $this->readings()
            ->where('timestamp', '>=', $today)
            ->sum('delta_kwh');
    }

    public function getMonthlyEnergy(): float
    {
        $thisMonth = now()->startOfMonth();
        return $this->readings()
            ->where('timestamp', '>=', $thisMonth)
            ->sum('delta_kwh');
    }

    public function getYearlyEnergy(): float
    {
        $thisYear = now()->startOfYear();
        return $this->readings()
            ->where('timestamp', '>=', $thisYear)
            ->sum('delta_kwh');
    }

    public function getAverageDailyEnergy(): float
    {
        $totalEnergy = $this->getTotalEnergy();
        $daysSinceInstallation = $this->created_at->diffInDays(now());
        
        if ($daysSinceInstallation <= 0) {
            return 0;
        }
        
        return $totalEnergy / $daysSinceInstallation;
    }

    public function getPeakHourEnergy(): float
    {
        // Implementar lógica para calcular energía en horas pico
        return 0; // Placeholder
    }

    public function getOffPeakHourEnergy(): float
    {
        // Implementar lógica para calcular energía en horas valle
        return 0; // Placeholder
    }

    public function getEfficiencyRating(): float
    {
        // Calcular eficiencia basada en la precisión y comunicación
        $accuracyScore = match($this->accuracy_class) {
            self::ACCURACY_CLASS_A => 100,
            self::ACCURACY_CLASS_B => 90,
            self::ACCURACY_CLASS_C => 80,
            self::ACCURACY_CLASS_D => 70,
            default => 60,
        };

        $communicationScore = $this->isCommunicating() ? 100 : 50;
        $batteryScore = $this->battery_level;
        $signalScore = $this->signal_strength;

        return ($accuracyScore + $communicationScore + $batteryScore + $signalScore) / 4;
    }

    public function getFormattedTotalEnergy(): string
    {
        return number_format($this->getTotalEnergy(), 2) . ' kWh';
    }

    public function getFormattedDailyEnergy(): string
    {
        return number_format($this->getDailyEnergy(), 2) . ' kWh';
    }

    public function getFormattedMonthlyEnergy(): string
    {
        return number_format($this->getMonthlyEnergy(), 2) . ' kWh';
    }

    public function getFormattedYearlyEnergy(): string
    {
        return number_format($this->getYearlyEnergy(), 2) . ' kWh';
    }

    public function getFormattedAverageDailyEnergy(): string
    {
        return number_format($this->getAverageDailyEnergy(), 2) . ' kWh';
    }

    public function getFormattedBatteryLevel(): string
    {
        return number_format($this->battery_level, 1) . '%';
    }

    public function getFormattedSignalStrength(): string
    {
        return number_format($this->signal_strength, 1) . '%';
    }

    public function getFormattedEfficiencyRating(): string
    {
        return number_format($this->getEfficiencyRating(), 1) . '%';
    }

    public function getFormattedType(): string
    {
        return self::getTypes()[$this->type] ?? 'Desconocido';
    }

    public function getFormattedAccuracyClass(): string
    {
        return self::getAccuracyClasses()[$this->accuracy_class] ?? 'Desconocido';
    }

    public function getFormattedCommunicationProtocol(): string
    {
        return self::getCommunicationProtocols()[$this->communication_protocol] ?? 'Desconocido';
    }

    public function getStatusBadgeClass(): string
    {
        if (!$this->active) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->needsCalibration()) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        if ($this->maintenance_required) {
            return 'bg-orange-100 text-orange-800';
        }
        
        if ($this->isLowBattery()) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($this->hasWeakSignal()) {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        if (!$this->isCommunicating()) {
            return 'bg-gray-100 text-gray-800';
        }
        
        return 'bg-green-100 text-green-800';
    }

    public function getAccuracyBadgeClass(): string
    {
        return match($this->accuracy_class) {
            self::ACCURACY_CLASS_A => 'bg-green-100 text-green-800',
            self::ACCURACY_CLASS_B => 'bg-blue-100 text-blue-800',
            self::ACCURACY_CLASS_C => 'bg-yellow-100 text-yellow-800',
            self::ACCURACY_CLASS_D => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getBatteryBadgeClass(): string
    {
        if ($this->battery_level > 80) {
            return 'bg-green-100 text-green-800';
        } elseif ($this->battery_level > 50) {
            return 'bg-blue-100 text-blue-800';
        } elseif ($this->battery_level > 20) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }

    public function getSignalBadgeClass(): string
    {
        if ($this->signal_strength > 80) {
            return 'bg-green-100 text-green-800';
        } elseif ($this->signal_strength > 50) {
            return 'bg-blue-100 text-blue-800';
        } elseif ($this->signal_strength > 30) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }
}
