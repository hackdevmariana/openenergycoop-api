<?php

namespace Database\Factories;

use App\Models\EnergySource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergySource>
 */
class EnergySourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = $this->faker->randomElement(['renewable', 'non_renewable', 'hybrid']);
        $type = $this->faker->randomElement(['photovoltaic', 'concentrated_solar', 'wind_turbine', 'hydroelectric', 'biomass_plant', 'geothermal_plant', 'nuclear_reactor', 'coal_plant', 'gas_plant', 'other']);
        $status = $this->faker->randomElement(['active', 'inactive', 'maintenance', 'development', 'testing', 'deprecated']);
        
        // Determinar si es renovable y limpia basado en la categoría
        $isRenewable = in_array($category, ['renewable', 'hybrid']);
        $isClean = in_array($category, ['renewable', 'hybrid']) || in_array($type, ['nuclear_reactor']);
        
        // Calcular eficiencia típica basada en el tipo
        $efficiencyTypical = match($type) {
            'photovoltaic' => $this->faker->randomFloat(1, 15, 25),
            'concentrated_solar' => $this->faker->randomFloat(1, 20, 35),
            'wind_turbine' => $this->faker->randomFloat(1, 25, 45),
            'hydroelectric' => $this->faker->randomFloat(1, 80, 95),
            'biomass_plant' => $this->faker->randomFloat(1, 25, 40),
            'geothermal_plant' => $this->faker->randomFloat(1, 10, 20),
            'nuclear_reactor' => $this->faker->randomFloat(1, 30, 35),
            'coal_plant' => $this->faker->randomFloat(1, 35, 45),
            'gas_plant' => $this->faker->randomFloat(1, 45, 60),
            default => $this->faker->randomFloat(1, 20, 50),
        };
        
        // Calcular capacidad típica basada en el tipo
        $capacityTypical = match($type) {
            'photovoltaic' => $this->faker->randomFloat(1, 100, 5000),
            'concentrated_solar' => $this->faker->randomFloat(1, 50, 1000),
            'wind_turbine' => $this->faker->randomFloat(1, 2000, 8000),
            'hydroelectric' => $this->faker->randomFloat(1, 1000, 20000),
            'biomass_plant' => $this->faker->randomFloat(1, 500, 5000),
            'geothermal_plant' => $this->faker->randomFloat(1, 100, 1000),
            'nuclear_reactor' => $this->faker->randomFloat(1, 500000, 2000000),
            'coal_plant' => $this->faker->randomFloat(1, 100000, 1000000),
            'gas_plant' => $this->faker->randomFloat(1, 50000, 500000),
            default => $this->faker->randomFloat(1, 100, 1000),
        };
        
        // Calcular huella de carbono basada en el tipo
        $carbonFootprint = match($type) {
            'photovoltaic' => $this->faker->randomFloat(3, 0.02, 0.08),
            'concentrated_solar' => $this->faker->randomFloat(3, 0.01, 0.05),
            'wind_turbine' => $this->faker->randomFloat(3, 0.01, 0.04),
            'hydroelectric' => $this->faker->randomFloat(3, 0.01, 0.06),
            'biomass_plant' => $this->faker->randomFloat(3, 0.02, 0.10),
            'geothermal_plant' => $this->faker->randomFloat(3, 0.01, 0.03),
            'nuclear_reactor' => $this->faker->randomFloat(3, 0.01, 0.02),
            'coal_plant' => $this->faker->randomFloat(3, 0.8, 1.2),
            'gas_plant' => $this->faker->randomFloat(3, 0.4, 0.6),
            default => $this->faker->randomFloat(3, 0.1, 0.5),
        };
        
        // Calcular costo de instalación basado en el tipo
        $installationCost = match($type) {
            'photovoltaic' => $this->faker->randomFloat(2, 800, 2000),
            'concentrated_solar' => $this->faker->randomFloat(2, 3000, 8000),
            'wind_turbine' => $this->faker->randomFloat(2, 1200, 2500),
            'hydroelectric' => $this->faker->randomFloat(2, 1500, 4000),
            'biomass_plant' => $this->faker->randomFloat(2, 2000, 5000),
            'geothermal_plant' => $this->faker->randomFloat(2, 3000, 7000),
            'nuclear_reactor' => $this->faker->randomFloat(2, 5000, 10000),
            'coal_plant' => $this->faker->randomFloat(2, 2000, 4000),
            'gas_plant' => $this->faker->randomFloat(2, 800, 1500),
            default => $this->faker->randomFloat(2, 1500, 3500),
        };

        return [
            'name' => $this->faker->unique()->words(3, true),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(3),
            'category' => $category,
            'type' => $type,
            'status' => $status,
            
            // Especificaciones técnicas
            'technical_specs' => $this->faker->paragraphs(2, true),
            'efficiency_min' => $efficiencyTypical * 0.8,
            'efficiency_max' => $efficiencyTypical * 1.2,
            'efficiency_typical' => $efficiencyTypical,
            'capacity_min' => $capacityTypical * 0.7,
            'capacity_max' => $capacityTypical * 1.3,
            'capacity_typical' => $capacityTypical,
            'lifespan_years' => $this->faker->numberBetween(20, 50),
            'degradation_rate' => $this->faker->randomFloat(2, 0.1, 2.0),
            
            // Impacto ambiental
            'carbon_footprint_kg_kwh' => $carbonFootprint,
            'water_consumption_l_kwh' => $this->faker->randomFloat(2, 0.1, 10.0),
            'land_use_m2_kw' => $this->faker->randomFloat(2, 0.1, 100.0),
            'environmental_impact' => $this->faker->paragraphs(2, true),
            'is_renewable' => $isRenewable,
            'is_clean' => $isClean,
            'renewable_certificate' => $isRenewable ? $this->faker->uuid() : null,
            'environmental_rating' => $this->faker->randomFloat(1, 50, 100),
            
            // Aspectos financieros
            'installation_cost_per_kw' => $installationCost,
            'maintenance_cost_annual' => $installationCost * $this->faker->randomFloat(2, 0.01, 0.05),
            'operational_cost_per_kwh' => $this->faker->randomFloat(4, 0.01, 0.20),
            'levelized_cost_kwh' => $this->faker->randomFloat(4, 0.05, 0.30),
            'payback_period_years' => $this->faker->randomFloat(1, 5, 25),
            'financial_notes' => $this->faker->paragraphs(1, true),
            
            // Disponibilidad y dependencias
            'geographic_availability' => $this->faker->paragraphs(2, true),
            'weather_dependencies' => $this->faker->paragraphs(2, true),
            'seasonal_variations' => $this->faker->paragraphs(2, true),
            'capacity_factor_min' => $this->faker->randomFloat(1, 10, 40),
            'capacity_factor_max' => $this->faker->randomFloat(1, 50, 90),
            
            // Tecnología y equipamiento
            'technology_description' => $this->faker->paragraphs(3, true),
            'manufacturer' => $this->faker->company(),
            'model_series' => $this->faker->words(2, true),
            'warranty_years' => $this->faker->randomFloat(1, 5, 25),
            'certification_standards' => $this->faker->words(3, true),
            'maintenance_requirements' => $this->faker->paragraphs(2, true),
            
            // Configuración del sistema
            'is_active' => $this->faker->boolean(80),
            'is_featured' => $this->faker->boolean(20),
            'is_public' => $this->faker->boolean(90),
            'requires_approval' => $this->faker->boolean(30),
            'icon' => $this->faker->randomElement(['heroicon-o-bolt', 'heroicon-o-sun', 'heroicon-o-wind', 'heroicon-o-droplet', 'heroicon-o-fire', 'heroicon-o-cog']),
            'color' => $this->faker->hexColor(),
            'sort_order' => $this->faker->numberBetween(0, 1000),
            
            // Metadatos y etiquetas
            'tags' => $this->faker->words(3),
            'notes' => $this->faker->paragraphs(1, true),
            
            // Relaciones
            'managed_by' => User::factory(),
            'created_by' => User::factory(),
            'approved_by' => $this->faker->optional(0.7, null)->randomElement([User::factory()]),
            'approved_at' => $this->faker->optional(0.7, null)->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Estado para fuentes de energía renovables
     */
    public function renewable(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'renewable',
            'is_renewable' => true,
            'is_clean' => true,
            'carbon_footprint_kg_kwh' => $this->faker->randomFloat(3, 0.01, 0.10),
            'environmental_rating' => $this->faker->randomFloat(1, 80, 100),
        ]);
    }

    /**
     * Estado para fuentes de energía no renovables
     */
    public function nonRenewable(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'non_renewable',
            'is_renewable' => false,
            'is_clean' => false,
            'carbon_footprint_kg_kwh' => $this->faker->randomFloat(3, 0.4, 1.5),
            'environmental_rating' => $this->faker->randomFloat(1, 20, 60),
        ]);
    }

    /**
     * Estado para fuentes de energía híbridas
     */
    public function hybrid(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'hybrid',
            'is_renewable' => true,
            'is_clean' => true,
            'carbon_footprint_kg_kwh' => $this->faker->randomFloat(3, 0.02, 0.15),
            'environmental_rating' => $this->faker->randomFloat(1, 70, 95),
        ]);
    }

    /**
     * Estado para fuentes de energía solares
     */
    public function solar(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $this->faker->randomElement(['photovoltaic', 'concentrated_solar']),
            'category' => 'renewable',
            'is_renewable' => true,
            'is_clean' => true,
            'efficiency_typical' => $this->faker->randomFloat(1, 15, 35),
            'capacity_typical' => $this->faker->randomFloat(1, 50, 5000),
            'carbon_footprint_kg_kwh' => $this->faker->randomFloat(3, 0.02, 0.08),
            'installation_cost_per_kw' => $this->faker->randomFloat(2, 800, 8000),
            'icon' => 'heroicon-o-sun',
            'color' => '#fbbf24',
        ]);
    }

    /**
     * Estado para fuentes de energía eólicas
     */
    public function wind(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'wind_turbine',
            'category' => 'renewable',
            'is_renewable' => true,
            'is_clean' => true,
            'efficiency_typical' => $this->faker->randomFloat(1, 25, 45),
            'capacity_typical' => $this->faker->randomFloat(1, 2000, 8000),
            'carbon_footprint_kg_kwh' => $this->faker->randomFloat(3, 0.01, 0.04),
            'installation_cost_per_kw' => $this->faker->randomFloat(2, 1200, 2500),
            'icon' => 'heroicon-o-wind',
            'color' => '#3b82f6',
        ]);
    }

    /**
     * Estado para fuentes de energía hidroeléctricas
     */
    public function hydroelectric(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'hydroelectric',
            'category' => 'renewable',
            'is_renewable' => true,
            'is_clean' => true,
            'efficiency_typical' => $this->faker->randomFloat(1, 80, 95),
            'capacity_typical' => $this->faker->randomFloat(1, 1000, 20000),
            'carbon_footprint_kg_kwh' => $this->faker->randomFloat(3, 0.01, 0.06),
            'installation_cost_per_kw' => $this->faker->randomFloat(2, 1500, 4000),
            'icon' => 'heroicon-o-droplet',
            'color' => '#06b6d4',
        ]);
    }

    /**
     * Estado para fuentes de energía nucleares
     */
    public function nuclear(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'nuclear_reactor',
            'category' => 'non_renewable',
            'is_renewable' => false,
            'is_clean' => true,
            'efficiency_typical' => $this->faker->randomFloat(1, 30, 35),
            'capacity_typical' => $this->faker->randomFloat(1, 500000, 2000000),
            'carbon_footprint_kg_kwh' => $this->faker->randomFloat(3, 0.01, 0.02),
            'installation_cost_per_kw' => $this->faker->randomFloat(2, 5000, 10000),
            'icon' => 'heroicon-o-cog',
            'color' => '#8b5cf6',
        ]);
    }

    /**
     * Estado para fuentes de energía de biomasa
     */
    public function biomass(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'biomass_plant',
            'category' => 'renewable',
            'is_renewable' => true,
            'is_clean' => true,
            'efficiency_typical' => $this->faker->randomFloat(1, 25, 40),
            'capacity_typical' => $this->faker->randomFloat(1, 500, 5000),
            'carbon_footprint_kg_kwh' => $this->faker->randomFloat(3, 0.02, 0.10),
            'installation_cost_per_kw' => $this->faker->randomFloat(2, 2000, 5000),
            'icon' => 'heroicon-o-fire',
            'color' => '#10b981',
        ]);
    }

    /**
     * Estado para fuentes de energía geotérmicas
     */
    public function geothermal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'geothermal_plant',
            'category' => 'renewable',
            'is_renewable' => true,
            'is_clean' => true,
            'efficiency_typical' => $this->faker->randomFloat(1, 10, 20),
            'capacity_typical' => $this->faker->randomFloat(1, 100, 1000),
            'carbon_footprint_kg_kwh' => $this->faker->randomFloat(3, 0.01, 0.03),
            'installation_cost_per_kw' => $this->faker->randomFloat(2, 3000, 7000),
            'icon' => 'heroicon-o-fire',
            'color' => '#dc2626',
        ]);
    }

    /**
     * Estado para fuentes de energía activas
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    /**
     * Estado para fuentes de energía inactivas
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
            'is_active' => false,
        ]);
    }

    /**
     * Estado para fuentes de energía en mantenimiento
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
            'is_active' => false,
        ]);
    }

    /**
     * Estado para fuentes de energía en desarrollo
     */
    public function development(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'development',
            'is_active' => false,
        ]);
    }

    /**
     * Estado para fuentes de energía en pruebas
     */
    public function testing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'testing',
            'is_active' => false,
        ]);
    }

    /**
     * Estado para fuentes de energía obsoletas
     */
    public function deprecated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'deprecated',
            'is_active' => false,
        ]);
    }

    /**
     * Estado para fuentes de energía destacadas
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Estado para fuentes de energía públicas
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Estado para fuentes de energía de alta eficiencia
     */
    public function highEfficiency(): static
    {
        return $this->state(fn (array $attributes) => [
            'efficiency_typical' => $this->faker->randomFloat(1, 80, 95),
            'efficiency_min' => $this->faker->randomFloat(1, 75, 85),
            'efficiency_max' => $this->faker->randomFloat(1, 90, 98),
        ]);
    }

    /**
     * Estado para fuentes de energía de baja eficiencia
     */
    public function lowEfficiency(): static
    {
        return $this->state(fn (array $attributes) => [
            'efficiency_typical' => $this->faker->randomFloat(1, 10, 40),
            'efficiency_min' => $this->faker->randomFloat(1, 8, 35),
            'efficiency_max' => $this->faker->randomFloat(1, 15, 45),
        ]);
    }

    /**
     * Estado para fuentes de energía de alta capacidad
     */
    public function highCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity_typical' => $this->faker->randomFloat(1, 10000, 100000),
            'capacity_min' => $this->faker->randomFloat(1, 8000, 80000),
            'capacity_max' => $this->faker->randomFloat(1, 12000, 120000),
        ]);
    }

    /**
     * Estado para fuentes de energía de baja capacidad
     */
    public function lowCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity_typical' => $this->faker->randomFloat(1, 10, 100),
            'capacity_min' => $this->faker->randomFloat(1, 8, 80),
            'capacity_max' => $this->faker->randomFloat(1, 12, 120),
        ]);
    }

    /**
     * Estado para fuentes de energía de bajo costo
     */
    public function lowCost(): static
    {
        return $this->state(fn (array $attributes) => [
            'installation_cost_per_kw' => $this->faker->randomFloat(2, 500, 1500),
            'maintenance_cost_annual' => $this->faker->randomFloat(2, 5, 50),
            'operational_cost_per_kwh' => $this->faker->randomFloat(4, 0.01, 0.10),
        ]);
    }

    /**
     * Estado para fuentes de energía de alto costo
     */
    public function highCost(): static
    {
        return $this->state(fn (array $attributes) => [
            'installation_cost_per_kw' => $this->faker->randomFloat(2, 5000, 15000),
            'maintenance_cost_annual' => $this->faker->randomFloat(2, 100, 500),
            'operational_cost_per_kwh' => $this->faker->randomFloat(4, 0.15, 0.50),
        ]);
    }

    /**
     * Estado para fuentes de energía de larga vida útil
     */
    public function longLifespan(): static
    {
        return $this->state(fn (array $attributes) => [
            'lifespan_years' => $this->faker->numberBetween(40, 80),
            'degradation_rate' => $this->faker->randomFloat(2, 0.05, 0.5),
        ]);
    }

    /**
     * Estado para fuentes de energía de corta vida útil
     */
    public function shortLifespan(): static
    {
        return $this->state(fn (array $attributes) => [
            'lifespan_years' => $this->faker->numberBetween(10, 25),
            'degradation_rate' => $this->faker->randomFloat(2, 1.0, 3.0),
        ]);
    }

    /**
     * Estado para fuentes de energía aprobadas
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Estado para fuentes de energía pendientes de aprobación
     */
    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_by' => null,
            'approved_at' => null,
            'requires_approval' => true,
        ]);
    }
}
