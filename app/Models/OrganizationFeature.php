<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrganizationFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'feature_key',
        'enabled_dashboard',
        'enabled_web',
        'notes',
    ];

    protected $casts = [
        'enabled_dashboard' => 'boolean',
        'enabled_web' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
