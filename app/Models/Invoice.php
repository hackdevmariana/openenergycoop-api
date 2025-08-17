<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'invoice_code', 'type', 'status', 'user_id', 'energy_cooperative_id', 'parent_invoice_id',
        'issue_date', 'due_date', 'service_period_start', 'service_period_end', 'sent_at', 'viewed_at', 'paid_at',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'paid_amount', 'pending_amount', 'currency',
        'tax_rate', 'tax_number', 'tax_type',
        'customer_name', 'customer_email', 'billing_address', 'customer_tax_id',
        'company_name', 'company_address', 'company_tax_id', 'company_email',
        'line_items', 'description', 'notes', 'terms_and_conditions',
        'energy_consumption_kwh', 'energy_production_kwh', 'energy_price_per_kwh', 'meter_reading_start', 'meter_reading_end',
        'payment_methods', 'payment_terms', 'grace_period_days',
        'is_recurring', 'recurring_frequency', 'next_billing_date', 'recurring_count', 'max_recurring_count',
        'pdf_path', 'pdf_url', 'attachments',
        'created_by_id', 'customer_ip', 'view_count', 'last_viewed_at', 'activity_log',
        'metadata', 'language', 'template', 'is_test'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'service_period_start' => 'date',
        'service_period_end' => 'date',
        'next_billing_date' => 'date',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'paid_at' => 'datetime',
        'last_viewed_at' => 'datetime',
        'subtotal' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'discount_amount' => 'decimal:4',
        'total_amount' => 'decimal:4',
        'paid_amount' => 'decimal:4',
        'pending_amount' => 'decimal:4',
        'tax_rate' => 'decimal:4',
        'energy_consumption_kwh' => 'decimal:4',
        'energy_production_kwh' => 'decimal:4',
        'energy_price_per_kwh' => 'decimal:6',
        'grace_period_days' => 'integer',
        'recurring_count' => 'integer',
        'max_recurring_count' => 'integer',
        'view_count' => 'integer',
        'line_items' => 'array',
        'payment_methods' => 'array',
        'attachments' => 'array',
        'activity_log' => 'array',
        'metadata' => 'array',
        'is_recurring' => 'boolean',
        'is_test' => 'boolean',
    ];

    // Relaciones

    /**
     * Usuario/cliente de la factura
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cooperativa energética
     */
    public function energyCooperative(): BelongsTo
    {
        return $this->belongsTo(EnergyCooperative::class);
    }

    /**
     * Usuario que creó la factura
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Factura padre (para credit notes)
     */
    public function parentInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'parent_invoice_id');
    }

    /**
     * Facturas hijas (credit notes, etc.)
     */
    public function childInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'parent_invoice_id');
    }

    /**
     * Pagos asociados a la factura
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Transacciones asociadas
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Reembolsos asociados
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    // Scopes

    /**
     * Scope para facturas pagadas
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope para facturas pendientes
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'sent', 'viewed']);
    }

    /**
     * Scope para facturas vencidas
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
                    ->orWhere(function ($q) {
                        $q->whereIn('status', ['sent', 'viewed'])
                          ->where('due_date', '<', now());
                    });
    }

    /**
     * Scope para facturas de un usuario
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para facturas recurrentes
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope para facturas por tipo
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para facturas de prueba
     */
    public function scopeTest($query)
    {
        return $query->where('is_test', true);
    }

    /**
     * Scope para facturas de producción
     */
    public function scopeProduction($query)
    {
        return $query->where('is_test', false);
    }

    // Métodos de ayuda

    /**
     * Verificar si la factura está pagada
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Verificar si la factura está pendiente
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['draft', 'sent', 'viewed']);
    }

    /**
     * Verificar si la factura está vencida
     */
    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || 
               (in_array($this->status, ['sent', 'viewed']) && $this->due_date < now());
    }

    /**
     * Verificar si está parcialmente pagada
     */
    public function isPartiallyPaid(): bool
    {
        return $this->status === 'partially_paid' || 
               ($this->paid_amount > 0 && $this->paid_amount < $this->total_amount);
    }

    /**
     * Verificar si es una factura recurrente
     */
    public function isRecurring(): bool
    {
        return $this->is_recurring;
    }

    /**
     * Obtener el balance pendiente
     */
    public function getBalance(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Verificar si la factura puede ser cancelada
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, ['draft', 'sent', 'viewed']) && $this->paid_amount == 0;
    }

    /**
     * Obtener días hasta vencimiento
     */
    public function getDaysUntilDue(): int
    {
        return max(0, now()->diffInDays($this->due_date, false));
    }

    /**
     * Obtener días de retraso
     */
    public function getDaysOverdue(): int
    {
        return $this->due_date < now() ? now()->diffInDays($this->due_date) : 0;
    }

    /**
     * Marcar como enviada
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }

    /**
     * Marcar como vista
     */
    public function markAsViewed(): void
    {
        $this->update([
            'status' => 'viewed',
            'viewed_at' => now(),
            'view_count' => $this->view_count + 1,
            'last_viewed_at' => now()
        ]);
    }

    /**
     * Marcar como pagada
     */
    public function markAsPaid(float $amount = null): void
    {
        $amount = $amount ?? $this->total_amount;
        
        $this->update([
            'status' => $amount >= $this->total_amount ? 'paid' : 'partially_paid',
            'paid_amount' => $amount,
            'pending_amount' => $this->total_amount - $amount,
            'paid_at' => $amount >= $this->total_amount ? now() : null
        ]);
    }

    /**
     * Obtener estado formateado
     */
    public function getFormattedStatus(): string
    {
        return match($this->status) {
            'draft' => 'Borrador',
            'sent' => 'Enviada',
            'viewed' => 'Vista',
            'overdue' => 'Vencida',
            'paid' => 'Pagada',
            'partially_paid' => 'Parcialmente Pagada',
            'cancelled' => 'Cancelada',
            'refunded' => 'Reembolsada',
            default => ucfirst($this->status)
        };
    }

    /**
     * Obtener tipo formateado
     */
    public function getFormattedType(): string
    {
        return match($this->type) {
            'standard' => 'Estándar',
            'proforma' => 'Proforma',
            'credit_note' => 'Nota de Crédito',
            'debit_note' => 'Nota de Débito',
            'recurring' => 'Recurrente',
            default => ucfirst($this->type)
        };
    }

    /**
     * Generar número de factura único
     */
    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $lastInvoice = self::where('invoice_number', 'like', "INV-{$year}-%")
                          ->orderBy('invoice_number', 'desc')
                          ->first();
        
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "INV-{$year}-" . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generar código único
     */
    public static function generateInvoiceCode(): string
    {
        do {
            $code = 'IC-' . date('Y') . '-' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('invoice_code', $code)->exists());

        return $code;
    }
}