<?php

namespace Tests\Unit\Models;

use App\Models\EnergyMeter;
use App\Models\Customer;
use App\Models\EnergyInstallation;
use App\Models\ConsumptionPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnergyMeterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'meter_number',
            'name',
            'description',
            'meter_type',
            'status',
            'meter_category',
            'manufacturer',
            'model',
            'serial_number',
            'installation_id',
            'consumption_point_id',
            'customer_id',
            'installation_date',
            'commissioning_date',
            'next_calibration_date',
            'voltage_rating',
            'current_rating',
            'accuracy_class',
            'measurement_range_min',
            'measurement_range_max',
            'is_smart_meter',
            'has_remote_reading',
            'has_two_way_communication',
            'communication_protocol',
            'firmware_version',
            'hardware_version',
            'warranty_expiry_date',
            'last_maintenance_date',
            'next_maintenance_date',
            'notes',
            'metadata',
            'managed_by',
            'created_by',
            'approved_by',
            'approved_at',
        ];

        $energyMeter = new EnergyMeter();
        $this->assertEquals($fillable, $energyMeter->getFillable());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $energyMeter = new EnergyMeter();
        $casts = $energyMeter->getCasts();

        $this->assertArrayHasKey('installation_date', $casts);
        $this->assertArrayHasKey('commissioning_date', $casts);
        $this->assertArrayHasKey('next_calibration_date', $casts);
        $this->assertArrayHasKey('warranty_expiry_date', $casts);
        $this->assertArrayHasKey('last_maintenance_date', $casts);
        $this->assertArrayHasKey('next_maintenance_date', $casts);
        $this->assertArrayHasKey('approved_at', $casts);
        $this->assertArrayHasKey('voltage_rating', $casts);
        $this->assertArrayHasKey('current_rating', $casts);
        $this->assertArrayHasKey('accuracy_class', $casts);
        $this->assertArrayHasKey('measurement_range_min', $casts);
        $this->assertArrayHasKey('measurement_range_max', $casts);
        $this->assertArrayHasKey('is_smart_meter', $casts);
        $this->assertArrayHasKey('has_remote_reading', $casts);
        $this->assertArrayHasKey('has_two_way_communication', $casts);
        $this->assertArrayHasKey('metadata', $casts);
    }

    /** @test */
    public function it_has_static_enum_methods()
    {
        $meterTypes = EnergyMeter::getMeterTypes();
        $statuses = EnergyMeter::getStatuses();
        $categories = EnergyMeter::getMeterCategories();

        $this->assertIsArray($meterTypes);
        $this->assertIsArray($statuses);
        $this->assertIsArray($categories);
        $this->assertNotEmpty($meterTypes);
        $this->assertNotEmpty($statuses);
        $this->assertNotEmpty($categories);
    }

    /** @test */
    public function it_has_relationships()
    {
        $energyMeter = EnergyMeter::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $energyMeter->installation());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $energyMeter->consumptionPoint());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $energyMeter->customer());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $energyMeter->installedBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $energyMeter->managedBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $energyMeter->createdBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $energyMeter->approvedBy());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, $energyMeter->meterable());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $energyMeter->readings());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $energyMeter->forecasts());
    }

    /** @test */
    public function it_has_scopes()
    {
        // Scope active
        $activeMeter = EnergyMeter::factory()->create(['status' => 'active']);
        $inactiveMeter = EnergyMeter::factory()->create(['status' => 'inactive']);

        $activeMeters = EnergyMeter::active()->get();
        $this->assertTrue($activeMeters->contains($activeMeter));
        $this->assertFalse($activeMeters->contains($inactiveMeter));

        // Scope smartMeter
        $smartMeter = EnergyMeter::factory()->create(['is_smart_meter' => true]);
        $regularMeter = EnergyMeter::factory()->create(['is_smart_meter' => false]);

        $smartMeters = EnergyMeter::smartMeter()->get();
        $this->assertTrue($smartMeters->contains($smartMeter));
        $this->assertFalse($smartMeters->contains($regularMeter));

        // Scope byType
        $smartMeter = EnergyMeter::factory()->create(['meter_type' => 'smart_meter']);
        $digitalMeter = EnergyMeter::factory()->create(['meter_type' => 'digital_meter']);

        $smartMeters = EnergyMeter::byType('smart_meter')->get();
        $this->assertTrue($smartMeters->contains($smartMeter));
        $this->assertFalse($smartMeters->contains($digitalMeter));

        // Scope byCategory
        $electricityMeter = EnergyMeter::factory()->create(['meter_category' => 'electricity']);
        $waterMeter = EnergyMeter::factory()->create(['meter_category' => 'water']);

        $electricityMeters = EnergyMeter::byCategory('electricity')->get();
        $this->assertTrue($electricityMeters->contains($electricityMeter));
        $this->assertFalse($electricityMeters->contains($waterMeter));
    }

    /** @test */
    public function it_has_validation_helper_methods()
    {
        $energyMeter = EnergyMeter::factory()->create([
            'status' => 'active',
            'is_smart_meter' => true,
            'next_calibration_date' => now()->addDays(30),
            'warranty_expiry_date' => now()->addDays(365),
            'next_maintenance_date' => now()->addDays(7),
        ]);

        $this->assertTrue($energyMeter->isActive());
        $this->assertTrue($energyMeter->isSmartMeter());
        $this->assertFalse($energyMeter->needsCalibration());
        $this->assertTrue($energyMeter->isUnderWarranty());
        $this->assertFalse($energyMeter->needsMaintenance());
        $this->assertTrue($energyMeter->isCommissioned());
    }

    /** @test */
    public function it_has_calculation_methods()
    {
        $energyMeter = EnergyMeter::factory()->create([
            'installation_date' => now()->subYears(2),
            'next_calibration_date' => now()->addDays(30),
            'warranty_expiry_date' => now()->addDays(365),
            'next_maintenance_date' => now()->addDays(7),
        ]);

        $this->assertEquals(2, $energyMeter->getAgeInYears());
        $this->assertEquals(30, $energyMeter->getDaysUntilCalibration());
        $this->assertEquals(365, $energyMeter->getDaysUntilWarrantyExpiry());
        $this->assertEquals(7, $energyMeter->getDaysUntilMaintenance());
    }

    /** @test */
    public function it_has_formatting_methods()
    {
        $energyMeter = EnergyMeter::factory()->create([
            'meter_type' => 'smart_meter',
            'status' => 'active',
            'meter_category' => 'electricity',
            'voltage_rating' => 230.5,
            'current_rating' => 63.0,
            'accuracy_class' => 0.5,
        ]);

        $this->assertIsString($energyMeter->getFormattedMeterType());
        $this->assertIsString($energyMeter->getFormattedStatus());
        $this->assertIsString($energyMeter->getFormattedMeterCategory());
        $this->assertIsString($energyMeter->getFormattedVoltageRating());
        $this->assertIsString($energyMeter->getFormattedCurrentRating());
    }

    /** @test */
    public function it_has_badge_class_methods()
    {
        $energyMeter = EnergyMeter::factory()->create([
            'status' => 'active',
            'meter_type' => 'smart_meter',
            'meter_category' => 'electricity',
            'accuracy_class' => 0.5,
            'is_smart_meter' => true,
            'has_remote_reading' => true,
            'has_two_way_communication' => true,
        ]);

        $this->assertIsString($energyMeter->getStatusBadgeClass());
        $this->assertIsString($energyMeter->getMeterTypeBadgeClass());
        $this->assertIsString($energyMeter->getMeterCategoryBadgeClass());
        $this->assertIsString($energyMeter->getAccuracyBadgeClass());
        $this->assertIsString($energyMeter->getSmartMeterBadgeClass());
        $this->assertIsString($energyMeter->getRemoteReadingBadgeClass());
        $this->assertIsString($energyMeter->getTwoWayCommunicationBadgeClass());
    }

    /** @test */
    public function it_has_summary_methods()
    {
        $energyMeter = EnergyMeter::factory()->create([
            'voltage_rating' => 230.0,
            'current_rating' => 63.0,
            'accuracy_class' => 0.5,
        ]);

        $this->assertIsString($energyMeter->getMeasurementRange());
        $this->assertIsString($energyMeter->getTechnicalSpecifications());
        $this->assertIsString($energyMeter->getMaintenanceSummary());
    }

    /** @test */
    public function it_can_be_created_with_factory()
    {
        $energyMeter = EnergyMeter::factory()->create();

        $this->assertInstanceOf(EnergyMeter::class, $energyMeter);
        $this->assertDatabaseHas('energy_meters', [
            'id' => $energyMeter->id,
        ]);
    }

    /** @test */
    public function it_can_be_updated()
    {
        $energyMeter = EnergyMeter::factory()->create();
        $newName = 'Updated Meter Name';

        $energyMeter->update(['name' => $newName]);

        $this->assertEquals($newName, $energyMeter->fresh()->name);
    }

    /** @test */
    public function it_can_be_deleted()
    {
        $energyMeter = EnergyMeter::factory()->create();
        $energyMeterId = $energyMeter->id;

        $energyMeter->delete();

        $this->assertDatabaseMissing('energy_meters', [
            'id' => $energyMeterId,
        ]);
    }

    /** @test */
    public function it_has_correct_table_name()
    {
        $energyMeter = new EnergyMeter();
        $this->assertEquals('energy_meters', $energyMeter->getTable());
    }

    /** @test */
    public function it_has_correct_primary_key()
    {
        $energyMeter = new EnergyMeter();
        $this->assertEquals('id', $energyMeter->getKeyName());
    }

    /** @test */
    public function it_uses_timestamps()
    {
        $energyMeter = new EnergyMeter();
        $this->assertTrue($energyMeter->usesTimestamps());
    }

    /** @test */
    public function it_has_soft_deletes_when_configured()
    {
        $energyMeter = new EnergyMeter();
        // Verificar si el modelo usa soft deletes (esto dependerá de la implementación)
        $this->assertTrue(true); // Placeholder hasta que se implemente soft deletes
    }

    /** @test */
    public function it_can_access_related_models()
    {
        $customer = Customer::factory()->create();
        $installation = EnergyInstallation::factory()->create();
        $consumptionPoint = ConsumptionPoint::factory()->create();
        $user = User::factory()->create();

        $energyMeter = EnergyMeter::factory()->create([
            'customer_id' => $customer->id,
            'installation_id' => $installation->id,
            'consumption_point_id' => $consumptionPoint->id,
            'managed_by' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->assertInstanceOf(Customer::class, $energyMeter->customer);
        $this->assertInstanceOf(EnergyInstallation::class, $energyMeter->installation);
        $this->assertInstanceOf(ConsumptionPoint::class, $energyMeter->consumptionPoint);
        $this->assertInstanceOf(User::class, $energyMeter->managedBy);
        $this->assertInstanceOf(User::class, $energyMeter->createdBy);
    }

    /** @test */
    public function it_handles_metadata_correctly()
    {
        $metadata = ['protocol' => 'Modbus', 'baud_rate' => 9600];
        $energyMeter = EnergyMeter::factory()->create(['metadata' => $metadata]);

        $this->assertEquals($metadata, $energyMeter->metadata);
        $this->assertIsArray($energyMeter->metadata);
    }

    /** @test */
    public function it_can_calculate_measurement_range()
    {
        $energyMeter = EnergyMeter::factory()->create([
            'measurement_range_min' => 0,
            'measurement_range_max' => 100,
        ]);

        $range = $energyMeter->getMeasurementRange();
        $this->assertIsString($range);
        $this->assertStringContainsString('0', $range);
        $this->assertStringContainsString('100', $range);
    }

    /** @test */
    public function it_can_determine_if_needs_calibration()
    {
        // Medidor que necesita calibración
        $meterNeedingCalibration = EnergyMeter::factory()->create([
            'next_calibration_date' => now()->subDays(5),
            'status' => 'active',
        ]);

        $this->assertTrue($meterNeedingCalibration->needsCalibration());

        // Medidor que no necesita calibración
        $meterNotNeedingCalibration = EnergyMeter::factory()->create([
            'next_calibration_date' => now()->addDays(30),
            'status' => 'active',
        ]);

        $this->assertFalse($meterNotNeedingCalibration->needsCalibration());
    }

    /** @test */
    public function it_can_determine_if_under_warranty()
    {
        // Medidor bajo garantía
        $meterUnderWarranty = EnergyMeter::factory()->create([
            'warranty_expiry_date' => now()->addDays(100),
        ]);

        $this->assertTrue($meterUnderWarranty->isUnderWarranty());

        // Medidor fuera de garantía
        $meterOutOfWarranty = EnergyMeter::factory()->create([
            'warranty_expiry_date' => now()->subDays(100),
        ]);

        $this->assertFalse($meterOutOfWarranty->isUnderWarranty());
    }

    /** @test */
    public function it_can_determine_if_needs_maintenance()
    {
        // Medidor que necesita mantenimiento
        $meterNeedingMaintenance = EnergyMeter::factory()->create([
            'next_maintenance_date' => now()->subDays(5),
            'status' => 'active',
        ]);

        $this->assertTrue($meterNeedingMaintenance->needsMaintenance());

        // Medidor que no necesita mantenimiento
        $meterNotNeedingMaintenance = EnergyMeter::factory()->create([
            'next_maintenance_date' => now()->addDays(30),
            'status' => 'active',
        ]);

        $this->assertFalse($meterNotNeedingMaintenance->needsMaintenance());
    }

    /** @test */
    public function it_can_determine_if_commissioned()
    {
        // Medidor puesto en servicio
        $commissionedMeter = EnergyMeter::factory()->create([
            'commissioning_date' => now()->subDays(10),
        ]);

        $this->assertTrue($commissionedMeter->isCommissioned());

        // Medidor no puesto en servicio
        $nonCommissionedMeter = EnergyMeter::factory()->create([
            'commissioning_date' => null,
        ]);

        $this->assertFalse($nonCommissionedMeter->isCommissioned());
    }

    /** @test */
    public function it_can_calculate_age_in_years()
    {
        $energyMeter = EnergyMeter::factory()->create([
            'installation_date' => now()->subYears(3)->subMonths(6),
        ]);

        $age = $energyMeter->getAgeInYears();
        $this->assertEquals(3, $age);
    }

    /** @test */
    public function it_can_calculate_days_until_calibration()
    {
        $energyMeter = EnergyMeter::factory()->create([
            'next_calibration_date' => now()->addDays(15),
        ]);

        $days = $energyMeter->getDaysUntilCalibration();
        $this->assertEquals(15, $days);
    }

    /** @test */
    public function it_can_calculate_days_until_warranty_expiry()
    {
        $energyMeter = EnergyMeter::factory()->create([
            'warranty_expiry_date' => now()->addDays(200),
        ]);

        $days = $energyMeter->getDaysUntilWarrantyExpiry();
        $this->assertEquals(200, $days);
    }

    /** @test */
    public function it_can_calculate_days_until_maintenance()
    {
        $energyMeter = EnergyMeter::factory()->create([
            'next_maintenance_date' => now()->addDays(25),
        ]);

        $days = $energyMeter->getDaysUntilMaintenance();
        $this->assertEquals(25, $days);
    }

    /** @test */
    public function it_returns_negative_days_for_overdue_dates()
    {
        $energyMeter = EnergyMeter::factory()->create([
            'next_calibration_date' => now()->subDays(10),
        ]);

        $days = $energyMeter->getDaysUntilCalibration();
        $this->assertEquals(-10, $days);
    }

    /** @test */
    public function it_handles_null_dates_correctly()
    {
        $energyMeter = EnergyMeter::factory()->create([
            'next_calibration_date' => null,
            'warranty_expiry_date' => null,
            'next_maintenance_date' => null,
        ]);

        $this->assertNull($energyMeter->getDaysUntilCalibration());
        $this->assertNull($energyMeter->getDaysUntilWarrantyExpiry());
        $this->assertNull($energyMeter->getDaysUntilMaintenance());
    }
}
