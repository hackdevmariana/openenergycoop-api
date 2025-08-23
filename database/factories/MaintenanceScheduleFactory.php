<?php

namespace Database\Factories;

use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceScheduleFactory extends Factory
{
    protected $model = MaintenanceSchedule::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'schedule_type' => $this->faker->randomElement(array_keys(MaintenanceSchedule::getScheduleTypes())),
            'frequency_type' => $this->faker->randomElement(array_keys(MaintenanceSchedule::getFrequencyTypes())),
            'frequency_value' => $this->faker->numberBetween(1, 30),
            'priority' => $this->faker->randomElement(array_keys(MaintenanceSchedule::getPriorities())),
            'department' => $this->faker->randomElement(['maintenance', 'operations', 'engineering', 'facilities']),
            'category' => $this->faker->randomElement(['equipment', 'facility', 'infrastructure', 'vehicle', 'building']),
            'equipment_id' => $this->faker->optional()->numberBetween(1, 100),
            'location_id' => $this->faker->optional()->numberBetween(1, 100),
            'vendor_id' => $this->faker->optional()->numberBetween(1, 100),
            'task_template_id' => $this->faker->optional()->numberBetween(1, 100),
            'checklist_template_id' => $this->faker->optional()->numberBetween(1, 100),
            'estimated_duration_hours' => $this->faker->randomFloat(2, 0.5, 40.0),
            'estimated_cost' => $this->faker->randomFloat(2, 100.0, 10000.0),
            'is_active' => $this->faker->boolean(80),
            'auto_generate_tasks' => $this->faker->boolean(60),
            'send_notifications' => $this->faker->boolean(70),
            'notification_emails' => $this->faker->optional()->randomElements([
                $this->faker->email(),
                $this->faker->email(),
                $this->faker->email(),
            ], $this->faker->numberBetween(1, 3)),
            'start_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'end_date' => $this->faker->optional()->dateTimeBetween('+2 months', '+1 year'),
            'next_maintenance_date' => $this->faker->optional()->dateTimeBetween('now', '+2 weeks'),
            'last_maintenance_date' => $this->faker->optional()->dateTimeBetween('-6 months', 'now'),
            'maintenance_window_start' => $this->faker->optional()->time('H:i'),
            'maintenance_window_end' => $this->faker->optional()->time('H:i'),
            'weather_dependent' => $this->faker->boolean(30),
            'weather_conditions' => $this->faker->optional()->randomElements([
                'clear', 'partly_cloudy', 'cloudy', 'rain', 'snow', 'windy'
            ], $this->faker->numberBetween(1, 3)),
            'required_skills' => $this->faker->randomElements([
                'electrical', 'mechanical', 'plumbing', 'carpentry', 'welding',
                'HVAC', 'electronics', 'automation', 'safety', 'quality_control'
            ], $this->faker->numberBetween(2, 5)),
            'required_tools' => $this->faker->randomElements([
                'multimeter', 'wrench_set', 'screwdriver_set', 'drill', 'saw',
                'pliers', 'hammer', 'level', 'caliper', 'thermometer'
            ], $this->faker->numberBetween(3, 6)),
            'required_materials' => $this->faker->randomElements([
                'lubricant', 'gaskets', 'bolts', 'nuts', 'washers', 'seals',
                'filters', 'belts', 'bearings', 'oils', 'greases'
            ], $this->faker->numberBetween(2, 5)),
            'safety_requirements' => $this->faker->optional()->randomElements([
                'safety_glasses', 'hard_hat', 'safety_shoes', 'gloves',
                'hearing_protection', 'respirator', 'fall_protection'
            ], $this->faker->numberBetween(1, 4)),
            'quality_standards' => $this->faker->optional()->randomElements([
                'ISO_9001', 'ISO_14001', 'OHSAS_18001', 'API_standards',
                'ASME_standards', 'ASTM_standards', 'company_standards'
            ], $this->faker->numberBetween(1, 3)),
            'compliance_requirements' => $this->faker->optional()->randomElements([
                'OSHA_compliance', 'EPA_regulations', 'local_codes',
                'industry_standards', 'safety_regulations', 'environmental_laws'
            ], $this->faker->numberBetween(1, 3)),
            'tags' => $this->faker->optional()->words(3),
            'notes' => $this->faker->optional()->paragraph(),
            'created_by' => User::factory(),
            'approved_by' => $this->faker->optional()->randomElement([User::factory(), null]),
            'approved_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function preventive(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => 'preventive',
        ]);
    }

    public function predictive(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => 'predictive',
        ]);
    }

    public function conditionBased(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => 'condition_based',
        ]);
    }

    public function corrective(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => 'corrective',
        ]);
    }

    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => 'emergency',
        ]);
    }

    public function planned(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => $this->faker->randomElement(['preventive', 'predictive', 'condition_based']),
        ]);
    }

    public function unplanned(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_type' => $this->faker->randomElement(['corrective', 'emergency']),
        ]);
    }

    public function daily(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency_type' => 'daily',
            'frequency_value' => $this->faker->numberBetween(1, 7),
        ]);
    }

    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency_type' => 'weekly',
            'frequency_value' => $this->faker->numberBetween(1, 4),
        ]);
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency_type' => 'monthly',
            'frequency_value' => $this->faker->numberBetween(1, 12),
        ]);
    }

    public function quarterly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency_type' => 'quarterly',
            'frequency_value' => $this->faker->numberBetween(1, 4),
        ]);
    }

    public function biannual(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency_type' => 'biannual',
            'frequency_value' => $this->faker->numberBetween(1, 2),
        ]);
    }

    public function annual(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency_type' => 'annual',
            'frequency_value' => 1,
        ]);
    }

    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency_type' => 'custom',
            'frequency_value' => $this->faker->numberBetween(1, 365),
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    public function mediumPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'medium',
        ]);
    }

    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'low',
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'critical',
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_at' => now(),
            'approved_by' => User::factory(),
        ]);
    }

    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    public function withAutoGenerateTasks(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_generate_tasks' => true,
        ]);
    }

    public function withoutAutoGenerateTasks(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_generate_tasks' => false,
        ]);
    }

    public function withNotifications(): static
    {
        return $this->state(fn (array $attributes) => [
            'send_notifications' => true,
            'notification_emails' => [
                $this->faker->email(),
                $this->faker->email(),
                $this->faker->email(),
            ],
        ]);
    }

    public function withoutNotifications(): static
    {
        return $this->state(fn (array $attributes) => [
            'send_notifications' => false,
            'notification_emails' => [],
        ]);
    }

    public function weatherDependent(): static
    {
        return $this->state(fn (array $attributes) => [
            'weather_dependent' => true,
            'weather_conditions' => [
                'clear', 'partly_cloudy', 'no_rain', 'light_wind'
            ],
        ]);
    }

    public function notWeatherDependent(): static
    {
        return $this->state(fn (array $attributes) => [
            'weather_dependent' => false,
            'weather_conditions' => [],
        ]);
    }

    public function withMaintenanceWindow(): static
    {
        return $this->state(fn (array $attributes) => [
            'maintenance_window_start' => '08:00',
            'maintenance_window_end' => '16:00',
        ]);
    }

    public function withoutMaintenanceWindow(): static
    {
        return $this->state(fn (array $attributes) => [
            'maintenance_window_start' => null,
            'maintenance_window_end' => null,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_maintenance_date' => now()->subDays($this->faker->numberBetween(1, 30)),
        ]);
    }

    public function dueSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_maintenance_date' => now()->addDays($this->faker->numberBetween(1, 7)),
        ]);
    }

    public function onTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_maintenance_date' => now()->addDays($this->faker->numberBetween(8, 30)),
        ]);
    }

    public function withEquipment(): static
    {
        return $this->state(fn (array $attributes) => [
            'equipment_id' => $this->faker->numberBetween(1, 100),
            'category' => 'equipment',
        ]);
    }

    public function withFacility(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'facility',
        ]);
    }

    public function withInfrastructure(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'infrastructure',
        ]);
    }

    public function withVendor(): static
    {
        return $this->state(fn (array $attributes) => [
            'vendor_id' => $this->faker->numberBetween(1, 100),
        ]);
    }

    public function withTaskTemplate(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_template_id' => $this->faker->numberBetween(1, 100),
        ]);
    }

    public function withChecklistTemplate(): static
    {
        return $this->state(fn (array $attributes) => [
            'checklist_template_id' => $this->faker->numberBetween(1, 100),
        ]);
    }

    public function shortDuration(): static
    {
        return $this->state(fn (array $attributes) => [
            'estimated_duration_hours' => $this->faker->randomFloat(2, 0.5, 4.0),
        ]);
    }

    public function mediumDuration(): static
    {
        return $this->state(fn (array $attributes) => [
            'estimated_duration_hours' => $this->faker->randomFloat(2, 4.0, 16.0),
        ]);
    }

    public function longDuration(): static
    {
        return $this->state(fn (array $attributes) => [
            'estimated_duration_hours' => $this->faker->randomFloat(2, 16.0, 40.0),
        ]);
    }

    public function lowCost(): static
    {
        return $this->state(fn (array $attributes) => [
            'estimated_cost' => $this->faker->randomFloat(2, 100.0, 1000.0),
        ]);
    }

    public function mediumCost(): static
    {
        return $this->state(fn (array $attributes) => [
            'estimated_cost' => $this->faker->randomFloat(2, 1000.0, 5000.0),
        ]);
    }

    public function highCost(): static
    {
        return $this->state(fn (array $attributes) => [
            'estimated_cost' => $this->faker->randomFloat(2, 5000.0, 10000.0),
        ]);
    }

    public function withTags(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $this->faker->words(3),
        ]);
    }

    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $this->faker->paragraph(),
        ]);
    }

    public function withSafetyRequirements(): static
    {
        return $this->state(fn (array $attributes) => [
            'safety_requirements' => [
                'safety_glasses', 'hard_hat', 'safety_shoes', 'gloves'
            ],
        ]);
    }

    public function withQualityStandards(): static
    {
        return $this->state(fn (array $attributes) => [
            'quality_standards' => [
                'ISO_9001', 'company_standards', 'industry_best_practices'
            ],
        ]);
    }

    public function withComplianceRequirements(): static
    {
        return $this->state(fn (array $attributes) => [
            'compliance_requirements' => [
                'OSHA_compliance', 'local_codes', 'safety_regulations'
            ],
        ]);
    }
}
