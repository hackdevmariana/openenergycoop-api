<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Taggable extends Model
{
    use HasFactory;

    protected $fillable = [
        'tag_id',
        'taggable_id',
        'taggable_type',
        'weight',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relaciones
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeByTag($query, int $tagId)
    {
        return $query->where('tag_id', $tagId);
    }

    public function scopeByWeight($query, float $minWeight = 1.0)
    {
        return $query->where('weight', '>=', $minWeight);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('weight', 'desc');
    }

    // Métodos de negocio
    public function updateWeight(float $weight): void
    {
        $this->update(['weight' => max(0, min(10, $weight))]);
    }

    public function increaseWeight(float $amount = 0.1): void
    {
        $newWeight = min(10, $this->weight + $amount);
        $this->update(['weight' => $newWeight]);
    }

    public function decreaseWeight(float $amount = 0.1): void
    {
        $newWeight = max(0, $this->weight - $amount);
        $this->update(['weight' => $newWeight]);
    }
}