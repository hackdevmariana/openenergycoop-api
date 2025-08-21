<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Milestone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'target_value',
        'current_value',
        'reward_type',
        'reward_payload',
        'is_repeatable',
        'is_active',
        'name',
        'description',
        'organization_id',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'reward_payload' => 'array',
        'is_repeatable' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Enums
    const TYPE_SALES_COUNT = 'sales_count';
    const TYPE_DONATION_AMOUNT = 'donation_amount';
    const TYPE_CUSTOM_EVENT = 'custom_event';
    const TYPE_ENERGY_PRODUCTION = 'energy_production';
    const TYPE_USER_REGISTRATION = 'user_registration';

    const REWARD_TYPE_ENERGY_BOND = 'energy_bond';
    const REWARD_TYPE_DISCOUNT_CODE = 'discount_code';
    const REWARD_TYPE_WALLET_CREDIT = 'wallet_credit';
    const REWARD_TYPE_ACHIEVEMENT = 'achievement';

    public static function getTypes(): array
    {
        return [
            self::TYPE_SALES_COUNT => 'Conteo de Ventas',
            self::TYPE_DONATION_AMOUNT => 'Monto de Donaciones',
            self::TYPE_CUSTOM_EVENT => 'Evento Personalizado',
            self::TYPE_ENERGY_PRODUCTION => 'Producción de Energía',
            self::TYPE_USER_REGISTRATION => 'Registro de Usuarios',
        ];
    }

    public static function getRewardTypes(): array
    {
        return [
            self::REWARD_TYPE_ENERGY_BOND => 'Bono Energético',
            self::REWARD_TYPE_DISCOUNT_CODE => 'Código de Descuento',
            self::REWARD_TYPE_WALLET_CREDIT => 'Crédito en Wallet',
            self::REWARD_TYPE_ACHIEVEMENT => 'Logro',
        ];
    }

    // Relaciones
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRepeatable($query)
    {
        return $query->where('is_repeatable', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Métodos
    public function isCompleted(): bool
    {
        return $this->current_value >= $this->target_value;
    }

    public function getProgressPercentage(): float
    {
        if ($this->target_value <= 0) {
            return 0;
        }
        
        return min(100, ($this->current_value / $this->target_value) * 100);
    }

    public function getRemainingValue(): float
    {
        return max(0, $this->target_value - $this->current_value);
    }

    public function incrementProgress(float $value): bool
    {
        $this->current_value += $value;
        
        if ($this->isCompleted()) {
            $this->triggerReward();
            
            if ($this->is_repeatable) {
                $this->current_value = 0; // Reset para siguiente ciclo
            }
        }
        
        return $this->save();
    }

    protected function triggerReward(): void
    {
        // Aquí se implementaría la lógica para otorgar la recompensa
        // Dependiendo del reward_type y reward_payload
        event(new \App\Events\MilestoneReached($this));
    }

    public function getFormattedTargetValue(): string
    {
        switch ($this->type) {
            case self::TYPE_SALES_COUNT:
                return number_format($this->target_value) . ' ventas';
            case self::TYPE_DONATION_AMOUNT:
                return '€' . number_format($this->target_value, 2);
            case self::TYPE_ENERGY_PRODUCTION:
                return number_format($this->target_value, 2) . ' kWh';
            default:
                return number_format($this->target_value);
        }
    }
}
