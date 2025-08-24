<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EnergyChallenge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'type',
        'goal_kwh',
        'starts_at',
        'ends_at',
        'reward_type',
        'reward_details',
        'is_active',
    ];

    protected $casts = [
        'goal_kwh' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'reward_details' => 'array',
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'starts_at',
        'ends_at',
        'deleted_at',
    ];

    // Relaciones
    public function userProgress()
    {
        return $this->hasMany(UserChallengeProgress::class, 'challenge_id');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'user_challenge_progress', 'challenge_id', 'user_id')
                    ->withPivot('progress_kwh', 'completed_at')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeIndividual($query)
    {
        return $query->where('type', 'individual');
    }

    public function scopeColectivo($query)
    {
        return $query->where('type', 'colectivo');
    }

    public function scopeCurrent($query)
    {
        $now = now();
        return $query->where('starts_at', '<=', $now)
                    ->where('ends_at', '>=', $now);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('ends_at', '<', now());
    }

    public function scopeByRewardType($query, $rewardType)
    {
        return $query->where('reward_type', $rewardType);
    }

    // Métodos de instancia
    public function isActive()
    {
        return $this->is_active && 
               $this->starts_at <= now() && 
               $this->ends_at >= now();
    }

    public function hasStarted()
    {
        return $this->starts_at <= now();
    }

    public function hasEnded()
    {
        return $this->ends_at < now();
    }

    public function getStatusAttribute()
    {
        if ($this->hasEnded()) {
            return 'completed';
        }
        if ($this->isActive()) {
            return 'active';
        }
        if ($this->hasStarted()) {
            return 'draft';
        }
        return 'upcoming';
    }

    public function getStatusLabelAttribute()
    {
        $statusLabels = [
            'completed' => 'Completado',
            'active' => 'Activo',
            'draft' => 'Borrador',
            'upcoming' => 'Próximo',
        ];
        
        return $statusLabels[$this->status] ?? 'Desconocido';
    }

    public function getDurationAttribute()
    {
        $start = Carbon::parse($this->starts_at);
        $end = Carbon::parse($this->ends_at);
        $days = $start->diffInDays($end);
        
        if ($days == 0) {
            return '1 día';
        }
        
        return $days . ' días';
    }

    public function getTimeUntilStartAttribute()
    {
        if ($this->hasStarted()) {
            return 'Ya comenzó';
        }
        
        $days = now()->diffInDays($this->starts_at);
        if ($days == 0) {
            return 'Comienza hoy';
        }
        
        return "Comienza en {$days} días";
    }

    public function getTimeUntilEndAttribute()
    {
        if ($this->hasEnded()) {
            return 'Ya terminó';
        }
        
        $days = now()->diffInDays($this->ends_at);
        if ($days == 0) {
            return 'Termina hoy';
        }
        
        return "Termina en {$days} días";
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->type === 'individual') {
            return 0; // Para desafíos individuales, el progreso se calcula por usuario
        }
        
        $totalProgress = $this->userProgress()->sum('progress_kwh');
        return min(100, ($totalProgress / $this->goal_kwh) * 100);
    }

    public function getTotalParticipantsAttribute()
    {
        return $this->userProgress()->count();
    }

    public function getAverageProgressAttribute()
    {
        $participants = $this->userProgress()->count();
        if ($participants === 0) {
            return 0;
        }
        
        $totalProgress = $this->userProgress()->sum('progress_kwh');
        return $totalProgress / $participants;
    }

    // Métodos de búsqueda
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Validaciones
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($challenge) {
            if ($challenge->ends_at <= $challenge->starts_at) {
                throw new \InvalidArgumentException('La fecha de fin debe ser posterior a la fecha de inicio');
            }
        });
    }
}
