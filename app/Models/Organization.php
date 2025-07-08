<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
    use HasFactory;

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

    public function appSettings()
    {
        return $this->hasOne(AppSetting::class);
    }

    public function features()
    {
        return $this->hasMany(OrganizationFeature::class);
    }
}
