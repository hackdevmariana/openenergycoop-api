<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DashboardWidget extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dashboard_widgets';

    protected $fillable = [
        'user_id',
        'dashboard_view_id',
        'type',
        'title',
        'position',
        'settings_json',
        'visible',
        'collapsible',
        'collapsed',
        'size',
        'grid_position',
        'refresh_interval',
        'last_refresh',
        'data_source',
        'filters',
        'permissions'
    ];

    protected $casts = [
        'settings_json' => 'array',
        'grid_position' => 'array',
        'data_source' => 'array',
        'filters' => 'array',
        'permissions' => 'array',
        'visible' => 'boolean',
        'collapsible' => 'boolean',
        'collapsed' => 'boolean',
        'position' => 'integer',
        'refresh_interval' => 'integer',
        'last_refresh' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $dates = [
        'last_refresh',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Constantes para tipos de widgets
    const TYPE_WALLET = 'wallet';
    const TYPE_ENERGY_PRODUCTION = 'energy_production';
    const TYPE_ENERGY_CONSUMPTION = 'energy_consumption';
    const TYPE_DEVICES = 'devices';
    const TYPE_METRICS = 'metrics';
    const TYPE_EVENTS = 'events';
    const TYPE_NOTIFICATIONS = 'notifications';
    const TYPE_CHARTS = 'charts';
    const TYPE_CALENDAR = 'calendar';
    const TYPE_WEATHER = 'weather';
    const TYPE_NEWS = 'news';
    const TYPE_SOCIAL = 'social';
    const TYPE_ANALYTICS = 'analytics';
    const TYPE_MAINTENANCE = 'maintenance';
    const TYPE_BILLING = 'billing';
    const TYPE_SUPPORT = 'support';

    // Constantes para tamaños
    const SIZE_SMALL = 'small';
    const SIZE_MEDIUM = 'medium';
    const SIZE_LARGE = 'large';
    const SIZE_XLARGE = 'xlarge';

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dashboardView()
    {
        return $this->belongsTo(DashboardView::class);
    }

    public function widgetData()
    {
        return $this->hasMany(WidgetData::class);
    }

    public function widgetAlerts()
    {
        return $this->hasMany(WidgetAlert::class);
    }

    // Scopes
    public function scopeByUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType(Builder $query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeVisible(Builder $query)
    {
        return $query->where('visible', true);
    }

    public function scopeHidden(Builder $query)
    {
        return $query->where('visible', false);
    }

    public function scopeByDashboardView(Builder $query, $viewId)
    {
        return $query->where('dashboard_view_id', $viewId);
    }

    public function scopeBySize(Builder $query, $size)
    {
        return $query->where('size', $size);
    }

    public function scopeOrdered(Builder $query)
    {
        return $query->orderBy('position');
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('type', 'like', "%{$search}%");
        });
    }

    public function scopeWithData(Builder $query)
    {
        return $query->with(['widgetData' => function ($q) {
            $q->latest()->limit(100);
        }]);
    }

    // Accessors
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            self::TYPE_WALLET => 'Cartera',
            self::TYPE_ENERGY_PRODUCTION => 'Producción de Energía',
            self::TYPE_ENERGY_CONSUMPTION => 'Consumo de Energía',
            self::TYPE_DEVICES => 'Dispositivos',
            self::TYPE_METRICS => 'Métricas',
            self::TYPE_EVENTS => 'Eventos',
            self::TYPE_NOTIFICATIONS => 'Notificaciones',
            self::TYPE_CHARTS => 'Gráficos',
            self::TYPE_CALENDAR => 'Calendario',
            self::TYPE_WEATHER => 'Clima',
            self::TYPE_NEWS => 'Noticias',
            self::TYPE_SOCIAL => 'Social',
            self::TYPE_ANALYTICS => 'Analíticas',
            self::TYPE_MAINTENANCE => 'Mantenimiento',
            self::TYPE_BILLING => 'Facturación',
            self::TYPE_SUPPORT => 'Soporte',
            default => 'Desconocido'
        };
    }

    public function getSizeLabelAttribute()
    {
        return match($this->size) {
            self::SIZE_SMALL => 'Pequeño',
            self::SIZE_MEDIUM => 'Mediano',
            self::SIZE_LARGE => 'Grande',
            self::SIZE_XLARGE => 'Extra Grande',
            default => 'Desconocido'
        };
    }

    public function getIsVisibleAttribute()
    {
        return (bool) $this->visible;
    }

    public function getIsCollapsibleAttribute()
    {
        return (bool) $this->collapsible;
    }

    public function getIsCollapsedAttribute()
    {
        return (bool) $this->collapsed;
    }

    public function getFormattedLastRefreshAttribute()
    {
        if (!$this->last_refresh) {
            return 'Nunca';
        }

        return $this->last_refresh->diffForHumans();
    }

    public function getGridPositionAttribute($value)
    {
        if (!$value) {
            return [
                'x' => 0,
                'y' => 0,
                'width' => $this->getDefaultWidth(),
                'height' => $this->getDefaultHeight()
            ];
        }

        return $value;
    }

    public function getDefaultWidthAttribute()
    {
        return match($this->size) {
            self::SIZE_SMALL => 1,
            self::SIZE_MEDIUM => 2,
            self::SIZE_LARGE => 3,
            self::SIZE_XLARGE => 4,
            default => 2
        };
    }

    public function getDefaultHeightAttribute()
    {
        return match($this->size) {
            self::SIZE_SMALL => 1,
            self::SIZE_MEDIUM => 2,
            self::SIZE_LARGE => 3,
            self::SIZE_XLARGE => 4,
            default => 2
        };
    }

    public function getSettingAttribute($key, $default = null)
    {
        if (!$this->settings_json) {
            return $default;
        }

        return data_get($this->settings_json, $key, $default);
    }

    public function getFilterAttribute($key, $default = null)
    {
        if (!$this->filters) {
            return $default;
        }

        return data_get($this->filters, $key, $default);
    }

    public function getPermissionAttribute($key, $default = null)
    {
        if (!$this->permissions) {
            return $default;
        }

        return data_get($this->permissions, $key, $default);
    }

    // Métodos
    public function setSetting($key, $value)
    {
        $settings = $this->settings_json ?? [];
        data_set($settings, $key, $value);
        
        $this->update(['settings_json' => $settings]);
    }

    public function setFilter($key, $value)
    {
        $filters = $this->filters ?? [];
        data_set($filters, $key, $value);
        
        $this->update(['filters' => $filters]);
    }

    public function setPermission($key, $value)
    {
        $permissions = $this->permissions ?? [];
        data_set($permissions, $key, $value);
        
        $this->update(['permissions' => $permissions]);
    }

    public function show()
    {
        $this->update(['visible' => true]);
    }

    public function hide()
    {
        $this->update(['visible' => false]);
    }

    public function toggleVisibility()
    {
        $this->update(['visible' => !$this->visible]);
    }

    public function collapse()
    {
        if ($this->collapsible) {
            $this->update(['collapsed' => true]);
        }
    }

    public function expand()
    {
        if ($this->collapsible) {
            $this->update(['collapsed' => false]);
        }
    }

    public function toggleCollapse()
    {
        if ($this->collapsible) {
            $this->update(['collapsed' => !$this->collapsed]);
        }
    }

    public function moveToPosition($newPosition)
    {
        $oldPosition = $this->position;
        
        if ($oldPosition === $newPosition) {
            return;
        }

        if ($this->dashboard_view_id) {
            $this->dashboardView->moveWidget($this->id, $newPosition);
        } else {
            $this->update(['position' => $newPosition]);
        }
    }

    public function resize($width, $height)
    {
        $gridPosition = $this->grid_position;
        $gridPosition['width'] = $width;
        $gridPosition['height'] = $height;
        
        $this->update(['grid_position' => $gridPosition]);
    }

    public function moveToGrid($x, $y)
    {
        $gridPosition = $this->grid_position;
        $gridPosition['x'] = $x;
        $gridPosition['y'] = $y;
        
        $this->update(['grid_position' => $gridPosition]);
    }

    public function setRefreshInterval($seconds)
    {
        $this->update(['refresh_interval' => $seconds]);
    }

    public function refresh()
    {
        $this->update(['last_refresh' => now()]);
    }

    public function needsRefresh()
    {
        if (!$this->refresh_interval) {
            return false;
        }

        if (!$this->last_refresh) {
            return true;
        }

        return $this->last_refresh->addSeconds($this->refresh_interval)->isPast();
    }

    public function duplicate()
    {
        $newWidget = $this->replicate();
        $newWidget->title = $this->title . ' (Copia)';
        $newWidget->position = $this->dashboardView ? 
            $this->dashboardView->widgets()->max('position') + 1 : 
            $this->position + 1;
        $newWidget->save();

        return $newWidget;
    }

    public function getAvailableTypes()
    {
        return [
            self::TYPE_WALLET => 'Cartera',
            self::TYPE_ENERGY_PRODUCTION => 'Producción de Energía',
            self::TYPE_ENERGY_CONSUMPTION => 'Consumo de Energía',
            self::TYPE_DEVICES => 'Dispositivos',
            self::TYPE_METRICS => 'Métricas',
            self::TYPE_EVENTS => 'Eventos',
            self::TYPE_NOTIFICATIONS => 'Notificaciones',
            self::TYPE_CHARTS => 'Gráficos',
            self::TYPE_CALENDAR => 'Calendario',
            self::TYPE_WEATHER => 'Clima',
            self::TYPE_NEWS => 'Noticias',
            self::TYPE_SOCIAL => 'Social',
            self::TYPE_ANALYTICS => 'Analíticas',
            self::TYPE_MAINTENANCE => 'Mantenimiento',
            self::TYPE_BILLING => 'Facturación',
            self::TYPE_SUPPORT => 'Soporte'
        ];
    }

    public function getAvailableSizes()
    {
        return [
            self::SIZE_SMALL => 'Pequeño (1x1)',
            self::SIZE_MEDIUM => 'Mediano (2x2)',
            self::SIZE_LARGE => 'Grande (3x3)',
            self::SIZE_XLARGE => 'Extra Grande (4x4)'
        ];
    }

    public function getDefaultSettings()
    {
        return match($this->type) {
            self::TYPE_WALLET => ['currency' => 'EUR', 'show_balance' => true],
            self::TYPE_ENERGY_PRODUCTION => ['unit' => 'kWh', 'period' => 'day'],
            self::TYPE_ENERGY_CONSUMPTION => ['unit' => 'kWh', 'period' => 'day'],
            self::TYPE_DEVICES => ['show_status' => true, 'max_devices' => 5],
            self::TYPE_METRICS => ['refresh_interval' => 300, 'show_chart' => true],
            self::TYPE_EVENTS => ['max_events' => 10, 'show_time' => true],
            self::TYPE_NOTIFICATIONS => ['max_notifications' => 5, 'show_unread' => true],
            self::TYPE_CHARTS => ['chart_type' => 'line', 'data_points' => 24],
            self::TYPE_CALENDAR => ['view' => 'month', 'show_events' => true],
            self::TYPE_WEATHER => ['location' => 'auto', 'units' => 'metric'],
            self::TYPE_NEWS => ['category' => 'energy', 'max_news' => 5],
            self::TYPE_SOCIAL => ['platform' => 'all', 'max_posts' => 5],
            self::TYPE_ANALYTICS => ['metrics' => ['production', 'consumption'], 'period' => 'week'],
            self::TYPE_MAINTENANCE => ['show_upcoming' => true, 'max_tasks' => 5],
            self::TYPE_BILLING => ['show_balance' => true, 'show_history' => true],
            self::TYPE_SUPPORT => ['show_tickets' => true, 'max_tickets' => 5],
            default => []
        };
    }

    // Eventos
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($widget) {
            if (!$widget->settings_json) {
                $widget->settings_json = $widget->getDefaultSettings();
            }
        });

        static::deleting(function ($widget) {
            // Eliminar datos asociados
            $widget->widgetData()->delete();
            $widget->widgetAlerts()->delete();
        });
    }
}
