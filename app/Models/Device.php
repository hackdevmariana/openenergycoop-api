<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Device extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'devices';

    protected $fillable = [
        'name',
        'type',
        'user_id',
        'consumption_point_id',
        'api_endpoint',
        'api_credentials',
        'device_config',
        'active',
        'model',
        'manufacturer',
        'serial_number',
        'firmware_version',
        'last_communication',
        'capabilities',
        'location',
        'notes'
    ];

    protected $casts = [
        'active' => 'boolean',
        'api_credentials' => 'encrypted:array',
        'device_config' => 'array',
        'capabilities' => 'array',
        'last_communication' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $dates = [
        'last_communication',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Constantes para tipos de dispositivos
    const TYPE_SMART_METER = 'smart_meter';
    const TYPE_BATTERY = 'battery';
    const TYPE_EV_CHARGER = 'ev_charger';
    const TYPE_SOLAR_PANEL = 'solar_panel';
    const TYPE_WIND_TURBINE = 'wind_turbine';
    const TYPE_HEAT_PUMP = 'heat_pump';
    const TYPE_THERMOSTAT = 'thermostat';
    const TYPE_SMART_PLUG = 'smart_plug';
    const TYPE_ENERGY_MONITOR = 'energy_monitor';
    const TYPE_GRID_CONNECTION = 'grid_connection';
    const TYPE_OTHER = 'other';

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function consumptionPoint()
    {
        return $this->belongsTo(ConsumptionPoint::class);
    }

    public function deviceReadings()
    {
        return $this->hasMany(DeviceReading::class);
    }

    public function deviceAlerts()
    {
        return $this->hasMany(DeviceAlert::class);
    }

    public function deviceMaintenance()
    {
        return $this->hasMany(DeviceMaintenance::class);
    }

    // Scopes
    public function scopeActive(Builder $query)
    {
        return $query->where('active', true);
    }

    public function scopeInactive(Builder $query)
    {
        return $query->where('active', false);
    }

    public function scopeByType(Builder $query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByManufacturer(Builder $query, $manufacturer)
    {
        return $query->where('manufacturer', $manufacturer);
    }

    public function scopeByModel(Builder $query, $model)
    {
        return $query->where('model', $model);
    }

    public function scopeOnline(Builder $query)
    {
        return $query->whereNotNull('last_communication')
                    ->where('last_communication', '>=', now()->subMinutes(5));
    }

    public function scopeOffline(Builder $query)
    {
        return $query->where(function ($q) {
            $q->whereNull('last_communication')
              ->orWhere('last_communication', '<', now()->subMinutes(5));
        });
    }

    public function scopeByLocation(Builder $query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('model', 'like', "%{$search}%")
              ->orWhere('manufacturer', 'like', "%{$search}%")
              ->orWhere('serial_number', 'like', "%{$search}%");
        });
    }

    public function scopeWithCapability(Builder $query, $capability)
    {
        return $query->whereJsonContains('capabilities', $capability);
    }

    // Accessors
    public function getStatusAttribute()
    {
        if (!$this->active) {
            return 'inactive';
        }

        if ($this->isOnline()) {
            return 'online';
        }

        return 'offline';
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'online' => 'success',
            'offline' => 'warning',
            'inactive' => 'danger',
            default => 'secondary'
        };
    }

    public function getFormattedLastCommunicationAttribute()
    {
        if (!$this->last_communication) {
            return 'Nunca';
        }

        return $this->last_communication->diffForHumans();
    }

    public function getIsOnlineAttribute()
    {
        return $this->isOnline();
    }

    public function getIsOfflineAttribute()
    {
        return $this->isOffline();
    }

    public function getDisplayNameAttribute()
    {
        if ($this->name) {
            return $this->name;
        }

        return "{$this->manufacturer} {$this->model}";
    }

    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            self::TYPE_SMART_METER => 'Contador Inteligente',
            self::TYPE_BATTERY => 'Batería',
            self::TYPE_EV_CHARGER => 'Cargador EV',
            self::TYPE_SOLAR_PANEL => 'Panel Solar',
            self::TYPE_WIND_TURBINE => 'Turbina Eólica',
            self::TYPE_HEAT_PUMP => 'Bomba de Calor',
            self::TYPE_THERMOSTAT => 'Termostato',
            self::TYPE_SMART_PLUG => 'Enchufe Inteligente',
            self::TYPE_ENERGY_MONITOR => 'Monitor de Energía',
            self::TYPE_GRID_CONNECTION => 'Conexión a Red',
            self::TYPE_OTHER => 'Otro',
            default => 'Desconocido'
        };
    }

    // Métodos
    public function isOnline()
    {
        if (!$this->last_communication) {
            return false;
        }

        return $this->last_communication->isAfter(now()->subMinutes(5));
    }

    public function isOffline()
    {
        return !$this->isOnline();
    }

    public function activate()
    {
        $this->update(['active' => true]);
    }

    public function deactivate()
    {
        $this->update(['active' => false]);
    }

    public function toggleActive()
    {
        $this->update(['active' => !$this->active]);
    }

    public function updateCommunication()
    {
        $this->update(['last_communication' => now()]);
    }

    public function hasCapability($capability)
    {
        if (!$this->capabilities) {
            return false;
        }

        return in_array($capability, $this->capabilities);
    }

    public function addCapability($capability)
    {
        $capabilities = $this->capabilities ?? [];
        
        if (!in_array($capability, $capabilities)) {
            $capabilities[] = $capability;
            $this->update(['capabilities' => $capabilities]);
        }
    }

    public function removeCapability($capability)
    {
        $capabilities = $this->capabilities ?? [];
        
        $capabilities = array_filter($capabilities, fn($cap) => $cap !== $capability);
        
        $this->update(['capabilities' => array_values($capabilities)]);
    }

    public function getConfig($key, $default = null)
    {
        if (!$this->device_config) {
            return $default;
        }

        return data_get($this->device_config, $key, $default);
    }

    public function setConfig($key, $value)
    {
        $config = $this->device_config ?? [];
        data_set($config, $key, $value);
        
        $this->update(['device_config' => $config]);
    }

    public function getApiCredential($key, $default = null)
    {
        if (!$this->api_credentials) {
            return $default;
        }

        return data_get($this->api_credentials, $key, $default);
    }

    public function setApiCredential($key, $value)
    {
        $credentials = $this->api_credentials ?? [];
        data_set($credentials, $key, $value);
        
        $this->update(['api_credentials' => $credentials]);
    }

    public function getAvailableTypes()
    {
        return [
            self::TYPE_SMART_METER => 'Contador Inteligente',
            self::TYPE_BATTERY => 'Batería',
            self::TYPE_EV_CHARGER => 'Cargador EV',
            self::TYPE_SOLAR_PANEL => 'Panel Solar',
            self::TYPE_WIND_TURBINE => 'Turbina Eólica',
            self::TYPE_HEAT_PUMP => 'Bomba de Calor',
            self::TYPE_THERMOSTAT => 'Termostato',
            self::TYPE_SMART_PLUG => 'Enchufe Inteligente',
            self::TYPE_ENERGY_MONITOR => 'Monitor de Energía',
            self::TYPE_GRID_CONNECTION => 'Conexión a Red',
            self::TYPE_OTHER => 'Otro'
        ];
    }

    public function getCapabilityOptions()
    {
        return [
            'energy_monitoring' => 'Monitoreo de Energía',
            'remote_control' => 'Control Remoto',
            'data_logging' => 'Registro de Datos',
            'alerts' => 'Alertas',
            'scheduling' => 'Programación',
            'integration' => 'Integración',
            'analytics' => 'Analíticas',
            'maintenance' => 'Mantenimiento'
        ];
    }

    // Eventos
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($device) {
            if (!$device->serial_number) {
                $device->serial_number = 'DEV-' . uniqid();
            }
        });

        static::updating(function ($device) {
            if ($device->isDirty('active') && !$device->active) {
                $device->last_communication = null;
            }
        });
    }
}
