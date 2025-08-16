<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasOrganization;

class SocialLink extends Model
{
    use HasFactory, HasOrganization;

    protected $fillable = [
        'platform',
        'url',
        'icon',
        'css_class',
        'color',
        'order',
        'is_active',
        'followers_count',
        'organization_id',
        'is_draft',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_draft' => 'boolean',
        'followers_count' => 'integer',
        'order' => 'integer',
    ];

    public const PLATFORMS = [
        'facebook' => 'Facebook',
        'twitter' => 'Twitter',
        'instagram' => 'Instagram',
        'linkedin' => 'LinkedIn',
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok',
        'telegram' => 'Telegram',
        'whatsapp' => 'WhatsApp',
        'github' => 'GitHub',
        'discord' => 'Discord',
        'other' => 'Otro',
    ];

    public const PLATFORM_COLORS = [
        'facebook' => '#1877F2',
        'twitter' => '#1DA1F2',
        'instagram' => '#E4405F',
        'linkedin' => '#0A66C2',
        'youtube' => '#FF0000',
        'tiktok' => '#000000',
        'telegram' => '#0088CC',
        'whatsapp' => '#25D366',
        'github' => '#181717',
        'discord' => '#5865F2',
        'other' => '#6C757D',
    ];

    // Relationships
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_draft', false);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('platform', 'asc');
    }

    public function scopePopular($query)
    {
        return $query->whereNotNull('followers_count')
                     ->orderBy('followers_count', 'desc');
    }

    // Business Logic Methods
    public function isPublished(): bool
    {
        return !$this->is_draft;
    }

    public function getPlatformLabel(): string
    {
        return self::PLATFORMS[$this->platform] ?? $this->platform;
    }

    public function getPlatformColor(): string
    {
        return $this->color ?? self::PLATFORM_COLORS[$this->platform] ?? '#6C757D';
    }

    public function getPlatformIcon(): string
    {
        if ($this->icon) {
            return $this->icon;
        }

        // Default icons based on platform
        $icons = [
            'facebook' => 'fab fa-facebook-f',
            'twitter' => 'fab fa-twitter',
            'instagram' => 'fab fa-instagram',
            'linkedin' => 'fab fa-linkedin-in',
            'youtube' => 'fab fa-youtube',
            'tiktok' => 'fab fa-tiktok',
            'telegram' => 'fab fa-telegram-plane',
            'whatsapp' => 'fab fa-whatsapp',
            'github' => 'fab fa-github',
            'discord' => 'fab fa-discord',
        ];

        return $icons[$this->platform] ?? 'fas fa-link';
    }

    public function getFormattedFollowersCount(): string
    {
        if (!$this->followers_count) {
            return '0';
        }

        if ($this->followers_count >= 1000000) {
            return number_format($this->followers_count / 1000000, 1) . 'M';
        }

        if ($this->followers_count >= 1000) {
            return number_format($this->followers_count / 1000, 1) . 'K';
        }

        return number_format($this->followers_count);
    }

    public function isVerified(): bool
    {
        // Logic to determine if the social link is verified
        // This could be based on followers count or other criteria
        return $this->followers_count >= 10000;
    }

    public function getCssClass(): string
    {
        return $this->css_class ?? "social-link-{$this->platform}";
    }

    /**
     * Boot method
     */
    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($socialLink) {
            // Auto-set platform color if not provided
            if (!$socialLink->color && isset(self::PLATFORM_COLORS[$socialLink->platform])) {
                $socialLink->color = self::PLATFORM_COLORS[$socialLink->platform];
            }

            // Auto-set CSS class if not provided
            if (!$socialLink->css_class) {
                $socialLink->css_class = "social-link-{$socialLink->platform}";
            }
        });
    }
}
