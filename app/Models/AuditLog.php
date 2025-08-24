<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'actor_type',
        'actor_identifier',
        'action',
        'description',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'request_data',
        'response_data',
        'response_code',
        'session_id',
        'request_id',
        'metadata'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'request_data' => 'array',
        'response_data' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    // Constantes para tipos de actor
    const ACTOR_TYPE_USER = 'user';
    const ACTOR_TYPE_SYSTEM = 'system';
    const ACTOR_TYPE_API = 'api';
    const ACTOR_TYPE_CRON = 'cron';
    const ACTOR_TYPE_WEBHOOK = 'webhook';

    // Constantes para acciones comunes
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_RESTORE = 'restore';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_LOGIN_FAILED = 'login_failed';
    const ACTION_PASSWORD_CHANGE = 'password_change';
    const ACTION_PERMISSION_CHANGE = 'permission_change';
    const ACTION_API_CALL = 'api_call';
    const ACTION_FILE_UPLOAD = 'file_upload';
    const ACTION_FILE_DELETE = 'file_delete';
    const ACTION_EXPORT = 'export';
    const ACTION_IMPORT = 'import';
    const ACTION_BULK_ACTION = 'bulk_action';

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    public function actor()
    {
        return $this->morphTo('actor', 'actor_type', 'actor_identifier');
    }

    // Scopes
    public function scopeByUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByActor(Builder $query, $actorType, $actorIdentifier = null)
    {
        $query->where('actor_type', $actorType);
        
        if ($actorIdentifier) {
            $query->where('actor_identifier', $actorIdentifier);
        }
        
        return $query;
    }

    public function scopeByAction(Builder $query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByAuditable(Builder $query, $auditableType, $auditableId = null)
    {
        $query->where('auditable_type', $auditableType);
        
        if ($auditableId) {
            $query->where('auditable_id', $auditableId);
        }
        
        return $query;
    }

    public function scopeByIpAddress(Builder $query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    public function scopeBySession(Builder $query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeByDateRange(Builder $query, $startDate, $endDate = null)
    {
        $query->where('created_at', '>=', $startDate);
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query;
    }

    public function scopeRecent(Builder $query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeToday(Builder $query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek(Builder $query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth(Builder $query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    public function scopeThisYear(Builder $query)
    {
        return $query->whereYear('created_at', now()->year);
    }

    public function scopeByMethod(Builder $query, $method)
    {
        return $query->where('method', strtoupper($method));
    }

    public function scopeByResponseCode(Builder $query, $code)
    {
        return $query->where('response_code', $code);
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('action', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('url', 'like', "%{$search}%")
              ->orWhere('ip_address', 'like', "%{$search}%");
        });
    }

    public function scopeWithMetadata(Builder $query, $key, $value = null)
    {
        if ($value === null) {
            return $query->whereJsonContains('metadata', [$key => $value]);
        }
        
        return $query->whereJsonContains('metadata', [$key => $value]);
    }

    // Accessors
    public function getActorLabelAttribute()
    {
        return match($this->actor_type) {
            self::ACTOR_TYPE_USER => 'Usuario',
            self::ACTOR_TYPE_SYSTEM => 'Sistema',
            self::ACTOR_TYPE_API => 'API',
            self::ACTOR_TYPE_CRON => 'Tarea Programada',
            self::ACTOR_TYPE_WEBHOOK => 'Webhook',
            default => 'Desconocido'
        };
    }

    public function getActionLabelAttribute()
    {
        return match($this->action) {
            self::ACTION_CREATE => 'Crear',
            self::ACTION_UPDATE => 'Actualizar',
            self::ACTION_DELETE => 'Eliminar',
            self::ACTION_RESTORE => 'Restaurar',
            self::ACTION_LOGIN => 'Iniciar Sesión',
            self::ACTION_LOGOUT => 'Cerrar Sesión',
            self::ACTION_LOGIN_FAILED => 'Error de Inicio de Sesión',
            self::ACTION_PASSWORD_CHANGE => 'Cambio de Contraseña',
            self::ACTION_PERMISSION_CHANGE => 'Cambio de Permisos',
            self::ACTION_API_CALL => 'Llamada API',
            self::ACTION_FILE_UPLOAD => 'Subida de Archivo',
            self::ACTION_FILE_DELETE => 'Eliminación de Archivo',
            self::ACTION_EXPORT => 'Exportar',
            self::ACTION_IMPORT => 'Importar',
            self::ACTION_BULK_ACTION => 'Acción Masiva',
            default => ucfirst($this->action)
        };
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d/m/Y H:i:s');
    }

    public function getFormattedIpAddressAttribute()
    {
        if (!$this->ip_address) {
            return 'N/A';
        }

        return $this->ip_address;
    }

    public function getFormattedUserAgentAttribute()
    {
        if (!$this->user_agent) {
            return 'N/A';
        }

        // Extraer información básica del user agent
        $userAgent = $this->user_agent;
        
        if (preg_match('/Chrome\/(\d+)/', $userAgent, $matches)) {
            return "Chrome {$matches[1]}";
        }
        
        if (preg_match('/Firefox\/(\d+)/', $userAgent, $matches)) {
            return "Firefox {$matches[1]}";
        }
        
        if (preg_match('/Safari\/(\d+)/', $userAgent, $matches)) {
            return "Safari {$matches[1]}";
        }
        
        if (preg_match('/Edge\/(\d+)/', $userAgent, $matches)) {
            return "Edge {$matches[1]}";
        }
        
        return 'Navegador Desconocido';
    }

    public function getHasChangesAttribute()
    {
        return !empty($this->old_values) || !empty($this->new_values);
    }

    public function getChangesSummaryAttribute()
    {
        if (!$this->has_changes) {
            return 'Sin cambios';
        }

        $changes = [];
        
        if ($this->old_values) {
            $changes[] = 'Valores anteriores: ' . count($this->old_values) . ' campos';
        }
        
        if ($this->new_values) {
            $changes[] = 'Valores nuevos: ' . count($this->new_values) . ' campos';
        }
        
        return implode(', ', $changes);
    }

    public function getIsSuccessfulAttribute()
    {
        if (!$this->response_code) {
            return true; // Sin código de respuesta, asumir éxito
        }
        
        return $this->response_code >= 200 && $this->response_code < 300;
    }

    public function getResponseStatusAttribute()
    {
        if (!$this->response_code) {
            return 'success';
        }
        
        if ($this->response_code >= 200 && $this->response_code < 300) {
            return 'success';
        }
        
        if ($this->response_code >= 400 && $this->response_code < 500) {
            return 'warning';
        }
        
        if ($this->response_code >= 500) {
            return 'danger';
        }
        
        return 'info';
    }

    // Métodos estáticos
    public static function log($action, $description = null, $options = [])
    {
        $data = array_merge([
            'action' => $action,
            'description' => $description,
            'user_id' => auth()->id(),
            'actor_type' => self::ACTOR_TYPE_USER,
            'actor_identifier' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'session_id' => session()->getId(),
            'request_id' => uniqid(),
            'created_at' => now()
        ], $options);

        return static::create($data);
    }

    public static function logUserAction($action, $description = null, $options = [])
    {
        $options['actor_type'] = self::ACTOR_TYPE_USER;
        $options['actor_identifier'] = auth()->id();
        
        return static::log($action, $description, $options);
    }

    public static function logSystemAction($action, $description = null, $options = [])
    {
        $options['actor_type'] = self::ACTOR_TYPE_SYSTEM;
        $options['actor_identifier'] = 'system';
        
        return static::log($action, $description, $options);
    }

    public static function logApiAction($action, $description = null, $options = [])
    {
        $options['actor_type'] = self::ACTOR_TYPE_API;
        $options['actor_identifier'] = $options['api_client_id'] ?? 'unknown';
        
        return static::log($action, $description, $options);
    }

    public static function logModelChange($model, $action, $description = null, $options = [])
    {
        $options['auditable_type'] = get_class($model);
        $options['auditable_id'] = $model->id;
        
        if ($action === self::ACTION_UPDATE) {
            $options['old_values'] = $model->getOriginal();
            $options['new_values'] = $model->getDirty();
        }
        
        return static::log($action, $description, $options);
    }

    public static function logLogin($user, $ipAddress = null, $userAgent = null)
    {
        return static::log(self::ACTION_LOGIN, "Usuario {$user->email} inició sesión", [
            'user_id' => $user->id,
            'actor_type' => self::ACTOR_TYPE_USER,
            'actor_identifier' => $user->id,
            'ip_address' => $ipAddress ?: request()->ip(),
            'user_agent' => $userAgent ?: request()->userAgent(),
            'auditable_type' => User::class,
            'auditable_id' => $user->id
        ]);
    }

    public static function logLoginFailed($email, $ipAddress = null, $userAgent = null)
    {
        return static::log(self::ACTION_LOGIN_FAILED, "Intento fallido de inicio de sesión para {$email}", [
            'ip_address' => $ipAddress ?: request()->ip(),
            'user_agent' => $userAgent ?: request()->userAgent(),
            'metadata' => ['email' => $email]
        ]);
    }

    public static function logLogout($user)
    {
        return static::log(self::ACTION_LOGOUT, "Usuario {$user->email} cerró sesión", [
            'user_id' => $user->id,
            'actor_type' => self::ACTOR_TYPE_USER,
            'actor_identifier' => $user->id,
            'auditable_type' => User::class,
            'auditable_id' => $user->id
        ]);
    }

    public static function logPasswordChange($user)
    {
        return static::log(self::ACTION_PASSWORD_CHANGE, "Usuario {$user->email} cambió su contraseña", [
            'user_id' => $user->id,
            'actor_type' => self::ACTOR_TYPE_USER,
            'actor_identifier' => $user->id,
            'auditable_type' => User::class,
            'auditable_id' => $user->id
        ]);
    }

    // Métodos de instancia
    public function getChangedFields()
    {
        if (!$this->has_changes) {
            return [];
        }

        $oldValues = $this->old_values ?? [];
        $newValues = $this->new_values ?? [];
        
        $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
        $changes = [];
        
        foreach ($allKeys as $key) {
            $oldValue = $oldValues[$key] ?? null;
            $newValue = $newValues[$key] ?? null;
            
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }
        
        return $changes;
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

    public function getRequestValue($key, $default = null)
    {
        if (!$this->request_data) {
            return $default;
        }

        return data_get($this->request_data, $key, $default);
    }

    public function getResponseValue($key, $default = null)
    {
        if (!$this->response_data) {
            return $default;
        }

        return data_get($this->response_data, $key, $default);
    }

    // Eventos
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            if (!$log->request_id) {
                $log->request_id = uniqid();
            }
        });
    }
}
