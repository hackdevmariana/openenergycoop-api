<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DashboardView extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dashboard_views';

    protected $fillable = [
        'user_id',
        'name',
        'layout_json',
        'is_default',
        'theme',
        'color_scheme',
        'widget_settings',
        'is_public',
        'description',
        'access_permissions'
    ];

    protected $casts = [
        'layout_json' => 'array',
        'widget_settings' => 'array',
        'access_permissions' => 'array',
        'is_default' => 'boolean',
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Constantes para temas
    const THEME_DEFAULT = 'default';
    const THEME_DARK = 'dark';
    const THEME_LIGHT = 'light';
    const THEME_SOLAR = 'solar';
    const THEME_WIND = 'wind';

    // Constantes para esquemas de colores
    const COLOR_SCHEME_LIGHT = 'light';
    const COLOR_SCHEME_DARK = 'dark';
    const COLOR_SCHEME_AUTO = 'auto';

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function widgets()
    {
        return $this->hasMany(DashboardWidget::class);
    }

    public function sharedWith()
    {
        return $this->belongsToMany(User::class, 'dashboard_view_shares', 'dashboard_view_id', 'user_id')
                    ->withPivot('permissions', 'shared_at')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeByUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDefault(Builder $query)
    {
        return $query->where('is_default', true);
    }

    public function scopePublic(Builder $query)
    {
        return $query->where('is_public', true);
    }

    public function scopePrivate(Builder $query)
    {
        return $query->where('is_public', false);
    }

    public function scopeByTheme(Builder $query, $theme)
    {
        return $query->where('theme', $theme);
    }

    public function scopeByColorScheme(Builder $query, $colorScheme)
    {
        return $query->where('color_scheme', $colorScheme);
    }

    public function scopeSearch(Builder $query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeWithWidgets(Builder $query)
    {
        return $query->with(['widgets' => function ($q) {
            $q->orderBy('position');
        }]);
    }

    // Accessors
    public function getWidgetCountAttribute()
    {
        return $this->widgets()->count();
    }

    public function getVisibleWidgetCountAttribute()
    {
        return $this->widgets()->where('visible', true)->count();
    }

    public function getThemeLabelAttribute()
    {
        return match($this->theme) {
            self::THEME_DEFAULT => 'Predeterminado',
            self::THEME_DARK => 'Oscuro',
            self::THEME_LIGHT => 'Claro',
            self::THEME_SOLAR => 'Solar',
            self::THEME_WIND => 'Eólico',
            default => 'Desconocido'
        };
    }

    public function getColorSchemeLabelAttribute()
    {
        return match($this->color_scheme) {
            self::COLOR_SCHEME_LIGHT => 'Claro',
            self::COLOR_SCHEME_DARK => 'Oscuro',
            self::COLOR_SCHEME_AUTO => 'Automático',
            default => 'Desconocido'
        };
    }

    public function getIsDefaultAttribute($value)
    {
        return (bool) $value;
    }

    public function getIsPublicAttribute($value)
    {
        return (bool) $value;
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at->format('d/m/Y H:i');
    }

    // Métodos
    public function setAsDefault()
    {
        // Desactivar otras vistas por defecto del usuario
        static::where('user_id', $this->user_id)
              ->where('id', '!=', $this->id)
              ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    public function addWidget($type, $position = null, $settings = [])
    {
        if ($position === null) {
            $position = $this->widgets()->max('position') + 1;
        }

        return $this->widgets()->create([
            'user_id' => $this->user_id,
            'type' => $type,
            'position' => $position,
            'settings_json' => $settings,
            'visible' => true
        ]);
    }

    public function removeWidget($widgetId)
    {
        $widget = $this->widgets()->find($widgetId);
        
        if ($widget) {
            $widget->delete();
            $this->reorderWidgets();
        }
    }

    public function reorderWidgets()
    {
        $widgets = $this->widgets()->orderBy('position')->get();
        
        foreach ($widgets as $index => $widget) {
            $widget->update(['position' => $index + 1]);
        }
    }

    public function getWidgetByPosition($position)
    {
        return $this->widgets()->where('position', $position)->first();
    }

    public function moveWidget($widgetId, $newPosition)
    {
        $widget = $this->widgets()->find($widgetId);
        
        if (!$widget) {
            return false;
        }

        $oldPosition = $widget->position;
        
        if ($oldPosition < $newPosition) {
            // Mover widgets hacia arriba
            $this->widgets()
                 ->where('position', '>', $oldPosition)
                 ->where('position', '<=', $newPosition)
                 ->decrement('position');
        } else {
            // Mover widgets hacia abajo
            $this->widgets()
                 ->where('position', '>=', $newPosition)
                 ->where('position', '<', $oldPosition)
                 ->increment('position');
        }

        $widget->update(['position' => $newPosition]);
        
        return true;
    }

    public function duplicate()
    {
        $newView = $this->replicate();
        $newView->name = $this->name . ' (Copia)';
        $newView->is_default = false;
        $newView->is_public = false;
        $newView->save();

        // Duplicar widgets
        foreach ($this->widgets as $widget) {
            $newWidget = $widget->replicate();
            $newWidget->dashboard_view_id = $newView->id;
            $newWidget->save();
        }

        return $newView;
    }

    public function shareWith($userId, $permissions = [])
    {
        $this->sharedWith()->attach($userId, [
            'permissions' => $permissions,
            'shared_at' => now()
        ]);
    }

    public function unshareWith($userId)
    {
        $this->sharedWith()->detach($userId);
    }

    public function isSharedWith($userId)
    {
        return $this->sharedWith()->where('user_id', $userId)->exists();
    }

    public function getSharedPermissions($userId)
    {
        $share = $this->sharedWith()->where('user_id', $userId)->first();
        
        return $share ? $share->pivot->permissions : [];
    }

    public function hasPermission($userId, $permission)
    {
        if ($this->user_id === $userId) {
            return true; // El propietario tiene todos los permisos
        }

        $permissions = $this->getSharedPermissions($userId);
        
        return in_array($permission, $permissions);
    }

    public function getLayoutModules()
    {
        if (!$this->layout_json) {
            return [];
        }

        return collect($this->layout_json)->sortBy('position')->values();
    }

    public function getModuleSettings($module)
    {
        $modules = $this->getLayoutModules();
        
        $moduleData = $modules->firstWhere('module', $module);
        
        return $moduleData['settings'] ?? [];
    }

    public function updateModuleSettings($module, $settings)
    {
        $layout = $this->layout_json ?? [];
        
        foreach ($layout as &$moduleData) {
            if ($moduleData['module'] === $module) {
                $moduleData['settings'] = array_merge(
                    $moduleData['settings'] ?? [],
                    $settings
                );
                break;
            }
        }

        $this->update(['layout_json' => $layout]);
    }

    public function getAvailableThemes()
    {
        return [
            self::THEME_DEFAULT => 'Predeterminado',
            self::THEME_DARK => 'Oscuro',
            self::THEME_LIGHT => 'Claro',
            self::THEME_SOLAR => 'Solar',
            self::THEME_WIND => 'Eólico'
        ];
    }

    public function getAvailableColorSchemes()
    {
        return [
            self::COLOR_SCHEME_LIGHT => 'Claro',
            self::COLOR_SCHEME_DARK => 'Oscuro',
            self::COLOR_SCHEME_AUTO => 'Automático'
        ];
    }

    public function getDefaultLayout()
    {
        return [
            [
                'module' => 'wallet',
                'position' => 1,
                'visible' => true,
                'settings' => ['currency' => 'EUR']
            ],
            [
                'module' => 'energy_production',
                'position' => 2,
                'visible' => true
            ],
            [
                'module' => 'events',
                'position' => 3,
                'visible' => true
            ],
            [
                'module' => 'devices',
                'position' => 4,
                'visible' => true
            ],
            [
                'module' => 'metrics',
                'position' => 5,
                'visible' => true
            ]
        ];
    }

    // Eventos
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($view) {
            if (!$view->layout_json) {
                $view->layout_json = $view->getDefaultLayout();
            }
        });

        static::deleting(function ($view) {
            // Eliminar widgets asociados
            $view->widgets()->delete();
            
            // Eliminar comparticiones
            $view->sharedWith()->detach();
        });
    }
}
