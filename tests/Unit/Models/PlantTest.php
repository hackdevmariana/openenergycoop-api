<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Plant;
use App\Models\PlantGroup;
use App\Models\CooperativePlantConfig;
use App\Models\User;
use App\Models\EnergyCooperative;
use App\Models\Organization;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;

class PlantTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_create_a_plant()
    {
        $plant = Plant::factory()->create([
            'name' => 'Pino',
            'co2_equivalent_per_unit_kg' => 25.5,
            'unit_label' => 'árbol',
            'is_active' => true
        ]);

        $this->assertInstanceOf(Plant::class, $plant);
        $this->assertEquals('Pino', $plant->name);
        $this->assertEquals(25.5, $plant->co2_equivalent_per_unit_kg);
        $this->assertEquals('árbol', $plant->unit_label);
        $this->assertTrue($plant->is_active);
    }

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'name',
            'co2_equivalent_per_unit_kg',
            'image',
            'description',
            'unit_label',
            'is_active'
        ];

        $this->assertEquals($fillable, (new Plant())->getFillable());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $plant = new Plant();
        
        $this->assertTrue($plant->hasCast('co2_equivalent_per_unit_kg', 'float'));
        $this->assertTrue($plant->hasCast('is_active', 'boolean'));
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $plant = Plant::factory()->create();
        $plantId = $plant->id;
        
        $plant->delete();
        
        $this->assertSoftDeleted('plants', ['id' => $plantId]);
    }

    /** @test */
    public function it_can_have_plant_groups()
    {
        $plant = Plant::factory()->create();
        $plantGroup = PlantGroup::factory()->create(['plant_id' => $plant->id]);

        $this->assertInstanceOf(PlantGroup::class, $plant->plantGroups->first());
        $this->assertEquals($plantGroup->id, $plant->plantGroups->first()->id);
    }

    /** @test */
    public function it_can_have_cooperative_configs()
    {
        $plant = Plant::factory()->create();
        $cooperative = EnergyCooperative::factory()->create();
        $config = CooperativePlantConfig::factory()->create([
            'plant_id' => $plant->id,
            'cooperative_id' => $cooperative->id
        ]);

        $this->assertInstanceOf(CooperativePlantConfig::class, $plant->cooperativeConfigs->first());
        $this->assertEquals($config->id, $plant->cooperativeConfigs->first()->id);
    }

    /** @test */
    public function it_can_scope_active_plants()
    {
        Plant::factory()->create(['is_active' => true]);
        Plant::factory()->create(['is_active' => false]);

        $activePlants = Plant::active()->get();

        $this->assertEquals(1, $activePlants->count());
        $this->assertTrue($activePlants->first()->is_active);
    }

    /** @test */
    public function it_can_scope_by_name()
    {
        Plant::factory()->create(['name' => 'Pino']);
        Plant::factory()->create(['name' => 'Vid']);

        $pinoPlants = Plant::byName('Pino')->get();

        $this->assertEquals(1, $pinoPlants->count());
        $this->assertEquals('Pino', $pinoPlants->first()->name);
    }

    /** @test */
    public function it_can_scope_by_unit_label()
    {
        Plant::factory()->create(['unit_label' => 'árbol']);
        Plant::factory()->create(['unit_label' => 'planta']);

        $treePlants = Plant::byUnitLabel('árbol')->get();

        $this->assertEquals(1, $treePlants->count());
        $this->assertEquals('árbol', $treePlants->first()->unit_label);
    }

    /** @test */
    public function it_can_scope_by_co2_range()
    {
        Plant::factory()->create(['co2_equivalent_per_unit_kg' => 10.0]);
        Plant::factory()->create(['co2_equivalent_per_unit_kg' => 50.0]);
        Plant::factory()->create(['co2_equivalent_per_unit_kg' => 100.0]);

        $mediumCo2Plants = Plant::byCo2Range(20.0, 80.0)->get();

        $this->assertEquals(1, $mediumCo2Plants->count());
        $this->assertEquals(50.0, $mediumCo2Plants->first()->co2_equivalent_per_unit_kg);
    }

    /** @test */
    public function it_can_search_plants()
    {
        Plant::factory()->create(['name' => 'Pino', 'description' => 'Árbol de hoja perenne']);
        Plant::factory()->create(['name' => 'Vid', 'description' => 'Planta trepadora']);
        Plant::factory()->create(['name' => 'Plátano', 'description' => 'Árbol frutal']);

        $searchResults = Plant::search('árbol')->get();

        $this->assertEquals(2, $searchResults->count());
        $this->assertTrue($searchResults->contains('name', 'Pino'));
        $this->assertTrue($searchResults->contains('name', 'Plátano'));
    }

    /** @test */
    public function it_has_image_url_accessor()
    {
        $plant = Plant::factory()->create(['image' => 'plants/pino.jpg']);

        $this->assertEquals(asset('storage/plants/pino.jpg'), $plant->imageUrl);
    }

    /** @test */
    public function it_has_formatted_co2_accessor()
    {
        $plant = Plant::factory()->create(['co2_equivalent_per_unit_kg' => 25.5]);

        $this->assertEquals('25.5 kg CO₂', $plant->formattedCo2);
    }

    /** @test */
    public function it_has_display_name_accessor()
    {
        $plant = Plant::factory()->create([
            'name' => 'Pino',
            'unit_label' => 'árbol'
        ]);

        $this->assertEquals('Pino (árbol)', $plant->displayName);
    }

    /** @test */
    public function it_can_calculate_co2_avoided()
    {
        $plant = Plant::factory()->create(['co2_equivalent_per_unit_kg' => 25.0]);
        $numberOfPlants = 10;

        $co2Avoided = $plant->calculateCo2Avoided($numberOfPlants);

        $this->assertEquals(250.0, $co2Avoided);
    }

    /** @test */
    public function it_can_check_if_available()
    {
        $activePlant = Plant::factory()->create(['is_active' => true]);
        $inactivePlant = Plant::factory()->create(['is_active' => false]);

        $this->assertTrue($activePlant->isAvailable());
        $this->assertFalse($inactivePlant->isAvailable());
    }

    /** @test */
    public function it_can_activate()
    {
        $plant = Plant::factory()->create(['is_active' => false]);

        $plant->activate();

        $this->assertTrue($plant->is_active);
    }

    /** @test */
    public function it_can_deactivate()
    {
        $plant = Plant::factory()->create(['is_active' => true]);

        $plant->deactivate();

        $this->assertFalse($plant->is_active);
    }

    /** @test */
    public function it_can_toggle_active_status()
    {
        $plant = Plant::factory()->create(['is_active' => false]);

        $plant->toggleActive();
        $this->assertTrue($plant->is_active);

        $plant->toggleActive();
        $this->assertFalse($plant->is_active);
    }

    /** @test */
    public function it_has_factory_states()
    {
        $inactivePlant = Plant::factory()->inactive()->create();
        $treePlant = Plant::factory()->tree()->create();
        $vinePlant = Plant::factory()->vine()->create();
        $highCo2Plant = Plant::factory()->highCo2()->create();
        $lowCo2Plant = Plant::factory()->lowCo2()->create();

        $this->assertFalse($inactivePlant->is_active);
        $this->assertEquals('árbol', $treePlant->unit_label);
        $this->assertEquals('viña', $vinePlant->unit_label);
        $this->assertGreaterThan(50, $highCo2Plant->co2_equivalent_per_unit_kg);
        $this->assertLessThan(20, $lowCo2Plant->co2_equivalent_per_unit_kg);
    }

    /** @test */
    public function it_can_be_created_with_factory()
    {
        $plant = Plant::factory()->create();

        $this->assertInstanceOf(Plant::class, $plant);
        $this->assertNotEmpty($plant->name);
        $this->assertGreaterThan(0, $plant->co2_equivalent_per_unit_kg);
        $this->assertNotEmpty($plant->unit_label);
    }
}
