<?php

namespace Database\Factories;

use App\Models\EnergyInstallation;
use App\Models\EnergySource;
use App\Models\Customer;
use App\Models\ProductionProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnergyInstallation>
 */
class EnergyInstallationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $installationTypes = array_keys(EnergyInstallation::getInstallationTypes());
        $statuses = array_keys(EnergyInstallation::getStatuses());
        $priorities = array_keys(EnergyInstallation::getPriorities());
        
        $installationType = $this->faker->randomElement($installationTypes);
        $status = $this->faker->randomElement($statuses);
        $priority = $this->faker->randomElement($priorities);
        
        // Calcular capacidad basada en el tipo de instalación
        $installedCapacity = $this->getCapacityByType($installationType);
        $operationalCapacity = $status === 'operational' ? $installedCapacity * $this->faker->randomFloat(2, 0.8, 1.0) : null;
        
        // Calcular eficiencia basada en el tipo y estado
        $efficiencyRating = $this->getEfficiencyByTypeAndStatus($installationType, $status);
        
        // Calcular costos basados en la capacidad
        $installationCost = $installedCapacity * $this->faker->randomFloat(2, 800, 1200); // $/kW
        $maintenanceCostPerYear = $installationCost * $this->faker->randomFloat(2, 0.02, 0.05); // 2-5% del costo de instalación
        
        // Generar fechas realistas
        $installationDate = $this->faker->dateTimeBetween('-2 years', 'now');
        $commissioningDate = $status === 'operational' ? $this->faker->dateTimeBetween($installationDate, '+1 month') : null;
        $warrantyExpiryDate = $this->faker->dateTimeBetween($installationDate, '+5 years');
        $nextMaintenanceDate = $status === 'operational' ? $this->faker->dateTimeBetween('now', '+6 months') : null;
        $approvalDate = $status !== 'planned' ? $this->faker->dateTimeBetween($installationDate, '+1 month') : null;

        return [
            'installation_number' => 'INST-' . str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'name' => $this->generateInstallationName($installationType),
            'description' => $this->faker->paragraph(3),
            'installation_type' => $installationType,
            'status' => $status,
            'priority' => $priority,
            'energy_source_id' => EnergySource::factory(),
            'customer_id' => Customer::factory(),
            'project_id' => ProductionProject::factory(),
            'installed_capacity_kw' => $installedCapacity,
            'operational_capacity_kw' => $operationalCapacity,
            'efficiency_rating' => $efficiencyRating,
            'installation_date' => $installationDate,
            'commissioning_date' => $commissioningDate,
            'location_address' => $this->faker->address(),
            'location_coordinates' => $this->faker->latitude() . ',' . $this->faker->longitude(),
            'installation_cost' => $installationCost,
            'maintenance_cost_per_year' => $maintenanceCostPerYear,
            'warranty_expiry_date' => $warrantyExpiryDate,
            'next_maintenance_date' => $nextMaintenanceDate,
            'notes' => $this->faker->optional(0.7)->paragraph(2),
            'is_active' => $status !== 'decommissioned' && $status !== 'cancelled',
            'is_public' => $this->faker->boolean(80), // 80% de probabilidad de ser público
            'installed_by_id' => User::factory(),
            'managed_by_id' => User::factory(),
            'created_by_id' => User::factory(),
            'approved_by_id' => $status !== 'planned' ? User::factory() : null,
            'approval_date' => $approvalDate,
            'technical_specifications' => $this->generateTechnicalSpecifications($installationType),
            'safety_certifications' => $this->generateSafetyCertifications(),
            'environmental_impact_data' => $this->generateEnvironmentalImpactData($installationType, $installedCapacity),
            'performance_metrics' => $this->generatePerformanceMetrics($installationType, $efficiencyRating),
            'maintenance_schedule' => $this->generateMaintenanceSchedule($installationType),
            'emergency_contacts' => $this->generateEmergencyContacts(),
            'documentation_files' => $this->generateDocumentationFiles(),
            'tags' => $this->generateTags($installationType),
            'custom_fields' => $this->generateCustomFields($installationType),
        ];
    }

    /**
     * Estado para instalaciones residenciales
     */
    public function residential(): static
    {
        return $this->state(fn (array $attributes) => [
            'installation_type' => 'residential',
            'installed_capacity_kw' => $this->faker->randomFloat(2, 3, 20),
            'priority' => $this->faker->randomElement(['low', 'medium']),
        ]);
    }

    /**
     * Estado para instalaciones comerciales
     */
    public function commercial(): static
    {
        return $this->state(fn (array $attributes) => [
            'installation_type' => 'commercial',
            'installed_capacity_kw' => $this->faker->randomFloat(2, 20, 500),
            'priority' => $this->faker->randomElement(['medium', 'high']),
        ]);
    }

    /**
     * Estado para instalaciones industriales
     */
    public function industrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'installation_type' => 'industrial',
            'installed_capacity_kw' => $this->faker->randomFloat(2, 500, 10000),
            'priority' => $this->faker->randomElement(['high', 'urgent', 'critical']),
        ]);
    }

    /**
     * Estado para instalaciones de escala de utilidad
     */
    public function utilityScale(): static
    {
        return $this->state(fn (array $attributes) => [
            'installation_type' => 'utility_scale',
            'installed_capacity_kw' => $this->faker->randomFloat(2, 10000, 100000),
            'priority' => 'critical',
        ]);
    }

    /**
     * Estado para instalaciones operativas
     */
    public function operational(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'operational',
            'is_active' => true,
            'commissioning_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'operational_capacity_kw' => fn (array $attributes) => $attributes['installed_capacity_kw'] * $this->faker->randomFloat(2, 0.85, 0.98),
        ]);
    }

    /**
     * Estado para instalaciones en mantenimiento
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
            'is_active' => true,
            'next_maintenance_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Estado para instalaciones planificadas
     */
    public function planned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'planned',
            'is_active' => false,
            'commissioning_date' => null,
            'operational_capacity_kw' => null,
            'efficiency_rating' => null,
        ]);
    }

    /**
     * Estado para instalaciones de alta prioridad
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->randomElement(['high', 'urgent', 'critical']),
        ]);
    }

    /**
     * Estado para instalaciones de baja prioridad
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'low',
        ]);
    }

    /**
     * Estado para instalaciones activas
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Estado para instalaciones inactivas
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Estado para instalaciones públicas
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Estado para instalaciones privadas
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Estado para instalaciones de alta eficiencia
     */
    public function highEfficiency(): static
    {
        return $this->state(fn (array $attributes) => [
            'efficiency_rating' => $this->faker->randomFloat(2, 90, 98),
        ]);
    }

    /**
     * Estado para instalaciones de baja eficiencia
     */
    public function lowEfficiency(): static
    {
        return $this->state(fn (array $attributes) => [
            'efficiency_rating' => $this->faker->randomFloat(2, 60, 75),
        ]);
    }

    /**
     * Estado para instalaciones de alto costo
     */
    public function highCost(): static
    {
        return $this->state(fn (array $attributes) => [
            'installation_cost' => fn (array $attributes) => $attributes['installed_capacity_kw'] * $this->faker->randomFloat(2, 1500, 2000),
        ]);
    }

    /**
     * Estado para instalaciones de bajo costo
     */
    public function lowCost(): static
    {
        return $this->state(fn (array $attributes) => [
            'installation_cost' => fn (array $attributes) => $attributes['installed_capacity_kw'] * $this->faker->randomFloat(2, 500, 800),
        ]);
    }

    /**
     * Estado para instalaciones recientes
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'installation_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Estado para instalaciones antiguas
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'installation_date' => $this->faker->dateTimeBetween('-10 years', '-5 years'),
            'created_at' => $this->faker->dateTimeBetween('-10 years', '-5 years'),
        ]);
    }

    /**
     * Estado para instalaciones con mantenimiento próximo
     */
    public function maintenanceDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_maintenance_date' => $this->faker->dateTimeBetween('now', '+2 weeks'),
        ]);
    }

    /**
     * Estado para instalaciones con mantenimiento atrasado
     */
    public function overdueMaintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_maintenance_date' => $this->faker->dateTimeBetween('-2 weeks', 'now'),
        ]);
    }

    /**
     * Estado para instalaciones bajo garantía
     */
    public function underWarranty(): static
    {
        return $this->state(fn (array $attributes) => [
            'warranty_expiry_date' => $this->faker->dateTimeBetween('now', '+2 years'),
        ]);
    }

    /**
     * Estado para instalaciones con garantía expirada
     */
    public function warrantyExpired(): static
    {
        return $this->state(fn (array $attributes) => [
            'warranty_expiry_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ]);
    }

    /**
     * Obtener capacidad basada en el tipo de instalación
     */
    private function getCapacityByType(string $type): float
    {
        return match ($type) {
            'residential' => $this->faker->randomFloat(2, 3, 20),
            'commercial' => $this->faker->randomFloat(2, 20, 500),
            'industrial' => $this->faker->randomFloat(2, 500, 10000),
            'utility_scale' => $this->faker->randomFloat(2, 10000, 100000),
            'community' => $this->faker->randomFloat(2, 100, 2000),
            'microgrid' => $this->faker->randomFloat(2, 50, 1000),
            'off_grid' => $this->faker->randomFloat(2, 5, 100),
            'grid_tied' => $this->faker->randomFloat(2, 10, 5000),
            default => $this->faker->randomFloat(2, 100, 1000),
        };
    }

    /**
     * Obtener eficiencia basada en el tipo y estado de la instalación
     */
    private function getEfficiencyByTypeAndStatus(string $type, string $status): ?float
    {
        if ($status === 'planned') {
            return null;
        }

        $baseEfficiency = match ($type) {
            'residential' => $this->faker->randomFloat(2, 75, 90),
            'commercial' => $this->faker->randomFloat(2, 80, 92),
            'industrial' => $this->faker->randomFloat(2, 85, 95),
            'utility_scale' => $this->faker->randomFloat(2, 88, 98),
            'community' => $this->faker->randomFloat(2, 82, 94),
            'microgrid' => $this->faker->randomFloat(2, 78, 90),
            'off_grid' => $this->faker->randomFloat(2, 70, 85),
            'grid_tied' => $this->faker->randomFloat(2, 80, 93),
            default => $this->faker->randomFloat(2, 75, 90),
        };

        // Ajustar eficiencia basada en el estado
        return match ($status) {
            'operational' => $baseEfficiency,
            'maintenance' => $baseEfficiency * $this->faker->randomFloat(2, 0.7, 0.9),
            'completed' => $baseEfficiency * $this->faker->randomFloat(2, 0.9, 1.0),
            default => $baseEfficiency * $this->faker->randomFloat(2, 0.6, 0.8),
        };
    }

    /**
     * Generar nombre de instalación basado en el tipo
     */
    private function generateInstallationName(string $type): string
    {
        $prefixes = match ($type) {
            'residential' => ['Residential', 'Home', 'Household', 'Family'],
            'commercial' => ['Commercial', 'Business', 'Office', 'Retail'],
            'industrial' => ['Industrial', 'Factory', 'Manufacturing', 'Production'],
            'utility_scale' => ['Utility Scale', 'Power Plant', 'Energy Farm', 'Generation Station'],
            'community' => ['Community', 'Neighborhood', 'Shared', 'Collective'],
            'microgrid' => ['Microgrid', 'Local Grid', 'Community Grid', 'Island Grid'],
            'off_grid' => ['Off Grid', 'Standalone', 'Independent', 'Autonomous'],
            'grid_tied' => ['Grid Tied', 'Connected', 'Integrated', 'Synchronized'],
            default => ['Energy', 'Power', 'Generation', 'Installation'],
        };

        $technologies = ['Solar', 'Wind', 'Hydro', 'Biomass', 'Geothermal', 'Hybrid'];
        $suffixes = ['Installation', 'System', 'Plant', 'Facility', 'Station', 'Project'];

        $prefix = $this->faker->randomElement($prefixes);
        $technology = $this->faker->randomElement($technologies);
        $suffix = $this->faker->randomElement($suffixes);

        return "{$prefix} {$technology} {$suffix}";
    }

    /**
     * Generar especificaciones técnicas
     */
    private function generateTechnicalSpecifications(string $type): array
    {
        $specs = [
            'voltage' => $this->faker->randomElement(['120V', '240V', '480V', '1000V', '1500V']),
            'frequency' => $this->faker->randomElement(['50Hz', '60Hz']),
            'power_factor' => $this->faker->randomFloat(2, 0.85, 0.98),
            'efficiency_class' => $this->faker->randomElement(['A', 'A+', 'A++', 'B', 'C']),
        ];

        if (in_array($type, ['residential', 'commercial'])) {
            $specs['inverter_type'] = $this->faker->randomElement(['String', 'Micro', 'Central', 'Hybrid']);
            $specs['battery_backup'] = $this->faker->boolean(30);
        }

        if (in_array($type, ['industrial', 'utility_scale'])) {
            $specs['transformer_rating'] = $this->faker->randomFloat(2, 100, 1000) . 'kVA';
            $specs['switchgear_rating'] = $this->faker->randomFloat(2, 100, 1000) . 'A';
        }

        return $specs;
    }

    /**
     * Generar certificaciones de seguridad
     */
    private function generateSafetyCertifications(): array
    {
        $certifications = [];
        $possibleCerts = ['UL', 'CE', 'IEC', 'ISO', 'ANSI', 'IEEE', 'NEC'];

        $numCerts = $this->faker->numberBetween(2, 5);
        $selectedCerts = $this->faker->randomElements($possibleCerts, $numCerts);

        foreach ($selectedCerts as $cert) {
            $certifications[$cert] = [
                'certification_number' => $cert . '-' . $this->faker->regexify('[A-Z0-9]{8}'),
                'issue_date' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                'expiry_date' => $this->faker->dateTimeBetween('now', '+3 years')->format('Y-m-d'),
                'status' => $this->faker->randomElement(['active', 'pending', 'expired']),
            ];
        }

        return $certifications;
    }

    /**
     * Generar datos de impacto ambiental
     */
    private function generateEnvironmentalImpactData(string $type, float $capacity): array
    {
        $annualEnergyProduction = $capacity * 8760 * 0.25; // 25% factor de capacidad promedio
        $carbonOffset = $annualEnergyProduction * 0.5; // 0.5 kg CO2/kWh evitado

        return [
            'annual_energy_production_kwh' => round($annualEnergyProduction, 2),
            'carbon_offset_kg_co2' => round($carbonOffset, 2),
            'water_savings_liters' => round($annualEnergyProduction * 0.1, 2), // 0.1 L/kWh
            'air_quality_improvement' => $this->faker->randomFloat(2, 0.8, 1.2),
            'noise_level_db' => $this->faker->randomFloat(1, 30, 65),
            'land_use_efficiency' => $this->faker->randomFloat(2, 0.5, 2.0), // kW/m²
        ];
    }

    /**
     * Generar métricas de rendimiento
     */
    private function generatePerformanceMetrics(string $type, ?float $efficiency): array
    {
        $metrics = [
            'availability_factor' => $this->faker->randomFloat(2, 0.85, 0.99),
            'capacity_factor' => $this->faker->randomFloat(2, 0.15, 0.35),
            'response_time_minutes' => $this->faker->numberBetween(1, 30),
        ];

        if ($efficiency) {
            $metrics['efficiency_trend'] = [
                'month_1' => $efficiency * $this->faker->randomFloat(2, 0.95, 1.05),
                'month_2' => $efficiency * $this->faker->randomFloat(2, 0.95, 1.05),
                'month_3' => $efficiency * $this->faker->randomFloat(2, 0.95, 1.05),
            ];
        }

        return $metrics;
    }

    /**
     * Generar programa de mantenimiento
     */
    private function generateMaintenanceSchedule(string $type): array
    {
        $frequency = match ($type) {
            'residential' => 'annual',
            'commercial' => 'semi_annual',
            'industrial' => 'quarterly',
            'utility_scale' => 'monthly',
            default => 'annual',
        };

        return [
            'frequency' => $frequency,
            'last_maintenance' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'next_maintenance' => $this->faker->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'maintenance_type' => $this->faker->randomElement(['preventive', 'predictive', 'corrective']),
            'estimated_duration_hours' => $this->faker->numberBetween(2, 24),
            'required_personnel' => $this->faker->numberBetween(1, 5),
        ];
    }

    /**
     * Generar contactos de emergencia
     */
    private function generateEmergencyContacts(): array
    {
        $contacts = [];
        $numContacts = $this->faker->numberBetween(2, 4);

        for ($i = 0; $i < $numContacts; $i++) {
            $contacts[] = [
                'name' => $this->faker->name(),
                'role' => $this->faker->randomElement(['Manager', 'Technician', 'Supervisor', 'Engineer']),
                'phone' => $this->faker->phoneNumber(),
                'email' => $this->faker->email(),
                'availability' => $this->faker->randomElement(['24/7', 'Business Hours', 'On Call', 'Weekends']),
                'priority' => $this->faker->randomElement(['primary', 'secondary', 'backup']),
            ];
        }

        return $contacts;
    }

    /**
     * Generar archivos de documentación
     */
    private function generateDocumentationFiles(): array
    {
        $files = [];
        $fileTypes = ['manual', 'schematic', 'warranty', 'certification', 'maintenance', 'safety'];

        foreach ($fileTypes as $type) {
            if ($this->faker->boolean(70)) { // 70% de probabilidad de tener cada tipo
                $files[] = [
                    'type' => $type,
                    'filename' => "{$type}_document.pdf",
                    'size_mb' => $this->faker->randomFloat(2, 0.5, 10.0),
                    'upload_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                    'version' => $this->faker->randomElement(['1.0', '1.1', '2.0', '2.1']),
                ];
            }
        }

        return $files;
    }

    /**
     * Generar etiquetas basadas en el tipo de instalación
     */
    private function generateTags(string $type): array
    {
        $baseTags = ['energy', 'renewable', 'sustainable'];
        
        $typeTags = match ($type) {
            'residential' => ['home', 'family', 'domestic'],
            'commercial' => ['business', 'office', 'retail'],
            'industrial' => ['factory', 'manufacturing', 'production'],
            'utility_scale' => ['utility', 'power-plant', 'large-scale'],
            'community' => ['community', 'shared', 'collective'],
            'microgrid' => ['microgrid', 'local', 'distributed'],
            'off_grid' => ['off-grid', 'standalone', 'independent'],
            'grid_tied' => ['grid-tied', 'connected', 'integrated'],
            default => ['general', 'standard'],
        };

        $technologyTags = $this->faker->randomElements(['solar', 'wind', 'hydro', 'biomass', 'geothermal', 'hybrid'], 1);
        $featureTags = $this->faker->randomElements(['smart', 'automated', 'monitored', 'efficient', 'green'], 2);

        return array_merge($baseTags, $typeTags, $technologyTags, $featureTags);
    }

    /**
     * Generar campos personalizados
     */
    private function generateCustomFields(string $type): array
    {
        $customFields = [
            'installation_notes' => $this->faker->optional(0.8)->sentence(),
            'special_requirements' => $this->faker->optional(0.3)->sentence(),
            'local_regulations' => $this->faker->optional(0.6)->sentence(),
        ];

        if (in_array($type, ['residential', 'commercial'])) {
            $customFields['aesthetic_requirements'] = $this->faker->optional(0.7)->sentence();
            $customFields['noise_restrictions'] = $this->faker->optional(0.4)->sentence();
        }

        if (in_array($type, ['industrial', 'utility_scale'])) {
            $customFields['safety_protocols'] = $this->faker->optional(0.9)->sentence();
            $customFields['environmental_compliance'] = $this->faker->optional(0.8)->sentence();
        }

        return $customFields;
    }
}
