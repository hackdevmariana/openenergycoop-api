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
        'position',
        'views_count',
        'helpful_count',
        'not_helpful_count',
        'is_featured',
        'tags',
        'organization_id',
        'language',
        'is_draft',
        'published_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_featured' => 'boolean',
        'is_draft' => 'boolean',
        'published_at' => 'datetime',
        'position' => 'integer',
        'views_count' => 'integer',
        'helpful_count' => 'integer',
        'not_helpful_count' => 'integer',
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
        return $query->where('is_draft', false)
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
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

    public function scopeOrderedByPosition($query)
    {
        return $query->orderBy('position', 'asc')->orderBy('created_at', 'desc');
    }

    // Business Logic Methods
    public function isPublished(): bool
    {
        return !$this->is_draft && 
               $this->published_at !== null && 
               $this->published_at <= now();
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function markAsHelpful(): void
    {
        $this->increment('helpful_count');
    }

    public function markAsNotHelpful(): void
    {
        $this->increment('not_helpful_count');
    }

    // Accessors
    public function getHelpfulRateAttribute()
    {
        $total = $this->helpful_count + $this->not_helpful_count;
        if ($total == 0) return 0;
        return round(($this->helpful_count / $total) * 100, 1);
    }

    public function getReadableAnswerAttribute()
    {
        return strip_tags($this->answer);
    }

    public function getShortAnswerAttribute()
    {
        return \Str::limit(strip_tags($this->answer), 150);
    }

    /**
     * Boot method
     */
    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($faq) {
            if (is_null($faq->published_at) && !$faq->is_draft) {
                $faq->published_at = now();
            }
        });
    }
}