<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\Cacheable;
use App\Contracts\Publishable;
use App\Contracts\Multilingual;
use App\Traits\HasCaching;
use App\Traits\HasPublishing;
use App\Traits\HasMultilingual;
use App\Traits\HasOrganization;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Document extends Model implements HasMedia, Cacheable, Publishable, Multilingual
{
    use HasFactory, 
        InteractsWithMedia, 
        HasCaching, 
        HasPublishing, 
        HasMultilingual, 
        HasOrganization;

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'checksum',
        'visible',
        'category_id',
        'uploaded_by',
        'uploaded_at',
        'download_count',
        'number_of_views',
        'version',
        'expires_at',
        'requires_auth',
        'allowed_roles',
        'thumbnail_path',
        'language',
        'organization_id',
        'is_draft',
        'published_at',
        'search_keywords',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'visible' => 'boolean',
        'uploaded_at' => 'datetime',
        'expires_at' => 'datetime',
        'requires_auth' => 'boolean',
        'allowed_roles' => 'array',
        'is_draft' => 'boolean',
        'published_at' => 'datetime',
        'search_keywords' => 'array',
        'download_count' => 'integer',
        'number_of_views' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * File types
     */
    public const FILE_TYPES = [
        'pdf' => 'PDF',
        'doc' => 'Word Document',
        'docx' => 'Word Document',
        'xls' => 'Excel Spreadsheet',
        'xlsx' => 'Excel Spreadsheet',
        'ppt' => 'PowerPoint Presentation',
        'pptx' => 'PowerPoint Presentation',
        'txt' => 'Text File',
        'zip' => 'ZIP Archive',
        'rar' => 'RAR Archive',
        'jpg' => 'JPEG Image',
        'jpeg' => 'JPEG Image',
        'png' => 'PNG Image',
        'gif' => 'GIF Image',
        'mp4' => 'MP4 Video',
        'mp3' => 'MP3 Audio',
    ];

    /**
     * Relationships
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function seoMetaData(): MorphOne
    {
        return $this->morphOne(SeoMetaData::class, 'seoable');
    }

    /**
     * Scopes
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('visible', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('file_type', $type);
    }

    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }

    public function scopeExpiringWithin(Builder $query, int $days): Builder
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays($days));
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->orderBy('download_count', 'desc');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('uploaded_at', 'desc');
    }

    /**
     * Media Collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')
            ->singleFile()
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain',
                'image/jpeg',
                'image/png',
            ]);

        $this->addMediaCollection('thumbnails')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10)
            ->performOnCollections('documents');

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600)
            ->quality(85)
            ->performOnCollections('documents');
    }

    /**
     * Business Logic Methods
     */
    public function canBePublished(): bool
    {
        if (empty($this->title)) {
            return false;
        }

        if (!$this->hasMedia('documents') && empty($this->file_path)) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expires_at && 
               $this->expires_at->isFuture() && 
               $this->expires_at->lte(now()->addDays($days));
    }

    public function canBeDownloadedBy(?User $user = null): bool
    {
        if (!$this->visible || $this->isExpired()) {
            return false;
        }

        if (!$this->requires_auth) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if (empty($this->allowed_roles)) {
            return true; // Any authenticated user
        }

        return $user->hasAnyRole($this->allowed_roles);
    }

    public function incrementDownloads(): void
    {
        $this->increment('download_count');
    }

    public function incrementViews(): void
    {
        $this->increment('number_of_views');
    }

    public function getDownloadUrl(): string
    {
        if ($this->hasMedia('documents')) {
            return $this->getFirstMediaUrl('documents');
        }
        
        return $this->file_path ? asset($this->file_path) : '#';
    }

    public function getSecureDownloadUrl(): string
    {
        return route('documents.download', ['document' => $this->id]);
    }

    public function getThumbnailUrl(): ?string
    {
        if ($this->hasMedia('thumbnails')) {
            return $this->getFirstMediaUrl('thumbnails');
        }

        if ($this->hasMedia('documents')) {
            return $this->getFirstMediaUrl('documents', 'thumb');
        }
        
        return $this->thumbnail_path;
    }

    public function getFileTypeLabel(): string
    {
        return self::FILE_TYPES[$this->file_type] ?? strtoupper($this->file_type);
    }

    public function getFileTypeIcon(): string
    {
        return match($this->file_type) {
            'pdf' => 'fa-file-pdf',
            'doc', 'docx' => 'fa-file-word',
            'xls', 'xlsx' => 'fa-file-excel',
            'ppt', 'pptx' => 'fa-file-powerpoint',
            'zip', 'rar' => 'fa-file-archive',
            'jpg', 'jpeg', 'png', 'gif' => 'fa-file-image',
            'mp4' => 'fa-file-video',
            'mp3' => 'fa-file-audio',
            'txt' => 'fa-file-alt',
            default => 'fa-file',
        };
    }

    public function getFormattedFileSize(): string
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getUrl(): string
    {
        return route('documents.show', ['document' => $this->id]);
    }

    public function duplicate(): self
    {
        $duplicate = $this->replicate();
        $duplicate->title = $this->title . ' (Copia)';
        $duplicate->download_count = 0;
        $duplicate->number_of_views = 0;
        $duplicate->is_draft = true;
        $duplicate->published_at = null;
        $duplicate->save();

        // Duplicate media
        foreach ($this->media as $media) {
            $media->copy($duplicate, $media->collection_name);
        }

        return $duplicate;
    }

    public function generateChecksum(): string
    {
        if ($this->hasMedia('documents')) {
            $filePath = $this->getFirstMedia('documents')->getPath();
            return hash_file('sha256', $filePath);
        }

        if ($this->file_path && file_exists($this->file_path)) {
            return hash_file('sha256', $this->file_path);
        }

        return '';
    }

    public function verifyIntegrity(): bool
    {
        if (!$this->checksum) {
            return true; // No checksum to verify
        }

        return $this->generateChecksum() === $this->checksum;
    }

    /**
     * Boot method
     */
    protected static function boot(): void
    {
        parent::boot();
        
        // Auto-set file info when media is added
        static::saved(function ($document) {
            if ($document->hasMedia('documents') && !$document->file_size) {
                $media = $document->getFirstMedia('documents');
                $document->update([
                    'file_size' => $media->size,
                    'mime_type' => $media->mime_type,
                    'file_type' => $media->getExtensionAttribute(),
                    'checksum' => hash_file('sha256', $media->getPath()),
                ]);
            }
        });
    }
}