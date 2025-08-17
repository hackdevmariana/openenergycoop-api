<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_name',
        'country',
        'commodity_type',
        'product_name',
        'price_datetime',
        'price_date',
        'price_time',
        'period_type',
        'delivery_start_date',
        'delivery_end_date',
        'delivery_period',
        'price',
        'currency',
        'unit',
        'volume',
        'high_price',
        'low_price',
        'price_change_percentage',
        'volatility',
        'data_source',
        'market_status',
    ];

    protected $casts = [
        'price_datetime' => 'datetime',
        'price_date' => 'date',
        'delivery_start_date' => 'datetime',
        'delivery_end_date' => 'datetime',
        'price' => 'decimal:4',
        'volume' => 'decimal:2',
        'high_price' => 'decimal:4',
        'low_price' => 'decimal:4',
        'price_change_percentage' => 'decimal:2',
        'volatility' => 'decimal:2',
    ];
}
