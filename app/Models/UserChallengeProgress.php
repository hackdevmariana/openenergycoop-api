<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class UserChallengeProgress extends Model
{
    use HasFactory;

    protected $table = 'user_challenge_progress';

    protected $fillable = [
        'user_id',
        'challenge_id',
        'progress_kwh',
        'completed_at',
    ];

    protected $casts = [
        'progress_kwh' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    protected $dates = [
        'completed_at',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(EnergyChallenge::class, 'challenge_id');
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByChallenge($query, $challengeId)
    {
        return $query->where('challenge_id', $challengeId);
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeInProgress($query)
    {
        return $query->whereNull('completed_at');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Métodos de instancia
    public function isCompleted()
    {
        return !is_null($this->completed_at);
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->challenge) {
            return min(100, ($this->progress_kwh / $this->challenge->goal_kwh) * 100);
        }
        return 0;
    }

    public function getRemainingKwhAttribute()
    {
        if ($this->challenge) {
            return max(0, $this->challenge->goal_kwh - $this->progress_kwh);
        }
        return 0;
    }

    public function getDaysRemainingAttribute()
    {
        if ($this->challenge && !$this->isCompleted()) {
            $days = now()->diffInDays($this->challenge->ends_at, false);
            return max(0, $days);
        }
        return 0;
    }

    public function getEstimatedCompletionDateAttribute()
    {
        if ($this->challenge && !$this->isCompleted()) {
            $remainingKwh = $this->remaining_kwh;
            if ($remainingKwh > 0) {
                // Calcular basado en el progreso promedio diario
                $daysElapsed = now()->diffInDays($this->challenge->starts_at);
                if ($daysElapsed > 0) {
                    $averageDailyProgress = $this->progress_kwh / $daysElapsed;
                    if ($averageDailyProgress > 0) {
                        $daysToComplete = $remainingKwh / $averageDailyProgress;
                        return now()->addDays($daysToComplete);
                    }
                }
            }
        }
        return null;
    }

    // Métodos para actualizar progreso
    public function updateProgress($additionalKwh)
    {
        $this->progress_kwh += $additionalKwh;
        
        // Verificar si se completó el desafío
        if ($this->challenge && $this->progress_kwh >= $this->challenge->goal_kwh) {
            $this->complete();
        }
        
        $this->save();
        return $this;
    }

    public function complete()
    {
        if (!$this->isCompleted()) {
            $this->completed_at = now();
            $this->save();
        }
        return $this;
    }

    public function reset()
    {
        $this->progress_kwh = 0;
        $this->completed_at = null;
        $this->save();
        return $this;
    }

    // Métodos de búsqueda
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('challenge', function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Eventos
    protected static function boot()
    {
        parent::boot();

        static::created(function ($progress) {
            // Aquí se pueden agregar eventos cuando se crea un progreso
            // Por ejemplo, notificaciones, logs, etc.
        });

        static::updated(function ($progress) {
            // Aquí se pueden agregar eventos cuando se actualiza un progreso
            // Por ejemplo, notificaciones de logros, etc.
        });
    }
}
