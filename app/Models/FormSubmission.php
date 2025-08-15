<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_name',
        'form_type',
        'form_data',
        'user_id',
        'name',
        'email',
        'status',
        'assigned_to_user_id',
        'reviewed_at',
        'processed_at',
        'processed_by_user_id',
        'source_page',
        'ip_address',
        'user_agent',
        'referrer',
        'metadata',
        'internal_notes',
    ];

    protected $casts = [
        'form_data' => 'array',
        'metadata' => 'array',
        'reviewed_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeByFormType($query, $type)
    {
        return $query->where('form_type', $type);
    }
}