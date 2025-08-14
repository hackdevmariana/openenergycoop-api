<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cif',
        'contact_person',
        'company_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener las solicitudes de suscripción de esta empresa
     */
    public function subscriptionRequests(): HasMany
    {
        return $this->hasMany(SubscriptionRequest::class, 'user_id');
    }

    /**
     * Obtener los usuarios asociados a esta empresa
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'company_id');
    }

    /**
     * Buscar empresa por CIF
     */
    public static function findByCif(string $cif): ?self
    {
        return static::where('cif', $cif)->first();
    }

    /**
     * Buscar empresas por nombre
     */
    public static function searchByName(string $name): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('name', 'like', "%{$name}%")->get();
    }
}
