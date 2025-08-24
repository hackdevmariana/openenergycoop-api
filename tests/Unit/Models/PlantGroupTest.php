<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\PlantGroup;
use App\Models\Plant;
use App\Models\User;
use App\Models\EnergyCooperative;
use App\Models\Organization;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;

class PlantGroupTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_create_a_plant_group()
    {
        $plantGroup = PlantGroup::factory()->create([
            'name' => 'Mi Viña Solar',
            'number_of_plants' => 50,
            'co2_avoided_total' => 1250.0,
            'custom_label' => 'Viña cooperativa',
            'is_active' => true
        ]);

        $this->assertInstanceOf(PlantGroup::class, $plantGroup);
        $this->assertEquals('Mi Viña Solar', $plantGroup->name);
        $this->assertEquals(50, $plantGroup->number_of_plants);
        $this->assertEquals(1250.0, $plantGroup->co2_avoided_total);
        $this->assertEquals('Viña cooperativa', $plantGroup->custom_label);
        $this->assertTrue($plantGroup->is_active);
    }

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'user_id',
            'name',
            'plant_id',
            'number_of_plants',
            'co2_avoided_total',
            'custom_label',
            'is_active'
        ];

        $this->assertEquals($fillable, (new PlantGroup())->getFillable());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $plantGroup = new PlantGroup();
        
        $this->assertTrue($plantGroup->hasCast('number_of_plants', 'integer'));
        $this->assertTrue($plantGroup->hasCast('co2_avoided_total', 'float'));
        $this->assertTrue($plantGroup->hasCast('is_active', 'boolean'));
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $plantGroup = PlantGroup::factory()->create();
        $plantGroupId = $plantGroup->id;
        
        $plantGroup->delete();
        
        $this->assertSoftDeleted('plant_groups', ['id' => $plantGroupId]);
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $plantGroup = PlantGroup::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $plantGroup->user);
        $this->assertEquals($user->id, $plantGroup->user->id);
    }

    /** @test */
    public function it_belongs_to_plant()
    {
        $plant = Plant::factory()->create();
        $plantGroup = PlantGroup::factory()->create(['plant_id' => $plant->id]);

        $this->assertInstanceOf(Plant::class, $plantGroup->plant);
        $this->assertEquals($plant->id, $plantGroup->plant->id);
    }

    /** @test */
    public function it_can_scope_active_groups()
    {
        PlantGroup::factory()->create(['is_active' => true]);
        PlantGroup::factory()->create(['is_active' => false]);

        $activeGroups = PlantGroup::active()->get();

        $this->assertEquals(1, $activeGroups->count());
        $this->assertTrue($activeGroups->first()->is_active);
    }

    /** @test */
    public function it_can_scope_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        PlantGroup::factory()->create(['user_id' => $user1->id]);
        PlantGroup::factory()->create(['user_id' => $user2->id]);

        $user1Groups = PlantGroup::byUser($user1->id)->get();

        $this->assertEquals(1, $user1Groups->count());
        $this->assertEquals($user1->id, $user1Groups->first()->user_id);
    }

    /** @test */
    public function it_can_scope_by_plant()
    {
        $plant1 = Plant::factory()->create();
        $plant2 = Plant::factory()->create();
        
        PlantGroup::factory()->create(['plant_id' => $plant1->id]);
        PlantGroup::factory()->create(['plant_id' => $plant2->id]);

        $plant1Groups = PlantGroup::byPlant($plant1->id)->get();

        $this->assertEquals(1, $plant1Groups->count());
        $this->assertEquals($plant1->id, $plant1Groups->first()->plant_id);
    }

    /** @test */
    public function it_can_scope_collective_groups()
    {
        PlantGroup::factory()->create(['user_id' => null]); // Colectivo
        PlantGroup::factory()->create(['user_id' => User::factory()->create()->id]); // Individual

        $collectiveGroups = PlantGroup::collective()->get();

        $this->assertEquals(1, $collectiveGroups->count());
        $this->assertNull($collectiveGroups->first()->user_id);
    }

    /** @test */
    public function it_can_scope_individual_groups()
    {
        PlantGroup::factory()->create(['user_id' => null]); // Colectivo
        PlantGroup::factory()->create(['user_id' => User::factory()->create()->id]); // Individual

        $individualGroups = PlantGroup::individual()->get();

        $this->assertEquals(1, $individualGroups->count());
        $this->assertNotNull($individualGroups->first()->user_id);
    }

    /** @test */
    public function it_can_scope_by_co2_range()
    {
        PlantGroup::factory()->create(['co2_avoided_total' => 100.0]);
        PlantGroup::factory()->create(['co2_avoided_total' => 500.0]);
        PlantGroup::factory()->create(['co2_avoided_total' => 1000.0]);

        $mediumCo2Groups = PlantGroup::byCo2Range(200.0, 800.0)->get();

        $this->assertEquals(1, $mediumCo2Groups->count());
        $this->assertEquals(500.0, $mediumCo2Groups->first()->co2_avoided_total);
    }

    /** @test */
    public function it_can_search_groups()
    {
        PlantGroup::factory()->create(['name' => 'Mi Viña Solar', 'custom_label' => 'Viña cooperativa']);
        PlantGroup::factory()->create(['name' => 'Pinar Comunitario', 'custom_label' => 'Bosque compartido']);
        PlantGroup::factory()->create(['name' => 'Huerta Individual', 'custom_label' => 'Mi huerta']);

        $searchResults = PlantGroup::search('cooperativa')->get();

        $this->assertEquals(1, $searchResults->count());
        $this->assertEquals('Mi Viña Solar', $searchResults->first()->name);
    }

    /** @test */
    public function it_has_display_name_accessor()
    {
        $plantGroup = PlantGroup::factory()->create([
            'name' => 'Mi Viña',
            'custom_label' => 'Viña cooperativa'
        ]);

        $this->assertEquals('Mi Viña (Viña cooperativa)', $plantGroup->displayName);
    }

    /** @test */
    public function it_has_formatted_co2_avoided_accessor()
    {
        $plantGroup = PlantGroup::factory()->create(['co2_avoided_total' => 1250.5]);

        $this->assertEquals('1,250.5 kg CO₂', $plantGroup->formattedCo2Avoided);
    }

    /** @test */
    public function it_has_formatted_plant_count_accessor()
    {
        $plantGroup = PlantGroup::factory()->create(['number_of_plants' => 50]);

        $this->assertEquals('50 plantas', $plantGroup->formattedPlantCount);
    }

    /** @test */
    public function it_has_is_collective_accessor()
    {
        $collectiveGroup = PlantGroup::factory()->create(['user_id' => null]);
        $individualGroup = PlantGroup::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->assertTrue($collectiveGroup->isCollective);
        $this->assertFalse($individualGroup->isCollective);
    }

    /** @test */
    public function it_has_is_individual_accessor()
    {
        $collectiveGroup = PlantGroup::factory()->create(['user_id' => null]);
        $individualGroup = PlantGroup::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->assertFalse($collectiveGroup->isIndividual);
        $this->assertTrue($individualGroup->isIndividual);
    }

    /** @test */
    public function it_can_add_plants()
    {
        $plantGroup = PlantGroup::factory()->create([
            'number_of_plants' => 10,
            'co2_avoided_total' => 250.0
        ]);

        $plantGroup->addPlants(5);

        $this->assertEquals(15, $plantGroup->number_of_plants);
        $this->assertEquals(375.0, $plantGroup->co2_avoided_total);
    }

    /** @test */
    public function it_can_remove_plants()
    {
        $plantGroup = PlantGroup::factory()->create([
            'number_of_plants' => 20,
            'co2_avoided_total' => 500.0
        ]);

        $plantGroup->removePlants(8);

        $this->assertEquals(12, $plantGroup->number_of_plants);
        $this->assertEquals(300.0, $plantGroup->co2_avoided_total);
    }

    /** @test */
    public function it_can_update_co2_avoided()
    {
        $plantGroup = PlantGroup::factory()->create(['co2_avoided_total' => 100.0]);

        $plantGroup->updateCo2Avoided(250.0);

        $this->assertEquals(250.0, $plantGroup->co2_avoided_total);
    }

    /** @test */
    public function it_can_calculate_efficiency()
    {
        $plantGroup = PlantGroup::factory()->create([
            'number_of_plants' => 10,
            'co2_avoided_total' => 250.0
        ]);

        $efficiency = $plantGroup->calculateEfficiency();

        $this->assertEquals(25.0, $efficiency); // 250.0 / 10 = 25.0
    }

    /** @test */
    public function it_can_activate()
    {
        $plantGroup = PlantGroup::factory()->create(['is_active' => false]);

        $plantGroup->activate();

        $this->assertTrue($plantGroup->is_active);
    }

    /** @test */
    public function it_can_deactivate()
    {
        $plantGroup = PlantGroup::factory()->create(['is_active' => true]);

        $plantGroup->deactivate();

        $this->assertFalse($plantGroup->is_active);
    }

    /** @test */
    public function it_can_toggle_active_status()
    {
        $plantGroup = PlantGroup::factory()->create(['is_active' => false]);

        $plantGroup->toggleActive();
        $this->assertTrue($plantGroup->is_active);

        $plantGroup->toggleActive();
        $this->assertFalse($plantGroup->is_active);
    }

    /** @test */
    public function it_updates_timestamp_when_plants_are_modified()
    {
        $plantGroup = PlantGroup::factory()->create();
        $originalUpdatedAt = $plantGroup->updated_at;

        sleep(1); // Asegurar diferencia de tiempo
        $plantGroup->addPlants(5);

        $this->assertNotEquals($originalUpdatedAt, $plantGroup->updated_at);
    }

    /** @test */
    public function it_has_factory_states()
    {
        $collectiveGroup = PlantGroup::factory()->collective()->create();
        $individualGroup = PlantGroup::factory()->individual()->create();
        $inactiveGroup = PlantGroup::factory()->inactive()->create();
        $largeGroup = PlantGroup::factory()->large()->create();
        $smallGroup = PlantGroup::factory()->small()->create();
        $highCo2Group = PlantGroup::factory()->highCo2Avoidance()->create();

        $this->assertNull($collectiveGroup->user_id);
        $this->assertNotNull($individualGroup->user_id);
        $this->assertFalse($inactiveGroup->is_active);
        $this->assertGreaterThan(100, $largeGroup->number_of_plants);
        $this->assertLessThan(20, $smallGroup->number_of_plants);
        $this->assertGreaterThan(1000, $highCo2Group->co2_avoided_total);
    }

    /** @test */
    public function it_can_be_created_with_factory()
    {
        $plantGroup = PlantGroup::factory()->create();

        $this->assertInstanceOf(PlantGroup::class, $plantGroup);
        $this->assertNotEmpty($plantGroup->name);
        $this->assertGreaterThan(0, $plantGroup->number_of_plants);
        $this->assertGreaterThan(0, $plantGroup->co2_avoided_total);
    }
}
