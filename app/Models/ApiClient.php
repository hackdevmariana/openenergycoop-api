<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ApiClient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'api_clients';

    protected $fillable = [
        'organization_id',
        'name',
        'token',
        'scopes',
        'last_used_at',
        'status',
        'allowed_ips',
        'callback_url',
        'expires_at',
        'revoked_at',
        'description',
        'rate_limits',
        'webhook_config',
        'permissions',
        'version',
        'metadata'
    ];

    protected $casts = [
        'scopes' => 'array',
        'allowed_ips' => 'array',
        'rate_limits' => 'array',
        'webhook_config' => 'array',
        'permissions' => 'array',
        'metadata' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $dates = [
        'last_used_at',
        'expires_at',
        'revoked_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Constantes para estados
    const STATUS_ACTIVE = 'active';
    const STATUS_REVOKED = 'revoked';
    const STATUS_SUSPENDED = 'suspended';

    // Constantes para scopes comunes
    const SCOPE_READ = 'read';
    const SCOPE_WRITE = 'write';
    const SCOPE_DELETE = 'delete';
    const SCOPE_ADMIN = 'admin';
    const SCOPE_USER = 'user';
    const SCOPE_ORGANIZATION = 'organization';
    const SCOPE_DEVICE = 'device';
    const SCOPE_METRICS = 'metrics';
    const SCOPE_BILLING = 'billing';
    const SCOPE_MAINTENANCE = 'maintenance';

    // Relaciones
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function apiCalls()
    {
        return $this->hasMany(ApiCall::class);
    }

    public function webhooks()
    {
        return $this->hasMany(Webhook::class);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    // Scopes
    public function scopeActive(Builder $query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeRevoked(Builder $query)
    {
        return $query->where('status', self::STATUS_REVOKED);
    }

    public function scopeSuspended(Builder $query)
    {
        return $query->where('status', self::STATUS_SUSPENDED);
    }

    public function scopeByOrganization(Builder $query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeByScope(Builder $query, $scope)
    {
        return $query->whereJsonContains('scopes', $scope);
    }

    public function scopeByVersion(Builder $query, $version)
    {
        return $query->where('version', $version);
    }

    public function scopeExpired(Builder $query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeNotExpired(Builder $query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeByIpAddress(Builder $query, $ipAddress)
    {
        return $query->where(function ($q) use ($ipAddress) {
            $q->whereNull('allowed_ips')
              ->orWhereJsonContains('allowed_ips', $ipAddress);
        });
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('token', 'like', "%{$search}%");
        });
    }

    public function scopeWithRecentActivity(Builder $query, $days = 30)
    {
        return $query->where('last_used_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_REVOKED => 'Revocado',
            self::STATUS_SUSPENDED => 'Suspendido',
            default => 'Desconocido'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_REVOKED => 'danger',
            self::STATUS_SUSPENDED => 'warning',
            default => 'secondary'
        };
    }

    public function getIsActiveAttribute()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getIsRevokedAttribute()
    {
        return $this->status === self::STATUS_REVOKED;
    }

    public function getIsSuspendedAttribute()
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function getIsExpiredAttribute()
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expires_at) {
            return null;
        }

        return now()->diffInDays($this->expires_at, false);
    }

    public function getFormattedExpiresAtAttribute()
    {
        if (!$this->expires_at) {
            return 'Sin expiración';
        }

        return $this->expires_at->format('d/m/Y H:i');
    }

    public function getFormattedLastUsedAtAttribute()
    {
        if (!$this->last_used_at) {
            return 'Nunca';
        }

        return $this->last_used_at->diffForHumans();
    }

    public function getFormattedRevokedAtAttribute()
    {
        if (!$this->revoked_at) {
            return 'N/A';
        }

        return $this->revoked_at->format('d/m/Y H:i');
    }

    public function getTokenPreviewAttribute()
    {
        if (!$this->token) {
            return 'N/A';
        }

        return substr($this->token, 0, 8) . '...' . substr($this->token, -8);
    }

    public function getScopesLabelAttribute()
    {
        if (!$this->scopes) {
            return 'Sin permisos';
        }

        $scopeLabels = [
            self::SCOPE_READ => 'Lectura',
            self::SCOPE_WRITE => 'Escritura',
            self::SCOPE_DELETE => 'Eliminación',
            self::SCOPE_ADMIN => 'Administración',
            self::SCOPE_USER => 'Usuario',
            self::SCOPE_ORGANIZATION => 'Organización',
            self::SCOPE_DEVICE => 'Dispositivos',
            self::SCOPE_METRICS => 'Métricas',
            self::SCOPE_BILLING => 'Facturación',
            self::SCOPE_MAINTENANCE => 'Mantenimiento'
        ];

        $labels = [];
        foreach ($this->scopes as $scope) {
            $labels[] = $scopeLabels[$scope] ?? $scope;
        }

        return implode(', ', $labels);
    }

    public function getVersionLabelAttribute()
    {
        return "v{$this->version}";
    }

    // Métodos
    public function generateToken()
    {
        $token = Str::random(64);
        $this->update(['token' => $token]);
        
        return $token;
    }

    public function regenerateToken()
    {
        return $this->generateToken();
    }

    public function revoke()
    {
        $this->update([
            'status' => self::STATUS_REVOKED,
            'revoked_at' => now()
        ]);
    }

    public function suspend()
    {
        $this->update([
            'status' => self::STATUS_SUSPENDED
        ]);
    }

    public function activate()
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'revoked_at' => null
        ]);
    }

    public function updateLastUsed()
    {
        $this->update(['last_used_at' => now()]);
    }

    public function hasScope($scope)
    {
        if (!$this->scopes) {
            return false;
        }

        return in_array($scope, $this->scopes);
    }

    public function hasAnyScope($scopes)
    {
        if (!$this->scopes) {
            return false;
        }

        return !empty(array_intersect($scopes, $this->scopes));
    }

    public function hasAllScopes($scopes)
    {
        if (!$this->scopes) {
            return false;
        }

        return empty(array_diff($scopes, $this->scopes));
    }

    public function addScope($scope)
    {
        $scopes = $this->scopes ?? [];
        
        if (!in_array($scope, $scopes)) {
            $scopes[] = $scope;
            $this->update(['scopes' => $scopes]);
        }
    }

    public function removeScope($scope)
    {
        $scopes = $this->scopes ?? [];
        
        $scopes = array_filter($scopes, fn($s) => $s !== $scope);
        
        $this->update(['scopes' => array_values($scopes)]);
    }

    public function isIpAllowed($ipAddress)
    {
        if (!$this->allowed_ips) {
            return true; // Sin restricciones de IP
        }

        return in_array($ipAddress, $this->allowed_ips);
    }

    public function addAllowedIp($ipAddress)
    {
        $allowedIps = $this->allowed_ips ?? [];
        
        if (!in_array($ipAddress, $allowedIps)) {
            $allowedIps[] = $ipAddress;
            $this->update(['allowed_ips' => $allowedIps]);
        }
    }

    public function removeAllowedIp($ipAddress)
    {
        $allowedIps = $this->allowed_ips ?? [];
        
        $allowedIps = array_filter($allowedIps, fn($ip) => $ip !== $ipAddress);
        
        $this->update(['allowed_ips' => array_values($allowedIps)]);
    }

    public function getRateLimit($endpoint = null)
    {
        if (!$this->rate_limits) {
            return null;
        }

        if ($endpoint) {
            return data_get($this->rate_limits, $endpoint);
        }

        return $this->rate_limits;
    }

    public function setRateLimit($endpoint, $limit)
    {
        $rateLimits = $this->rate_limits ?? [];
        data_set($rateLimits, $endpoint, $limit);
        
        $this->update(['rate_limits' => $rateLimits]);
    }

    public function getWebhookConfig($key = null)
    {
        if (!$this->webhook_config) {
            return null;
        }

        if ($key) {
            return data_get($this->webhook_config, $key);
        }

        return $this->webhook_config;
    }

    public function setWebhookConfig($key, $value)
    {
        $webhookConfig = $this->webhook_config ?? [];
        data_set($webhookConfig, $key, $value);
        
        $this->update(['webhook_config' => $webhookConfig]);
    }

    public function getPermission($key, $default = null)
    {
        if (!$this->permissions) {
            return $default;
        }

        return data_get($this->permissions, $key, $default);
    }

    public function setPermission($key, $value)
    {
        $permissions = $this->permissions ?? [];
        data_set($permissions, $key, $value);
        
        $this->update(['permissions' => $permissions]);
    }

    public function getMetadataValue($key, $default = null)
    {
        if (!$this->metadata) {
            return $default;
        }

        return data_get($this->metadata, $key, $default);
    }

    public function setMetadataValue($key, $value)
    {
        $metadata = $this->metadata ?? [];
        data_set($metadata, $key, $value);
        
        $this->update(['metadata' => $metadata]);
    }

    public function canAccessEndpoint($endpoint)
    {
        // Verificar si el cliente tiene acceso al endpoint
        $endpointPermissions = $this->getPermission('endpoints', []);
        
        if (empty($endpointPermissions)) {
            return true; // Sin restricciones específicas
        }

        return in_array($endpoint, $endpointPermissions);
    }

    public function canPerformAction($action)
    {
        // Verificar si el cliente puede realizar la acción
        $actionPermissions = $this->getPermission('actions', []);
        
        if (empty($actionPermissions)) {
            return true; // Sin restricciones específicas
        }

        return in_array($action, $actionPermissions);
    }

    public function getAvailableScopes()
    {
        return [
            self::SCOPE_READ => 'Lectura - Acceso de solo lectura a datos',
            self::SCOPE_WRITE => 'Escritura - Crear y actualizar datos',
            self::SCOPE_DELETE => 'Eliminación - Eliminar datos',
            self::SCOPE_ADMIN => 'Administración - Acceso completo al sistema',
            self::SCOPE_USER => 'Usuario - Gestión de usuarios',
            self::SCOPE_ORGANIZATION => 'Organización - Gestión de organizaciones',
            self::SCOPE_DEVICE => 'Dispositivos - Gestión de dispositivos IoT',
            self::SCOPE_METRICS => 'Métricas - Acceso a métricas y estadísticas',
            self::SCOPE_BILLING => 'Facturación - Gestión de facturación',
            self::SCOPE_MAINTENANCE => 'Mantenimiento - Gestión de mantenimiento'
        ];
    }

    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ACTIVE => 'Activo - Cliente funcionando normalmente',
            self::STATUS_SUSPENDED => 'Suspendido - Cliente temporalmente deshabilitado',
            self::STATUS_REVOKED => 'Revocado - Cliente permanentemente deshabilitado'
        ];
    }

    public function getDefaultRateLimits()
    {
        return [
            'default' => [
                'requests' => 1000,
                'window' => 3600 // 1 hora
            ],
            'auth' => [
                'requests' => 100,
                'window' => 3600
            ],
            'metrics' => [
                'requests' => 500,
                'window' => 3600
            ],
            'devices' => [
                'requests' => 2000,
                'window' => 3600
            ]
        ];
    }

    public function getDefaultWebhookConfig()
    {
        return [
            'enabled' => false,
            'url' => null,
            'events' => [],
            'secret' => null,
            'retry_attempts' => 3,
            'timeout' => 30
        ];
    }

    // Eventos
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            if (!$client->token) {
                $client->token = Str::random(64);
            }
            
            if (!$client->version) {
                $client->version = '1.0';
            }
            
            if (!$client->status) {
                $client->status = self::STATUS_ACTIVE;
            }
        });

        static::updating(function ($client) {
            if ($client->isDirty('status') && $client->status === self::STATUS_REVOKED) {
                $client->revoked_at = now();
            }
        });
    }
}
