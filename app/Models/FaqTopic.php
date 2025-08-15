<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaqTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'sort_order',
        'is_active',
        'organization_id',
        'language',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'topic_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInLanguage($query, $language)
    {
        return $query->where('language', $language);
    }
}