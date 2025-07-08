<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'organization_id',
        'name',
        'slogan',
        'primary_color',
        'secondary_color',
        'locale',
        'custom_js',
    ];

    protected $casts = [
        'organization_id' => 'integer',
        'primary_color' => 'string',
        'secondary_color' => 'string',
        'locale' => 'string',
        'custom_js' => 'string',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('favicon')->singleFile();
    }

    protected static function booted(): void
    {
        static::saved(fn() => Cache::forget('app.settings'));
        static::deleted(fn() => Cache::forget('app.settings'));
    }
}
