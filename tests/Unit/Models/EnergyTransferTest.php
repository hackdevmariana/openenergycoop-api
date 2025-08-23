<?php

namespace Tests\Unit\Models;

use App\Models\EnergyTransfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnergyTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes()
    {
        $fillable = [
            'transfer_number', 'name', 'description', 'transfer_type', 'status',
            'priority', 'source_id', 'source_type', 'destination_id', 'destination_type',
            'source_meter_id', 'destination_meter_id', 'transfer_amount_kwh', 'transfer_amount_mwh',
            'transfer_rate_kw', 'transfer_rate_mw', 'transfer_unit', 'scheduled_start_time',
            'scheduled_end_time', 'actual_start_time', 'actual_end_time', 'completion_time',
            'duration_hours', 'efficiency_percentage', 'loss_percentage', 'loss_amount_kwh',
            'net_transfer_amount_kwh', 'net_transfer_amount_mwh', 'cost_per_kwh', 'total_cost',
            'currency', 'exchange_rate', 'transfer_method', 'transfer_medium', 'transfer_protocol',
            'is_automated', 'requires_approval', 'is_approved', 'is_verified', 'transfer_conditions',
            'safety_requirements', 'quality_standards', 'transfer_parameters', 'monitoring_data',
            'alarm_settings', 'event_logs', 'performance_metrics', 'tags', 'scheduled_by',
            'initiated_by', 'approved_by', 'verified_by', 'completed_by', 'created_by', 'notes'
        ];

        $this->assertEquals($fillable, (new EnergyTransfer())->getFillable());
    }

    public function test_casts()
    {
        $casts = [
            'transfer_amount_kwh' => 'decimal:2',
            'transfer_amount_mwh' => 'decimal:2',
            'transfer_rate_kw' => 'decimal:2',
            'transfer_rate_mw' => 'decimal:2',
            'scheduled_start_time' => 'datetime',
            'scheduled_end_time' => 'datetime',
            'actual_start_time' => 'datetime',
            'actual_end_time' => 'datetime',
            'completion_time' => 'datetime',
            'duration_hours' => 'decimal:2',
            'efficiency_percentage' => 'decimal:2',
            'loss_percentage' => 'decimal:2',
            'loss_amount_kwh' => 'decimal:2',
            'net_transfer_amount_kwh' => 'decimal:2',
            'net_transfer_amount_mwh' => 'decimal:2',
            'cost_per_kwh' => 'decimal:4',
            'total_cost' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'is_automated' => 'boolean',
            'requires_approval' => 'boolean',
            'is_approved' => 'boolean',
            'is_verified' => 'boolean',
            'approved_at' => 'datetime',
            'verified_at' => 'datetime',
            'completed_at' => 'datetime',
            'transfer_parameters' => 'array',
            'monitoring_data' => 'array',
            'alarm_settings' => 'array',
            'event_logs' => 'array',
            'performance_metrics' => 'array',
            'tags' => 'array',
        ];

        $this->assertEquals($casts, (new EnergyTransfer())->getCasts());
    }

    public function test_static_enum_methods()
    {
        $this->assertIsArray(EnergyTransfer::getTransferTypes());
        $this->assertIsArray(EnergyTransfer::getStatuses());
        $this->assertIsArray(EnergyTransfer::getPriorities());
    }

    public function test_relationships()
    {
        $energyTransfer = EnergyTransfer::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, $energyTransfer->source());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, $energyTransfer->destination());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $energyTransfer->sourceMeter());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $energyTransfer->destinationMeter());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $energyTransfer->scheduledBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $energyTransfer->initiatedBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $energyTransfer->approvedBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $energyTransfer->verifiedBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $energyTransfer->completedBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $energyTransfer->createdBy());
    }

    public function test_scopes()
    {
        $energyTransfer = EnergyTransfer::factory()->create(['status' => 'pending']);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byStatus('pending'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byTransferType('generation'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byPriority('high'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::overdue());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::dueSoon());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::highPriority());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::automated());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::manual());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::requiresApproval());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::approved());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::verified());
    }

    public function test_boolean_status_checks()
    {
        $energyTransfer = EnergyTransfer::factory()->create(['status' => 'pending']);
        
        $this->assertTrue($energyTransfer->isPending());
        $this->assertFalse($energyTransfer->isScheduled());
        $this->assertFalse($energyTransfer->isInProgress());
        $this->assertFalse($energyTransfer->isCompleted());
        $this->assertFalse($energyTransfer->isCancelled());
        $this->assertFalse($energyTransfer->isFailed());
        $this->assertFalse($energyTransfer->isOnHold());
        $this->assertFalse($energyTransfer->isReversed());
    }

    public function test_priority_checks()
    {
        $energyTransfer = EnergyTransfer::factory()->create(['priority' => 'high']);
        
        $this->assertTrue($energyTransfer->isHighPriority());
        $this->assertFalse($energyTransfer->isLowPriority());
        $this->assertFalse($energyTransfer->isNormalPriority());
    }

    public function test_transfer_type_checks()
    {
        $energyTransfer = EnergyTransfer::factory()->create(['transfer_type' => 'generation']);
        
        $this->assertTrue($energyTransfer->isGeneration());
        $this->assertFalse($energyTransfer->isConsumption());
        $this->assertFalse($energyTransfer->isStorage());
        $this->assertFalse($energyTransfer->isGridImport());
        $this->assertFalse($energyTransfer->isGridExport());
        $this->assertFalse($energyTransfer->isPeerToPeer());
        $this->assertFalse($energyTransfer->isVirtual());
        $this->assertFalse($energyTransfer->isPhysical());
        $this->assertFalse($energyTransfer->isContractual());
    }

    public function test_operation_checks()
    {
        $energyTransfer = EnergyTransfer::factory()->create(['is_automated' => true]);
        
        $this->assertTrue($energyTransfer->isAutomated());
        $this->assertFalse($energyTransfer->isManual());
    }

    public function test_calculation_methods()
    {
        $energyTransfer = EnergyTransfer::factory()->create([
            'scheduled_start_time' => now(),
            'scheduled_end_time' => now()->addHours(5),
            'actual_start_time' => now()->addMinutes(30),
            'actual_end_time' => now()->addHours(4),
            'transfer_amount_kwh' => 1000.00,
            'loss_amount_kwh' => 50.00,
            'cost_per_kwh' => 0.15
        ]);

        $this->assertEquals(5, $energyTransfer->getTransferDuration());
        $this->assertEquals(3.5, $energyTransfer->getActualDuration());
        $this->assertEquals(950.00, $energyTransfer->getNetTransferAmountKwh());
        $this->assertEquals(0.95, $energyTransfer->getNetTransferAmountMwh());
        $this->assertEquals(150.00, $energyTransfer->getTotalCost());
        $this->assertEquals(5.0, $energyTransfer->getLossPercentage());
        $this->assertEquals(95.0, $energyTransfer->getEfficiencyPercentage());
    }

    public function test_formatting_methods()
    {
        $energyTransfer = EnergyTransfer::factory()->create([
            'transfer_amount_kwh' => 1234.56,
            'currency' => 'EUR',
            'efficiency_percentage' => 95.5,
            'loss_percentage' => 4.5
        ]);

        $this->assertStringContainsString('1.234,56', $energyTransfer->getFormattedTransferAmountKwh());
        $this->assertStringContainsString('kWh', $energyTransfer->getFormattedTransferAmountKwh());
        $this->assertStringContainsString('95,5%', $energyTransfer->getFormattedEfficiencyPercentage());
        $this->assertStringContainsString('4,5%', $energyTransfer->getFormattedLossPercentage());
    }

    public function test_badge_classes()
    {
        $energyTransfer = EnergyTransfer::factory()->create(['status' => 'completed']);
        
        $this->assertStringContainsString('bg-green-100', $energyTransfer->getStatusBadgeClass());
        $this->assertStringContainsString('text-green-800', $energyTransfer->getStatusBadgeClass());
    }

    public function test_validation_methods()
    {
        $energyTransfer = EnergyTransfer::factory()->create([
            'transfer_amount_kwh' => 1000.00,
            'loss_amount_kwh' => 50.00,
            'net_transfer_amount_kwh' => 950.00
        ]);

        $this->assertTrue($energyTransfer->canBeCancelled());
        $this->assertTrue($energyTransfer->canBeStarted());
        $this->assertFalse($energyTransfer->canBeCompleted());
    }

    public function test_date_methods()
    {
        $energyTransfer = EnergyTransfer::factory()->create([
            'scheduled_start_time' => now()->addHours(2),
            'scheduled_end_time' => now()->addHours(5)
        ]);

        $this->assertEquals(2, $energyTransfer->getTimeToStart() / 3600);
        $this->assertEquals(5, $energyTransfer->getTimeToEnd() / 3600);
        $this->assertTrue($energyTransfer->isStartingSoon(3));
        $this->assertFalse($energyTransfer->isStartingSoon(1));
        $this->assertTrue($energyTransfer->isEndingSoon(6));
        $this->assertFalse($energyTransfer->isEndingSoon(2));
    }

    public function test_scheduled_status_checks()
    {
        $energyTransfer = EnergyTransfer::factory()->create([
            'scheduled_start_time' => now()->addHours(1)
        ]);

        $this->assertTrue($energyTransfer->isScheduledFor());
        $this->assertFalse($energyTransfer->isOverdue());
    }

    public function test_overdue_status_checks()
    {
        $energyTransfer = EnergyTransfer::factory()->create([
            'scheduled_end_time' => now()->subHours(1),
            'status' => 'in_progress'
        ]);

        $this->assertTrue($energyTransfer->isOverdue());
    }

    public function test_amount_range_scopes()
    {
        $energyTransfer = EnergyTransfer::factory()->create(['transfer_amount_kwh' => 500.00]);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byAmountRange(400, 600));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byCostRange(50, 100));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byEfficiencyRange(80, 100));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byLossRange(0, 10));
    }

    public function test_currency_scope()
    {
        $energyTransfer = EnergyTransfer::factory()->create(['currency' => 'EUR']);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byCurrency('EUR'));
    }

    public function test_source_destination_scopes()
    {
        $energyTransfer = EnergyTransfer::factory()->create([
            'source_id' => 1,
            'source_type' => 'App\\Models\\User',
            'destination_id' => 2,
            'destination_type' => 'App\\Models\\Company'
        ]);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::bySource(1, 'App\\Models\\User'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byDestination(2, 'App\\Models\\Company'));
    }

    public function test_meter_scopes()
    {
        $energyTransfer = EnergyTransfer::factory()->create(['source_meter_id' => 1]);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::bySourceMeter(1));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byDestinationMeter(2));
    }

    public function test_date_range_scopes()
    {
        $energyTransfer = EnergyTransfer::factory()->create([
            'scheduled_start_time' => now()->subDays(5),
            'scheduled_end_time' => now()->subDays(3)
        ]);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byDateRange(now()->subDays(10), now()));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byScheduledStartTime(now()->subDays(5)));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byScheduledEndTime(now()->subDays(3)));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byActualStartTime(now()->subDays(4)));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byActualEndTime(now()->subDays(2)));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, EnergyTransfer::byCompletionTime(now()->subDays(2)));
    }
}
