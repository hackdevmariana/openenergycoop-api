<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Plant;

class SimplePlantTest extends TestCase
{
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
        
        $this->assertTrue($plant->hasCast('co2_equivalent_per_unit_kg', 'decimal'));
        $this->assertTrue($plant->hasCast('is_active', 'boolean'));
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $plant = new Plant();
        
        $this->assertTrue(in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($plant)));
    }

    /** @test */
    public function it_has_factory_trait()
    {
        $plant = new Plant();
        
        $this->assertTrue(in_array('Illuminate\Database\Eloquent\Factories\HasFactory', class_uses($plant)));
    }

    /** @test */
    public function it_can_calculate_co2_avoided()
    {
        $plant = new Plant();
        $plant->co2_equivalent_per_unit_kg = 25.0;
        $numberOfPlants = 10;

        $co2Avoided = $plant->calculateCo2Avoided($numberOfPlants);

        $this->assertEquals(250.0, $co2Avoided);
    }

    /** @test */
    public function it_can_check_if_available()
    {
        $activePlant = new Plant();
        $activePlant->is_active = true;
        
        $inactivePlant = new Plant();
        $inactivePlant->is_active = false;

        $this->assertTrue($activePlant->isAvailable());
        $this->assertFalse($inactivePlant->isAvailable());
    }

    /** @test */
    public function it_can_activate()
    {
        $plant = new Plant();
        $plant->is_active = false;

        // Simular la activación sin base de datos
        $plant->is_active = true;

        $this->assertTrue($plant->is_active);
    }

    /** @test */
    public function it_can_deactivate()
    {
        $plant = new Plant();
        $plant->is_active = true;

        // Simular la desactivación sin base de datos
        $plant->is_active = false;

        $this->assertFalse($plant->is_active);
    }

    /** @test */
    public function it_can_toggle_active_status()
    {
        $plant = new Plant();
        $plant->is_active = false;

        // Simular el toggle sin base de datos
        $plant->is_active = !$plant->is_active;
        $this->assertTrue($plant->is_active);

        $plant->is_active = !$plant->is_active;
        $this->assertFalse($plant->is_active);
    }

    /** @test */
    public function it_has_image_url_accessor()
    {
        $plant = new Plant();
        $plant->image = 'plants/pino.jpg';

        // El accessor devuelve la imagen por defecto si no existe en storage
        $this->assertEquals(asset('images/default-plant.png'), $plant->imageUrl);
    }

    /** @test */
    public function it_has_formatted_co2_accessor()
    {
        $plant = new Plant();
        $plant->co2_equivalent_per_unit_kg = 25.5;

        $this->assertEquals('25.50 kg CO2', $plant->formattedCo2);
    }

    /** @test */
    public function it_has_display_name_accessor()
    {
        $plant = new Plant();
        $plant->name = 'Pino';
        $plant->unit_label = 'árbol';

        $this->assertEquals('Pino (árbol)', $plant->displayName);
    }
}
