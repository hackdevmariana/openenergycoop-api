<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\Cacheable;
use App\Traits\HasCaching;

class Comment extends Model implements Cacheable
{
    use HasFactory, HasCaching;

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'author_name',
        'author_email',
        'content',
        'status',
        'parent_id',
        'ip_address',
        'user_agent',
        'likes_count',
        'dislikes_count',
        'is_pinned',
        'approved_at',
        'approved_by_user_id',
    ];

    protected $casts = [
        'likes_count' => 'integer',
        'dislikes_count' => 'integer',
        'is_pinned' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Comment statuses
     */
    public const STATUSES = [
        'pending' => 'Pendiente',
        'approved' => 'Aprobado',
        'rejected' => 'Rechazado',
        'spam' => 'Spam',
    ];

    /**
     * Relationships
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->orderBy('created_at');
    }

    public function approvedReplies(): HasMany
    {
        return $this->replies()->where('status', 'approved');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    /**
     * Scopes
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeRootComments(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeReplies(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->orderBy('likes_count', 'desc');
    }

    /**
     * Business Logic Methods
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isSpam(): bool
    {
        return $this->status === 'spam';
    }

    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    public function hasReplies(): bool
    {
        return $this->replies()->exists();
    }

    public function approve(User $approver = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by_user_id' => $approver?->id,
        ]);
    }

    public function reject(): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by_user_id' => null,
        ]);
    }

    public function markAsSpam(): void
    {
        $this->update([
            'status' => 'spam',
            'approved_at' => null,
            'approved_by_user_id' => null,
        ]);
    }

    public function pin(): void
    {
        $this->update(['is_pinned' => true]);
    }

    public function unpin(): void
    {
        $this->update(['is_pinned' => false]);
    }

    public function like(): void
    {
        $this->increment('likes_count');
    }

    public function dislike(): void
    {
        $this->increment('dislikes_count');
    }

    public function getAuthorName(): string
    {
        if ($this->user) {
            return $this->user->name;
        }
        
        return $this->author_name ?: 'Anónimo';
    }

    public function getAuthorEmail(): ?string
    {
        if ($this->user) {
            return $this->user->email;
        }
        
        return $this->author_email;
    }

    public function getAuthorAvatar(): ?string
    {
        if ($this->user && method_exists($this->user, 'getAvatarUrl')) {
            return $this->user->getAvatarUrl();
        }
        
        // Generate Gravatar URL
        if ($email = $this->getAuthorEmail()) {
            $hash = md5(strtolower(trim($email)));
            return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=40";
        }
        
        return null;
    }

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            'spam' => 'gray',
            default => 'gray',
        };
    }

    public function getDepth(): int
    {
        $depth = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        
        return $depth;
    }

    public function getAllReplies(): \Illuminate\Database\Eloquent\Collection
    {
        $replies = collect();
        
        foreach ($this->replies as $reply) {
            $replies->push($reply);
            $replies = $replies->merge($reply->getAllReplies());
        }
        
        return $replies;
    }

    public function getThreadRoot(): Comment
    {
        $root = $this;
        
        while ($root->parent) {
            $root = $root->parent;
        }
        
        return $root;
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'pending' && !empty($this->content);
    }

    public function canBeRejected(): bool
    {
        return in_array($this->status, ['pending', 'approved']);
    }

    public function getExcerpt(int $length = 100): string
    {
        return \Str::limit(strip_tags($this->content), $length);
    }

    /**
     * Boot method
     */
    protected static function boot(): void
    {
        parent::boot();
        
        // Auto-detect spam comments
        static::creating(function ($comment) {
            if ($comment->isLikelySpam()) {
                $comment->status = 'spam';
            }
        });
    }

    /**
     * Simple spam detection
     */
    private function isLikelySpam(): bool
    {
        $content = strtolower($this->content);
        
        $spamWords = ['viagra', 'casino', 'lottery', 'winner', 'congratulations', 'click here'];
        
        foreach ($spamWords as $word) {
            if (str_contains($content, $word)) {
                return true;
            }
        }
        
        // Check for excessive links
        if (substr_count($content, 'http') > 2) {
            return true;
        }
        
        return false;
    }
}