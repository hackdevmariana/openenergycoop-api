<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\CooperativePlantConfig;
use App\Models\Plant;
use App\Models\EnergyCooperative;
use App\Models\Organization;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;

class CooperativePlantConfigTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_create_a_cooperative_plant_config()
    {
        $config = CooperativePlantConfig::factory()->create([
            'default' => true,
            'active' => true
        ]);

        $this->assertInstanceOf(CooperativePlantConfig::class, $config);
        $this->assertTrue($config->default);
        $this->assertTrue($config->active);
    }

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'cooperative_id',
            'plant_id',
            'default',
            'active',
            'organization_id'
        ];

        $this->assertEquals($fillable, (new CooperativePlantConfig())->getFillable());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $config = new CooperativePlantConfig();
        
        $this->assertTrue($config->hasCast('default', 'boolean'));
        $this->assertTrue($config->hasCast('active', 'boolean'));
    }

    /** @test */
    public function it_belongs_to_cooperative()
    {
        $cooperative = EnergyCooperative::factory()->create();
        $config = CooperativePlantConfig::factory()->create(['cooperative_id' => $cooperative->id]);

        $this->assertInstanceOf(EnergyCooperative::class, $config->cooperative);
        $this->assertEquals($cooperative->id, $config->cooperative->id);
    }

    /** @test */
    public function it_belongs_to_plant()
    {
        $plant = Plant::factory()->create();
        $config = CooperativePlantConfig::factory()->create(['plant_id' => $plant->id]);

        $this->assertInstanceOf(Plant::class, $config->plant);
        $this->assertEquals($plant->id, $config->plant->id);
    }

    /** @test */
    public function it_belongs_to_organization()
    {
        $organization = Organization::factory()->create();
        $config = CooperativePlantConfig::factory()->create(['organization_id' => $organization->id]);

        $this->assertInstanceOf(Organization::class, $config->organization);
        $this->assertEquals($organization->id, $config->organization->id);
    }

    /** @test */
    public function it_can_scope_active_configs()
    {
        CooperativePlantConfig::factory()->create(['active' => true]);
        CooperativePlantConfig::factory()->create(['active' => false]);

        $activeConfigs = CooperativePlantConfig::active()->get();

        $this->assertEquals(1, $activeConfigs->count());
        $this->assertTrue($activeConfigs->first()->active);
    }

    /** @test */
    public function it_can_scope_default_configs()
    {
        CooperativePlantConfig::factory()->create(['default' => true]);
        CooperativePlantConfig::factory()->create(['default' => false]);

        $defaultConfigs = CooperativePlantConfig::default()->get();

        $this->assertEquals(1, $defaultConfigs->count());
        $this->assertTrue($defaultConfigs->first()->default);
    }

    /** @test */
    public function it_can_scope_by_cooperative()
    {
        $cooperative1 = EnergyCooperative::factory()->create();
        $cooperative2 = EnergyCooperative::factory()->create();
        
        CooperativePlantConfig::factory()->create(['cooperative_id' => $cooperative1->id]);
        CooperativePlantConfig::factory()->create(['cooperative_id' => $cooperative2->id]);

        $cooperative1Configs = CooperativePlantConfig::byCooperative($cooperative1->id)->get();

        $this->assertEquals(1, $cooperative1Configs->count());
        $this->assertEquals($cooperative1->id, $cooperative1Configs->first()->cooperative_id);
    }

    /** @test */
    public function it_can_scope_by_plant()
    {
        $plant1 = Plant::factory()->create();
        $plant2 = Plant::factory()->create();
        
        CooperativePlantConfig::factory()->create(['plant_id' => $plant1->id]);
        CooperativePlantConfig::factory()->create(['plant_id' => $plant2->id]);

        $plant1Configs = CooperativePlantConfig::byPlant($plant1->id)->get();

        $this->assertEquals(1, $plant1Configs->count());
        $this->assertEquals($plant1->id, $plant1Configs->first()->plant_id);
    }

    /** @test */
    public function it_can_scope_by_organization()
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        
        CooperativePlantConfig::factory()->create(['organization_id' => $org1->id]);
        CooperativePlantConfig::factory()->create(['organization_id' => $org2->id]);

        $org1Configs = CooperativePlantConfig::byOrganization($org1->id)->get();

        $this->assertEquals(1, $org1Configs->count());
        $this->assertEquals($org1->id, $org1Configs->first()->organization_id);
    }

    /** @test */
    public function it_has_is_default_accessor()
    {
        $defaultConfig = CooperativePlantConfig::factory()->create(['default' => true]);
        $nonDefaultConfig = CooperativePlantConfig::factory()->create(['default' => false]);

        $this->assertTrue($defaultConfig->isDefault);
        $this->assertFalse($nonDefaultConfig->isDefault);
    }

    /** @test */
    public function it_has_is_active_accessor()
    {
        $activeConfig = CooperativePlantConfig::factory()->create(['active' => true]);
        $inactiveConfig = CooperativePlantConfig::factory()->create(['active' => false]);

        $this->assertTrue($activeConfig->isActive);
        $this->assertFalse($inactiveConfig->isActive);
    }

    /** @test */
    public function it_can_set_as_default()
    {
        $cooperative = EnergyCooperative::factory()->create();
        
        // Crear configuraciones existentes
        CooperativePlantConfig::factory()->create([
            'cooperative_id' => $cooperative->id,
            'default' => true
        ]);
        
        $newConfig = CooperativePlantConfig::factory()->create([
            'cooperative_id' => $cooperative->id,
            'default' => false
        ]);

        $newConfig->setAsDefault();

        $this->assertTrue($newConfig->default);
        
        // Verificar que la configuración anterior ya no es por defecto
        $this->assertDatabaseMissing('cooperative_plant_configs', [
            'cooperative_id' => $cooperative->id,
            'default' => true,
            'id' => $newConfig->id
        ]);
    }

    /** @test */
    public function it_can_remove_default_status()
    {
        $config = CooperativePlantConfig::factory()->create(['default' => true]);

        $config->removeDefault();

        $this->assertFalse($config->default);
    }

    /** @test */
    public function it_can_activate()
    {
        $config = CooperativePlantConfig::factory()->create(['active' => false]);

        $config->activate();

        $this->assertTrue($config->active);
    }

    /** @test */
    public function it_can_deactivate()
    {
        $config = CooperativePlantConfig::factory()->create(['active' => true]);

        $config->deactivate();

        $this->assertFalse($config->active);
    }

    /** @test */
    public function it_can_toggle_active_status()
    {
        $config = CooperativePlantConfig::factory()->create(['active' => false]);

        $config->toggleActive();
        $this->assertTrue($config->active);

        $config->toggleActive();
        $this->assertFalse($config->active);
    }

    /** @test */
    public function it_ensures_only_one_default_per_cooperative()
    {
        $cooperative = EnergyCooperative::factory()->create();
        
        // Crear primera configuración por defecto
        $config1 = CooperativePlantConfig::factory()->create([
            'cooperative_id' => $cooperative->id,
            'default' => true
        ]);
        
        // Crear segunda configuración por defecto
        $config2 = CooperativePlantConfig::factory()->create([
            'cooperative_id' => $cooperative->id,
            'default' => true
        ]);

        // Verificar que solo la última es por defecto
        $this->assertFalse($config1->fresh()->default);
        $this->assertTrue($config2->fresh()->default);
    }

    /** @test */
    public function it_has_factory_states()
    {
        $defaultConfig = CooperativePlantConfig::factory()->default()->create();
        $notDefaultConfig = CooperativePlantConfig::factory()->notDefault()->create();
        $activeConfig = CooperativePlantConfig::factory()->active()->create();
        $inactiveConfig = CooperativePlantConfig::factory()->inactive()->create();
        $noOrgConfig = CooperativePlantConfig::factory()->noOrganization()->create();

        $this->assertTrue($defaultConfig->default);
        $this->assertFalse($notDefaultConfig->default);
        $this->assertTrue($activeConfig->active);
        $this->assertFalse($inactiveConfig->active);
        $this->assertNull($noOrgConfig->organization_id);
    }

    /** @test */
    public function it_can_be_created_with_factory()
    {
        $config = CooperativePlantConfig::factory()->create();

        $this->assertInstanceOf(CooperativePlantConfig::class, $config);
        $this->assertNotNull($config->cooperative_id);
        $this->assertNotNull($config->plant_id);
    }

    /** @test */
    public function it_handles_organization_id_as_nullable()
    {
        $config = CooperativePlantConfig::factory()->create(['organization_id' => null]);

        $this->assertNull($config->organization_id);
        $this->assertNull($config->organization);
    }

    /** @test */
    public function it_can_have_multiple_configs_per_cooperative()
    {
        $cooperative = EnergyCooperative::factory()->create();
        $plant1 = Plant::factory()->create();
        $plant2 = Plant::factory()->create();

        $config1 = CooperativePlantConfig::factory()->create([
            'cooperative_id' => $cooperative->id,
            'plant_id' => $plant1->id
        ]);
        
        $config2 = CooperativePlantConfig::factory()->create([
            'cooperative_id' => $cooperative->id,
            'plant_id' => $plant2->id
        ]);

        $this->assertNotEquals($config1->id, $config2->id);
        $this->assertEquals($cooperative->id, $config1->cooperative_id);
        $this->assertEquals($cooperative->id, $config2->cooperative_id);
    }

    /** @test */
    public function it_can_have_multiple_configs_per_plant()
    {
        $plant = Plant::factory()->create();
        $cooperative1 = EnergyCooperative::factory()->create();
        $cooperative2 = EnergyCooperative::factory()->create();

        $config1 = CooperativePlantConfig::factory()->create([
            'plant_id' => $plant->id,
            'cooperative_id' => $cooperative1->id
        ]);
        
        $config2 = CooperativePlantConfig::factory()->create([
            'plant_id' => $plant->id,
            'cooperative_id' => $cooperative2->id
        ]);

        $this->assertNotEquals($config1->id, $config2->id);
        $this->assertEquals($plant->id, $config1->plant_id);
        $this->assertEquals($plant->id, $config2->plant_id);
    }
}
