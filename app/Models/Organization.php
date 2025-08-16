<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Organization extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'contact_email',
        'contact_phone',
        'primary_color',
        'secondary_color',
        'css_files',
        'active',
    ];

    protected $casts = [
        'css_files' => 'array',
        'active' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
    }

    public function appSettings()
    {
        return $this->hasOne(AppSetting::class);
    }

    public function features()
    {
        return $this->hasMany(OrganizationFeature::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function comments()
    {
        return $this->hasManyThrough(Comment::class, Article::class);
    }
}
