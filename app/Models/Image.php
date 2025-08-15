<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'alt_text',
        'filename',
        'path',
        'url',
        'mime_type',
        'file_size',
        'width',
        'height',
        'metadata',
        'category_id',
        'tags',
        'organization_id',
        'language',
        'is_public',
        'is_featured',
        'status',
        'seo_title',
        'seo_description',
        'responsive_urls',
        'download_count',
        'view_count',
        'last_used_at',
        'uploaded_by_user_id',
        'published_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'tags' => 'array',
        'responsive_urls' => 'array',
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
        'last_used_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($image) {
            if (empty($image->slug)) {
                $image->slug = Str::slug($image->title);
            }
        });

        static::updating(function ($image) {
            if ($image->isDirty('title') && empty($image->getOriginal('slug'))) {
                $image->slug = Str::slug($image->title);
            }
        });
    }

    // Relaciones
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    // Scopes
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory(Builder $query, $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByMimeType(Builder $query, string $mimeType): Builder
    {
        return $query->where('mime_type', 'like', $mimeType . '%');
    }

    public function scopeImages(Builder $query): Builder
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeInLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->whereFullText(['title', 'description', 'alt_text'], $search)
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
    }

    public function scopeRecentlyUsed(Builder $query, int $days = 30): Builder
    {
        return $query->where('last_used_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) return 'Desconocido';

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDimensionsAttribute(): ?string
    {
        if (!$this->width || !$this->height) return null;
        return "{$this->width} × {$this->height} px";
    }

    public function getAspectRatioAttribute(): ?float
    {
        if (!$this->width || !$this->height) return null;
        return round($this->width / $this->height, 2);
    }

    public function getIsLandscapeAttribute(): ?bool
    {
        if (!$this->width || !$this->height) return null;
        return $this->width > $this->height;
    }

    public function getIsPortraitAttribute(): ?bool
    {
        if (!$this->width || !$this->height) return null;
        return $this->height > $this->width;
    }

    public function getIsSquareAttribute(): ?bool
    {
        if (!$this->width || !$this->height) return null;
        return $this->width === $this->height;
    }

    // Métodos de utilidad
    public function getFullUrl(): string
    {
        if ($this->url) {
            return $this->url;
        }

        return Storage::url($this->path);
    }

    public function getThumbnailUrl(int $width = 300, int $height = 300): string
    {
        // Si tenemos URLs responsivas, usar la más apropiada
        if ($this->responsive_urls) {
            foreach ($this->responsive_urls as $size => $url) {
                if (Str::contains($size, $width . 'x' . $height)) {
                    return $url;
                }
            }
        }

        // Fallback a la URL original
        return $this->getFullUrl();
    }

    public function incrementDownloads(): bool
    {
        return $this->increment('download_count');
    }

    public function incrementViews(): bool
    {
        return $this->increment('view_count', 1, ['last_used_at' => now()]);
    }

    public function markAsUsed(): bool
    {
        return $this->update(['last_used_at' => now()]);
    }

    public function isImage(): bool
    {
        return Str::startsWith($this->mime_type, 'image/');
    }

    public function isVector(): bool
    {
        return in_array($this->mime_type, ['image/svg+xml']);
    }

    public function generateResponsiveVersions(array $sizes = [150, 300, 600, 1200]): array
    {
        $responsiveUrls = [];
        
        foreach ($sizes as $size) {
            // Aquí implementarías la generación de versiones responsivas
            // Por ahora, solo retornamos la URL original
            $responsiveUrls["{$size}x{$size}"] = $this->getFullUrl();
        }
        
        $this->update(['responsive_urls' => $responsiveUrls]);
        
        return $responsiveUrls;
    }

    public function duplicate(): self
    {
        $duplicate = $this->replicate();
        $duplicate->slug = $this->slug . '-copy-' . now()->timestamp;
        $duplicate->title = $this->title . ' (Copia)';
        $duplicate->is_featured = false;
        $duplicate->download_count = 0;
        $duplicate->view_count = 0;
        $duplicate->last_used_at = null;
        $duplicate->save();

        return $duplicate;
    }

    public function archive(): bool
    {
        return $this->update(['status' => 'archived']);
    }

    public function restore(): bool
    {
        return $this->update(['status' => 'active']);
    }

    public function softDelete(): bool
    {
        return $this->update(['status' => 'deleted']);
    }

    // Métodos estáticos de utilidad
    public static function getUsageStats(): array
    {
        return [
            'total' => self::count(),
            'active' => self::active()->count(),
            'public' => self::public()->count(),
            'featured' => self::featured()->count(),
            'total_size' => self::sum('file_size'),
            'total_downloads' => self::sum('download_count'),
            'total_views' => self::sum('view_count'),
        ];
    }

    public static function getMostUsed(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::active()
            ->public()
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get();
    }

    public static function getRecentUploads(int $days = 7): \Illuminate\Database\Eloquent\Collection
    {
        return self::active()
            ->where('created_at', '>=', now()->subDays($days))
            ->orderByDesc('created_at')
            ->get();
    }

    public static function cleanupUnused(int $daysOld = 365): int
    {
        return self::where('last_used_at', '<', now()->subDays($daysOld))
            ->where('view_count', 0)
            ->where('download_count', 0)
            ->update(['status' => 'archived']);
    }
}