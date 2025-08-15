<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'question',
        'answer',
        'short_answer',
        'slug',
        'position',
        'view_count',
        'helpful_count',
        'not_helpful_count',
        'is_featured',
        'keywords',
        'priority',
        'show_in_search',
        'organization_id',
        'language',
        'is_draft',
        'published_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_featured' => 'boolean',
        'show_in_search' => 'boolean',
        'is_draft' => 'boolean',
        'published_at' => 'datetime',
    ];

    // Relaciones
    public function topic(): BelongsTo
    {
        return $this->belongsTo(FaqTopic::class, 'topic_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_draft', false);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByTopic($query, $topicId)
    {
        return $query->where('topic_id', $topicId);
    }

    public function scopeInLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    // Accessors
    public function getHelpfulRateAttribute()
    {
        $total = $this->helpful_count + $this->not_helpful_count;
        if ($total == 0) return 0;
        return round(($this->helpful_count / $total) * 100, 1);
    }
}