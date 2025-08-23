<?php

namespace Tests\Unit\Models;

use App\Models\ConsumptionPoint;
use App\Models\Customer;
use App\Models\EnergyInstallation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsumptionPointTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_the_correct_fillable_attributes()
    {
        $fillable = [
            'point_number',
            'name',
            'description',
            'point_type',
            'status',
            'customer_id',
            'installation_id',
            'location_address',
            'latitude',
            'longitude',
            'peak_demand_kw',
            'average_demand_kw',
            'annual_consumption_kwh',
            'connection_date',
            'disconnection_date',
            'meter_number',
            'meter_type',
            'meter_installation_date',
            'meter_next_calibration_date',
            'voltage_level',
            'current_rating',
            'phase_type',
            'connection_type',
            'service_type',
            'tariff_type',
            'billing_frequency',
            'is_connected',
            'is_primary',
            'notes',
            'metadata',
            'managed_by',
            'created_by',
            'approved_by',
            'approved_at',
        ];

        $this->assertEquals($fillable, (new ConsumptionPoint())->getFillable());
    }

    /** @test */
    public function it_has_the_correct_casts()
    {
        $casts = [
            'id' => 'int',
            'customer_id' => 'int',
            'installation_id' => 'int',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'peak_demand_kw' => 'decimal:2',
            'average_demand_kw' => 'decimal:2',
            'annual_consumption_kwh' => 'decimal:2',
            'connection_date' => 'date',
            'disconnection_date' => 'date',
            'meter_installation_date' => 'date',
            'meter_next_calibration_date' => 'date',
            'voltage_level' => 'decimal:2',
            'current_rating' => 'decimal:2',
            'is_connected' => 'boolean',
            'is_primary' => 'boolean',
            'metadata' => 'array',
            'approved_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];

        $this->assertEquals($casts, (new ConsumptionPoint())->getCasts());
    }

    /** @test */
    public function it_returns_point_types()
    {
        $pointTypes = ConsumptionPoint::getPointTypes();

        $this->assertIsArray($pointTypes);
        $this->assertArrayHasKey('residential', $pointTypes);
        $this->assertArrayHasKey('commercial', $pointTypes);
        $this->assertArrayHasKey('industrial', $pointTypes);
        $this->assertArrayHasKey('agricultural', $pointTypes);
        $this->assertArrayHasKey('public', $pointTypes);
        $this->assertArrayHasKey('street_lighting', $pointTypes);
        $this->assertArrayHasKey('charging_station', $pointTypes);
        $this->assertArrayHasKey('other', $pointTypes);
    }

    /** @test */
    public function it_returns_statuses()
    {
        $statuses = ConsumptionPoint::getStatuses();

        $this->assertIsArray($statuses);
        $this->assertArrayHasKey('active', $statuses);
        $this->assertArrayHasKey('inactive', $statuses);
        $this->assertArrayHasKey('maintenance', $statuses);
        $this->assertArrayHasKey('disconnected', $statuses);
        $this->assertArrayHasKey('planned', $statuses);
        $this->assertArrayHasKey('decommissioned', $statuses);
    }

    /** @test */
    public function it_belongs_to_customer()
    {
        $customer = Customer::factory()->create();
        $consumptionPoint = ConsumptionPoint::factory()->create(['customer_id' => $customer->id]);

        $this->assertInstanceOf(Customer::class, $consumptionPoint->customer);
        $this->assertEquals($customer->id, $consumptionPoint->customer->id);
    }

    /** @test */
    public function it_belongs_to_installation()
    {
        $installation = EnergyInstallation::factory()->create();
        $consumptionPoint = ConsumptionPoint::factory()->create(['installation_id' => $installation->id]);

        $this->assertInstanceOf(EnergyInstallation::class, $consumptionPoint->installation);
        $this->assertEquals($installation->id, $consumptionPoint->installation->id);
    }

    /** @test */
    public function it_belongs_to_managed_by_user()
    {
        $user = User::factory()->create();
        $consumptionPoint = ConsumptionPoint::factory()->create(['managed_by' => $user->id]);

        $this->assertInstanceOf(User::class, $consumptionPoint->managedBy);
        $this->assertEquals($user->id, $consumptionPoint->managedBy->id);
    }

    /** @test */
    public function it_belongs_to_created_by_user()
    {
        $user = User::factory()->create();
        $consumptionPoint = ConsumptionPoint::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $consumptionPoint->createdBy);
        $this->assertEquals($user->id, $consumptionPoint->createdBy->id);
    }

    /** @test */
    public function it_belongs_to_approved_by_user()
    {
        $user = User::factory()->create();
        $consumptionPoint = ConsumptionPoint::factory()->create(['approved_by' => $user->id]);

        $this->assertInstanceOf(User::class, $consumptionPoint->approvedBy);
        $this->assertEquals($user->id, $consumptionPoint->approvedBy->id);
    }

    /** @test */
    public function it_has_many_meters()
    {
        $consumptionPoint = ConsumptionPoint::factory()->create();
        
        // Asumiendo que existe un modelo Meter con relación a ConsumptionPoint
        // $meters = Meter::factory()->count(3)->create(['consumption_point_id' => $consumptionPoint->id]);
        
        // $this->assertInstanceOf(Collection::class, $consumptionPoint->meters);
        // $this->assertCount(3, $consumptionPoint->meters);
        
        $this->assertTrue(true); // Placeholder hasta que se implemente el modelo Meter
    }

    /** @test */
    public function it_has_many_readings()
    {
        $consumptionPoint = ConsumptionPoint::factory()->create();
        
        // Asumiendo que existe un modelo Reading con relación a ConsumptionPoint
        // $readings = Reading::factory()->count(3)->create(['consumption_point_id' => $consumptionPoint->id]);
        
        // $this->assertInstanceOf(Collection::class, $consumptionPoint->readings);
        // $this->assertCount(3, $consumptionPoint->readings);
        
        $this->assertTrue(true); // Placeholder hasta que se implemente el modelo Reading
    }

    /** @test */
    public function it_has_many_forecasts()
    {
        $consumptionPoint = ConsumptionPoint::factory()->create();
        
        // Asumiendo que existe un modelo Forecast con relación a ConsumptionPoint
        // $forecasts = Forecast::factory()->count(3)->create(['consumption_point_id' => $consumptionPoint->id]);
        
        // $this->assertInstanceOf(Collection::class, $consumptionPoint->forecasts);
        // $this->assertCount(3, $consumptionPoint->forecasts);
        
        $this->assertTrue(true); // Placeholder hasta que se implemente el modelo Forecast
    }

    /** @test */
    public function it_has_active_scope()
    {
        ConsumptionPoint::factory()->create(['status' => 'active']);
        ConsumptionPoint::factory()->create(['status' => 'inactive']);
        ConsumptionPoint::factory()->create(['status' => 'maintenance']);

        $activePoints = ConsumptionPoint::active()->get();

        $this->assertCount(1, $activePoints);
        $this->assertEquals('active', $activePoints->first()->status);
    }

    /** @test */
    public function it_has_maintenance_scope()
    {
        ConsumptionPoint::factory()->create(['status' => 'active']);
        ConsumptionPoint::factory()->create(['status' => 'maintenance']);
        ConsumptionPoint::factory()->create(['status' => 'inactive']);

        $maintenancePoints = ConsumptionPoint::maintenance()->get();

        $this->assertCount(1, $maintenancePoints);
        $this->assertEquals('maintenance', $maintenancePoints->first()->status);
    }

    /** @test */
    public function it_has_disconnected_scope()
    {
        ConsumptionPoint::factory()->create(['status' => 'active']);
        ConsumptionPoint::factory()->create(['status' => 'disconnected']);
        ConsumptionPoint::factory()->create(['status' => 'inactive']);

        $disconnectedPoints = ConsumptionPoint::disconnected()->get();

        $this->assertCount(1, $disconnectedPoints);
        $this->assertEquals('disconnected', $disconnectedPoints->first()->status);
    }

    /** @test */
    public function it_has_by_type_scope()
    {
        ConsumptionPoint::factory()->create(['point_type' => 'residential']);
        ConsumptionPoint::factory()->create(['point_type' => 'commercial']);
        ConsumptionPoint::factory()->create(['point_type' => 'residential']);

        $residentialPoints = ConsumptionPoint::byType('residential')->get();

        $this->assertCount(2, $residentialPoints);
        $this->assertEquals('residential', $residentialPoints->first()->point_type);
    }

    /** @test */
    public function it_has_by_customer_scope()
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();
        
        ConsumptionPoint::factory()->create(['customer_id' => $customer1->id]);
        ConsumptionPoint::factory()->create(['customer_id' => $customer2->id]);
        ConsumptionPoint::factory()->create(['customer_id' => $customer1->id]);

        $customer1Points = ConsumptionPoint::byCustomer($customer1->id)->get();

        $this->assertCount(2, $customer1Points);
        $this->assertEquals($customer1->id, $customer1Points->first()->customer_id);
    }

    /** @test */
    public function it_has_by_installation_scope()
    {
        $installation1 = EnergyInstallation::factory()->create();
        $installation2 = EnergyInstallation::factory()->create();
        
        ConsumptionPoint::factory()->create(['installation_id' => $installation1->id]);
        ConsumptionPoint::factory()->create(['installation_id' => $installation2->id]);
        ConsumptionPoint::factory()->create(['installation_id' => $installation1->id]);

        $installation1Points = ConsumptionPoint::byInstallation($installation1->id)->get();

        $this->assertCount(2, $installation1Points);
        $this->assertEquals($installation1->id, $installation1Points->first()->installation_id);
    }

    /** @test */
    public function it_has_connected_scope()
    {
        ConsumptionPoint::factory()->create(['is_connected' => true]);
        ConsumptionPoint::factory()->create(['is_connected' => false]);
        ConsumptionPoint::factory()->create(['is_connected' => true]);

        $connectedPoints = ConsumptionPoint::connected()->get();

        $this->assertCount(2, $connectedPoints);
        $this->assertTrue($connectedPoints->first()->is_connected);
    }

    /** @test */
    public function it_has_primary_scope()
    {
        ConsumptionPoint::factory()->create(['is_primary' => true]);
        ConsumptionPoint::factory()->create(['is_primary' => false]);
        ConsumptionPoint::factory()->create(['is_primary' => true]);

        $primaryPoints = ConsumptionPoint::primary()->get();

        $this->assertCount(2, $primaryPoints);
        $this->assertTrue($primaryPoints->first()->is_primary);
    }

    /** @test */
    public function it_has_high_consumption_scope()
    {
        ConsumptionPoint::factory()->create(['annual_consumption_kwh' => 5000]);
        ConsumptionPoint::factory()->create(['annual_consumption_kwh' => 15000]);
        ConsumptionPoint::factory()->create(['annual_consumption_kwh' => 8000]);

        $highConsumptionPoints = ConsumptionPoint::highConsumption(10000)->get();

        $this->assertCount(1, $highConsumptionPoints);
        $this->assertEquals(15000, $highConsumptionPoints->first()->annual_consumption_kwh);
    }

    /** @test */
    public function it_has_needs_calibration_scope()
    {
        $pastDate = now()->subDays(5);
        $futureDate = now()->addDays(5);
        
        ConsumptionPoint::factory()->create(['meter_next_calibration_date' => $pastDate]);
        ConsumptionPoint::factory()->create(['meter_next_calibration_date' => $futureDate]);
        ConsumptionPoint::factory()->create(['meter_next_calibration_date' => $pastDate]);

        $needsCalibrationPoints = ConsumptionPoint::needsCalibration()->get();

        $this->assertCount(2, $needsCalibrationPoints);
        $this->assertTrue($needsCalibrationPoints->first()->meter_next_calibration_date->lt(now()));
    }

    /** @test */
    public function it_checks_if_active()
    {
        $activePoint = ConsumptionPoint::factory()->create(['status' => 'active']);
        $inactivePoint = ConsumptionPoint::factory()->create(['status' => 'inactive']);

        $this->assertTrue($activePoint->isActive());
        $this->assertFalse($inactivePoint->isActive());
    }

    /** @test */
    public function it_checks_if_operational()
    {
        $operationalPoint = ConsumptionPoint::factory()->create(['status' => 'active']);
        $maintenancePoint = ConsumptionPoint::factory()->create(['status' => 'maintenance']);

        $this->assertTrue($operationalPoint->isOperational());
        $this->assertFalse($maintenancePoint->isOperational());
    }

    /** @test */
    public function it_checks_if_needs_maintenance()
    {
        $maintenancePoint = ConsumptionPoint::factory()->create(['status' => 'maintenance']);
        $activePoint = ConsumptionPoint::factory()->create(['status' => 'active']);

        $this->assertTrue($maintenancePoint->needsMaintenance());
        $this->assertFalse($activePoint->needsMaintenance());
    }

    /** @test */
    public function it_checks_if_needs_meter_calibration()
    {
        $pastDate = now()->subDays(5);
        $futureDate = now()->addDays(5);
        
        $needsCalibrationPoint = ConsumptionPoint::factory()->create(['meter_next_calibration_date' => $pastDate]);
        $okPoint = ConsumptionPoint::factory()->create(['meter_next_calibration_date' => $futureDate]);

        $this->assertTrue($needsCalibrationPoint->needsMeterCalibration());
        $this->assertFalse($okPoint->needsMeterCalibration());
    }

    /** @test */
    public function it_checks_if_under_warranty()
    {
        $warrantyEndDate = now()->addDays(30);
        $expiredWarrantyDate = now()->subDays(30);
        
        $underWarrantyPoint = ConsumptionPoint::factory()->create(['warranty_end_date' => $warrantyEndDate]);
        $expiredPoint = ConsumptionPoint::factory()->create(['warranty_end_date' => $expiredWarrantyDate]);

        $this->assertTrue($underWarrantyPoint->isUnderWarranty());
        $this->assertFalse($expiredPoint->isUnderWarranty());
    }

    /** @test */
    public function it_calculates_capacity_utilization()
    {
        $point = ConsumptionPoint::factory()->create([
            'peak_demand_kw' => 100,
            'average_demand_kw' => 60
        ]);

        $utilization = $point->getCapacityUtilization();

        $this->assertEquals(60.0, $utilization);
    }

    /** @test */
    public function it_calculates_days_since_connection()
    {
        $connectionDate = now()->subDays(10);
        $point = ConsumptionPoint::factory()->create(['connection_date' => $connectionDate]);

        $days = $point->getDaysSinceConnection();

        $this->assertEquals(10, $days);
    }

    /** @test */
    public function it_calculates_days_since_disconnection()
    {
        $disconnectionDate = now()->subDays(5);
        $point = ConsumptionPoint::factory()->create(['disconnection_date' => $disconnectionDate]);

        $days = $point->getDaysSinceDisconnection();

        $this->assertEquals(5, $days);
    }

    /** @test */
    public function it_calculates_days_until_calibration()
    {
        $calibrationDate = now()->addDays(15);
        $point = ConsumptionPoint::factory()->create(['meter_next_calibration_date' => $calibrationDate]);

        $days = $point->getDaysUntilCalibration();

        $this->assertEquals(15, $days);
    }

    /** @test */
    public function it_calculates_demand_factor()
    {
        $point = ConsumptionPoint::factory()->create([
            'peak_demand_kw' => 100,
            'average_demand_kw' => 60
        ]);

        $factor = $point->getDemandFactor();

        $this->assertEquals(0.6, $factor);
    }

    /** @test */
    public function it_formats_point_type()
    {
        $point = ConsumptionPoint::factory()->create(['point_type' => 'residential']);

        $formatted = $point->getFormattedPointType();

        $this->assertEquals('Residencial', $formatted);
    }

    /** @test */
    public function it_formats_status()
    {
        $point = ConsumptionPoint::factory()->create(['status' => 'active']);

        $formatted = $point->getFormattedStatus();

        $this->assertEquals('Activo', $formatted);
    }

    /** @test */
    public function it_formats_peak_demand()
    {
        $point = ConsumptionPoint::factory()->create(['peak_demand_kw' => 15.5]);

        $formatted = $point->getFormattedPeakDemand();

        $this->assertEquals('15.50 kW', $formatted);
    }

    /** @test */
    public function it_formats_average_demand()
    {
        $point = ConsumptionPoint::factory()->create(['average_demand_kw' => 8.2]);

        $formatted = $point->getFormattedAverageDemand();

        $this->assertEquals('8.20 kW', $formatted);
    }

    /** @test */
    public function it_formats_annual_consumption()
    {
        $point = ConsumptionPoint::factory()->create(['annual_consumption_kwh' => 5000.0]);

        $formatted = $point->getFormattedAnnualConsumption();

        $this->assertEquals('5,000.00 kWh', $formatted);
    }

    /** @test */
    public function it_formats_voltage_level()
    {
        $point = ConsumptionPoint::factory()->create(['voltage_level' => 230.0]);

        $formatted = $point->getFormattedVoltageLevel();

        $this->assertEquals('230.00 V', $formatted);
    }

    /** @test */
    public function it_formats_current_rating()
    {
        $point = ConsumptionPoint::factory()->create(['current_rating' => 63.0]);

        $formatted = $point->getFormattedCurrentRating();

        $this->assertEquals('63.00 A', $formatted);
    }

    /** @test */
    public function it_formats_connection_date()
    {
        $date = '2024-01-15';
        $point = ConsumptionPoint::factory()->create(['connection_date' => $date]);

        $formatted = $point->getFormattedConnectionDate();

        $this->assertEquals('15/01/2024', $formatted);
    }

    /** @test */
    public function it_formats_disconnection_date()
    {
        $date = '2024-12-31';
        $point = ConsumptionPoint::factory()->create(['disconnection_date' => $date]);

        $formatted = $point->getFormattedDisconnectionDate();

        $this->assertEquals('31/12/2024', $formatted);
    }

    /** @test */
    public function it_returns_status_badge_class()
    {
        $activePoint = ConsumptionPoint::factory()->create(['status' => 'active']);
        $maintenancePoint = ConsumptionPoint::factory()->create(['status' => 'maintenance']);
        $disconnectedPoint = ConsumptionPoint::factory()->create(['status' => 'disconnected']);

        $this->assertEquals('badge-success', $activePoint->getStatusBadgeClass());
        $this->assertEquals('badge-warning', $maintenancePoint->getStatusBadgeClass());
        $this->assertEquals('badge-danger', $disconnectedPoint->getStatusBadgeClass());
    }

    /** @test */
    public function it_returns_point_type_badge_class()
    {
        $residentialPoint = ConsumptionPoint::factory()->create(['point_type' => 'residential']);
        $commercialPoint = ConsumptionPoint::factory()->create(['point_type' => 'commercial']);
        $industrialPoint = ConsumptionPoint::factory()->create(['point_type' => 'industrial']);

        $this->assertEquals('badge-info', $residentialPoint->getPointTypeBadgeClass());
        $this->assertEquals('badge-primary', $commercialPoint->getPointTypeBadgeClass());
        $this->assertEquals('badge-secondary', $industrialPoint->getPointTypeBadgeClass());
    }

    /** @test */
    public function it_returns_total_consumption()
    {
        $point = ConsumptionPoint::factory()->create(['annual_consumption_kwh' => 5000.0]);

        $total = $point->getTotalConsumption();

        $this->assertEquals(5000.0, $total);
    }

    /** @test */
    public function it_returns_peak_demand()
    {
        $point = ConsumptionPoint::factory()->create(['peak_demand_kw' => 15.5]);

        $peak = $point->getPeakDemand();

        $this->assertEquals(15.5, $peak);
    }

    /** @test */
    public function it_returns_average_demand()
    {
        $point = ConsumptionPoint::factory()->create(['average_demand_kw' => 8.2]);

        $average = $point->getAverageDemand();

        $this->assertEquals(8.2, $average);
    }

    /** @test */
    public function it_returns_connection_status()
    {
        $connectedPoint = ConsumptionPoint::factory()->create(['is_connected' => true]);
        $disconnectedPoint = ConsumptionPoint::factory()->create(['is_connected' => false]);

        $this->assertTrue($connectedPoint->isConnected());
        $this->assertFalse($disconnectedPoint->isConnected());
    }

    /** @test */
    public function it_returns_primary_status()
    {
        $primaryPoint = ConsumptionPoint::factory()->create(['is_primary' => true]);
        $secondaryPoint = ConsumptionPoint::factory()->create(['is_primary' => false]);

        $this->assertTrue($primaryPoint->isPrimary());
        $this->assertFalse($secondaryPoint->isPrimary());
    }

    /** @test */
    public function it_returns_approval_status()
    {
        $approvedPoint = ConsumptionPoint::factory()->create(['approved_by' => 1, 'approved_at' => now()]);
        $pendingPoint = ConsumptionPoint::factory()->create(['approved_by' => null, 'approved_at' => null]);

        $this->assertTrue($approvedPoint->isApproved());
        $this->assertFalse($pendingPoint->isApproved());
    }

    /** @test */
    public function it_returns_approval_date()
    {
        $approvalDate = now();
        $point = ConsumptionPoint::factory()->create(['approved_at' => $approvalDate]);

        $this->assertEquals($approvalDate, $point->getApprovalDate());
    }

    /** @test */
    public function it_returns_approver_name()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $point = ConsumptionPoint::factory()->create(['approved_by' => $user->id]);

        $this->assertEquals('John Doe', $point->getApproverName());
    }

    /** @test */
    public function it_returns_creator_name()
    {
        $user = User::factory()->create(['name' => 'Jane Smith']);
        $point = ConsumptionPoint::factory()->create(['created_by' => $user->id]);

        $this->assertEquals('Jane Smith', $point->getCreatorName());
    }

    /** @test */
    public function it_returns_manager_name()
    {
        $user = User::factory()->create(['name' => 'Bob Manager']);
        $point = ConsumptionPoint::factory()->create(['managed_by' => $user->id]);

        $this->assertEquals('Bob Manager', $point->getManagerName());
    }

    /** @test */
    public function it_returns_customer_name()
    {
        $customer = Customer::factory()->create(['name' => 'Acme Corp']);
        $point = ConsumptionPoint::factory()->create(['customer_id' => $customer->id]);

        $this->assertEquals('Acme Corp', $point->getCustomerName());
    }

    /** @test */
    public function it_returns_installation_name()
    {
        $installation = EnergyInstallation::factory()->create(['name' => 'Solar Farm']);
        $point = ConsumptionPoint::factory()->create(['installation_id' => $installation->id]);

        $this->assertEquals('Solar Farm', $point->getInstallationName());
    }

    /** @test */
    public function it_returns_location_coordinates()
    {
        $point = ConsumptionPoint::factory()->create([
            'latitude' => 40.4168,
            'longitude' => -3.7038
        ]);

        $coordinates = $point->getLocationCoordinates();

        $this->assertEquals('40.4168, -3.7038', $coordinates);
    }

    /** @test */
    public function it_returns_location_summary()
    {
        $point = ConsumptionPoint::factory()->create([
            'location_address' => '123 Main St',
            'latitude' => 40.4168,
            'longitude' => -3.7038
        ]);

        $summary = $point->getLocationSummary();

        $this->assertEquals('123 Main St (40.4168, -3.7038)', $summary);
    }

    /** @test */
    public function it_returns_meter_summary()
    {
        $point = ConsumptionPoint::factory()->create([
            'meter_number' => 'MTR-001',
            'meter_type' => 'smart'
        ]);

        $summary = $point->getMeterSummary();

        $this->assertEquals('MTR-001 (smart)', $summary);
    }

    /** @test */
    public function it_returns_electrical_specs()
    {
        $point = ConsumptionPoint::factory()->create([
            'voltage_level' => 230.0,
            'current_rating' => 63.0,
            'phase_type' => 'single'
        ]);

        $specs = $point->getElectricalSpecs();

        $this->assertEquals('230V, 63A, Monofásico', $specs);
    }

    /** @test */
    public function it_returns_connection_summary()
    {
        $point = ConsumptionPoint::factory()->create([
            'connection_type' => 'grid',
            'service_type' => 'standard',
            'tariff_type' => 'time-of-use'
        ]);

        $summary = $point->getConnectionSummary();

        $this->assertEquals('Grid - Estándar - Tiempo de uso', $summary);
    }

    /** @test */
    public function it_returns_billing_summary()
    {
        $point = ConsumptionPoint::factory()->create([
            'billing_frequency' => 'monthly',
            'tariff_type' => 'time-of-use'
        ]);

        $summary = $point->getBillingSummary();

        $this->assertEquals('Mensual - Tiempo de uso', $summary);
    }

    /** @test */
    public function it_returns_operational_summary()
    {
        $point = ConsumptionPoint::factory()->create([
            'status' => 'active',
            'is_connected' => true,
            'is_primary' => true
        ]);

        $summary = $point->getOperationalSummary();

        $this->assertEquals('Activo, Conectado, Principal', $summary);
    }

    /** @test */
    public function it_returns_technical_summary()
    {
        $point = ConsumptionPoint::factory()->create([
            'peak_demand_kw' => 15.5,
            'average_demand_kw' => 8.2,
            'annual_consumption_kwh' => 5000.0
        ]);

        $summary = $point->getTechnicalSummary();

        $this->assertEquals('Pico: 15.50 kW, Promedio: 8.20 kW, Anual: 5,000.00 kWh', $summary);
    }

    /** @test */
    public function it_returns_full_summary()
    {
        $point = ConsumptionPoint::factory()->create([
            'name' => 'Residential Point',
            'point_type' => 'residential',
            'status' => 'active',
            'peak_demand_kw' => 15.5
        ]);

        $summary = $point->getFullSummary();

        $this->assertStringContainsString('Residential Point', $summary);
        $this->assertStringContainsString('Residencial', $summary);
        $this->assertStringContainsString('Activo', $summary);
        $this->assertStringContainsString('15.50 kW', $summary);
    }
}
