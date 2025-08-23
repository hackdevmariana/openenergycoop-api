<?php

namespace Database\Factories;

use App\Models\TaskTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskTemplateFactory extends Factory
{
    protected $model = TaskTemplate::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->sentence(3),
            'description' => $this->faker->paragraph(3),
            'template_type' => $this->faker->randomElement([
                'maintenance', 'inspection', 'repair', 'replacement',
                'calibration', 'cleaning', 'lubrication', 'testing',
                'upgrade', 'installation'
            ]),
            'category' => $this->faker->randomElement([
                'Electrical', 'Mechanical', 'HVAC', 'Plumbing',
                'Structural', 'Safety', 'Environmental', 'IT'
            ]),
            'subcategory' => $this->faker->randomElement([
                'Preventive', 'Corrective', 'Emergency', 'Predictive',
                'Condition-based', 'Time-based', 'Usage-based'
            ]),
            'estimated_duration_hours' => $this->faker->randomFloat(1, 0.5, 24.0),
            'estimated_cost' => $this->faker->randomFloat(2, 50.00, 5000.00),
            'required_skills' => $this->faker->randomElements([
                'electrical', 'mechanical', 'plumbing', 'carpentry',
                'welding', 'painting', 'cleaning', 'testing',
                'calibration', 'documentation', 'safety', 'quality'
            ], $this->faker->numberBetween(2, 5)),
            'required_tools' => $this->faker->randomElements([
                'multimeter', 'screwdriver', 'wrench', 'pliers',
                'hammer', 'drill', 'saw', 'level', 'tape_measure',
                'safety_glasses', 'gloves', 'ladder'
            ], $this->faker->numberBetween(2, 6)),
            'required_parts' => $this->faker->randomElements([
                'fuses', 'wires', 'switches', 'motors', 'belts',
                'filters', 'bearings', 'valves', 'pipes', 'fasteners'
            ], $this->faker->numberBetween(1, 4)),
            'safety_requirements' => $this->faker->randomElements([
                'PPE required', 'Lockout/Tagout', 'Confined space permit',
                'Hot work permit', 'Fall protection', 'Respiratory protection',
                'Hearing protection', 'Eye protection'
            ], $this->faker->numberBetween(2, 5)),
            'technical_requirements' => $this->faker->randomElements([
                'Voltage testing', 'Insulation check', 'Pressure testing',
                'Temperature monitoring', 'Vibration analysis', 'Flow measurement',
                'Level measurement', 'Quality inspection'
            ], $this->faker->numberBetween(2, 5)),
            'quality_standards' => $this->faker->randomElements([
                'Industry standards', 'Safety compliance', 'ISO 9001',
                'OSHA requirements', 'Manufacturer specifications',
                'Company procedures', 'Regulatory compliance'
            ], $this->faker->numberBetween(2, 5)),
            'checklist_items' => $this->faker->randomElements([
                'Check voltage', 'Test insulation', 'Verify pressure',
                'Monitor temperature', 'Inspect connections', 'Test functionality',
                'Verify settings', 'Check documentation'
            ], $this->faker->numberBetween(3, 8)),
            'work_instructions' => $this->faker->randomElements([
                'Step 1: Safety preparation', 'Step 2: Equipment isolation',
                'Step 3: Visual inspection', 'Step 4: Testing procedures',
                'Step 5: Documentation', 'Step 6: Cleanup',
                'Step 7: Verification', 'Step 8: Handover'
            ], $this->faker->numberBetween(4, 8)),
            'is_active' => $this->faker->boolean(80),
            'is_standard' => $this->faker->boolean(30),
            'version' => $this->faker->randomElement(['1.0', '1.1', '1.2', '2.0', '2.1']),
            'tags' => $this->faker->randomElements([
                'maintenance', 'electrical', 'mechanical', 'safety',
                'preventive', 'corrective', 'emergency', 'routine',
                'critical', 'non-critical', 'scheduled', 'unscheduled'
            ], $this->faker->numberBetween(2, 5)),
            'notes' => $this->faker->optional(0.7)->paragraph(2),
            'department' => $this->faker->randomElement([
                'Engineering', 'Operations', 'Maintenance', 'Safety',
                'Quality', 'Facilities', 'Production', 'Utilities'
            ]),
            'priority' => $this->faker->randomElement([
                'low', 'medium', 'high', 'urgent', 'critical'
            ]),
            'risk_level' => $this->faker->randomElement([
                'low', 'medium', 'high', 'extreme'
            ]),
            'compliance_requirements' => $this->faker->randomElements([
                'OSHA', 'NEC', 'NFPA', 'EPA', 'ISO', 'ANSI',
                'ASTM', 'ASME', 'IEEE', 'UL'
            ], $this->faker->numberBetween(1, 4)),
            'documentation_required' => $this->faker->randomElements([
                'work_order', 'safety_checklist', 'quality_report',
                'completion_certificate', 'inspection_report',
                'test_results', 'calibration_record'
            ], $this->faker->numberBetween(2, 5)),
            'training_required' => $this->faker->boolean(60),
            'certification_required' => $this->faker->boolean(40),
            'environmental_considerations' => $this->faker->randomElements([
                'waste_disposal', 'emissions', 'noise', 'vibration',
                'chemical_handling', 'spill_prevention', 'recycling',
                'energy_efficiency'
            ], $this->faker->numberBetween(1, 4)),
            'budget_code' => $this->faker->optional(0.8)->regexify('BUD[0-9]{3}'),
            'cost_center' => $this->faker->optional(0.8)->regexify('CC[0-9]{3}'),
            'project_code' => $this->faker->optional(0.6)->regexify('PRJ[0-9]{3}'),
            'created_by' => User::factory(),
            'approved_by' => $this->faker->optional(0.7)->randomElement([User::factory(), null]),
            'approved_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the template is active.
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    /**
     * Indicate that the template is inactive.
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    /**
     * Indicate that the template is standard.
     */
    public function standard()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_standard' => true,
            ];
        });
    }

    /**
     * Indicate that the template is custom.
     */
    public function custom()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_standard' => false,
            ];
        });
    }

    /**
     * Indicate that the template is approved.
     */
    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'approved_at' => now(),
                'approved_by' => User::factory(),
            ];
        });
    }

    /**
     * Indicate that the template is pending approval.
     */
    public function pendingApproval()
    {
        return $this->state(function (array $attributes) {
            return [
                'approved_at' => null,
                'approved_by' => null,
            ];
        });
    }

    /**
     * Indicate that the template is high priority.
     */
    public function highPriority()
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'high',
            ];
        });
    }

    /**
     * Indicate that the template is urgent priority.
     */
    public function urgent()
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'urgent',
            ];
        });
    }

    /**
     * Indicate that the template is critical priority.
     */
    public function critical()
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'critical',
            ];
        });
    }

    /**
     * Indicate that the template is high risk.
     */
    public function highRisk()
    {
        return $this->state(function (array $attributes) {
            return [
                'risk_level' => 'high',
            ];
        });
    }

    /**
     * Indicate that the template is extreme risk.
     */
    public function extremeRisk()
    {
        return $this->state(function (array $attributes) {
            return [
                'risk_level' => 'extreme',
            ];
        });
    }

    /**
     * Indicate that the template requires training.
     */
    public function trainingRequired()
    {
        return $this->state(function (array $attributes) {
            return [
                'training_required' => true,
            ];
        });
    }

    /**
     * Indicate that the template requires certification.
     */
    public function certificationRequired()
    {
        return $this->state(function (array $attributes) {
            return [
                'certification_required' => true,
            ];
        });
    }

    /**
     * Indicate that the template is for maintenance.
     */
    public function maintenance()
    {
        return $this->state(function (array $attributes) {
            return [
                'template_type' => 'maintenance',
            ];
        });
    }

    /**
     * Indicate that the template is for inspection.
     */
    public function inspection()
    {
        return $this->state(function (array $attributes) {
            return [
                'template_type' => 'inspection',
            ];
        });
    }

    /**
     * Indicate that the template is for repair.
     */
    public function repair()
    {
        return $this->state(function (array $attributes) {
            return [
                'template_type' => 'repair',
            ];
        });
    }

    /**
     * Indicate that the template is for replacement.
     */
    public function replacement()
    {
        return $this->state(function (array $attributes) {
            return [
                'template_type' => 'replacement',
            ];
        });
    }

    /**
     * Indicate that the template is for calibration.
     */
    public function calibration()
    {
        return $this->state(function (array $attributes) {
            return [
                'template_type' => 'calibration',
            ];
        });
    }

    /**
     * Indicate that the template is for cleaning.
     */
    public function cleaning()
    {
        return $this->state(function (array $attributes) {
            return [
                'template_type' => 'cleaning',
            ];
        });
    }

    /**
     * Indicate that the template is for lubrication.
     */
    public function lubrication()
    {
        return $this->state(function (array $attributes) {
            return [
                'template_type' => 'lubrication',
            ];
        });
    }

    /**
     * Indicate that the template is for testing.
     */
    public function testing()
    {
        return $this->state(function (array $attributes) {
            return [
                'template_type' => 'testing',
            ];
        });
    }

    /**
     * Indicate that the template is for upgrade.
     */
    public function upgrade()
    {
        return $this->state(function (array $attributes) {
            return [
                'template_type' => 'upgrade',
            ];
        });
    }

    /**
     * Indicate that the template is for installation.
     */
    public function installation()
    {
        return $this->state(function (array $attributes) {
            return [
                'template_type' => 'installation',
            ];
        });
    }

    /**
     * Indicate that the template is for electrical work.
     */
    public function electrical()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'Electrical',
            ];
        });
    }

    /**
     * Indicate that the template is for mechanical work.
     */
    public function mechanical()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'Mechanical',
            ];
        });
    }

    /**
     * Indicate that the template is for HVAC work.
     */
    public function hvac()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'HVAC',
            ];
        });
    }

    /**
     * Indicate that the template is for plumbing work.
     */
    public function plumbing()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'Plumbing',
            ];
        });
    }

    /**
     * Indicate that the template is for structural work.
     */
    public function structural()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'Structural',
            ];
        });
    }

    /**
     * Indicate that the template is for safety work.
     */
    public function safety()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'Safety',
            ];
        });
    }

    /**
     * Indicate that the template is for environmental work.
     */
    public function environmental()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'Environmental',
            ];
        });
    }

    /**
     * Indicate that the template is for IT work.
     */
    public function it()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => 'IT',
            ];
        });
    }

    /**
     * Indicate that the template is for engineering department.
     */
    public function engineering()
    {
        return $this->state(function (array $attributes) {
            return [
                'department' => 'Engineering',
            ];
        });
    }

    /**
     * Indicate that the template is for operations department.
     */
    public function operations()
    {
        return $this->state(function (array $attributes) {
            return [
                'department' => 'Operations',
            ];
        });
    }

    /**
     * Indicate that the template is for maintenance department.
     */
    public function maintenanceDept()
    {
        return $this->state(function (array $attributes) {
            return [
                'department' => 'Maintenance',
            ];
        });
    }

    /**
     * Indicate that the template is for safety department.
     */
    public function safetyDept()
    {
        return $this->state(function (array $attributes) {
            return [
                'department' => 'Safety',
            ];
        });
    }

    /**
     * Indicate that the template is for quality department.
     */
    public function quality()
    {
        return $this->state(function (array $attributes) {
            return [
                'department' => 'Quality',
            ];
        });
    }

    /**
     * Indicate that the template is for facilities department.
     */
    public function facilities()
    {
        return $this->state(function (array $attributes) {
            return [
                'department' => 'Facilities',
            ];
        });
    }

    /**
     * Indicate that the template is for production department.
     */
    public function production()
    {
        return $this->state(function (array $attributes) {
            return [
                'department' => 'Production',
            ];
        });
    }

    /**
     * Indicate that the template is for utilities department.
     */
    public function utilities()
    {
        return $this->state(function (array $attributes) {
            return [
                'department' => 'Utilities',
            ];
        });
    }

    /**
     * Indicate that the template has low estimated duration.
     */
    public function shortDuration()
    {
        return $this->state(function (array $attributes) {
            return [
                'estimated_duration_hours' => $this->faker->randomFloat(1, 0.5, 2.0),
            ];
        });
    }

    /**
     * Indicate that the template has medium estimated duration.
     */
    public function mediumDuration()
    {
        return $this->state(function (array $attributes) {
            return [
                'estimated_duration_hours' => $this->faker->randomFloat(1, 2.0, 8.0),
            ];
        });
    }

    /**
     * Indicate that the template has high estimated duration.
     */
    public function longDuration()
    {
        return $this->state(function (array $attributes) {
            return [
                'estimated_duration_hours' => $this->faker->randomFloat(1, 8.0, 24.0),
            ];
        });
    }

    /**
     * Indicate that the template has low estimated cost.
     */
    public function lowCost()
    {
        return $this->state(function (array $attributes) {
            return [
                'estimated_cost' => $this->faker->randomFloat(2, 50.00, 500.00),
            ];
        });
    }

    /**
     * Indicate that the template has medium estimated cost.
     */
    public function mediumCost()
    {
        return $this->state(function (array $attributes) {
            return [
                'estimated_cost' => $this->faker->randomFloat(2, 500.00, 2000.00),
            ];
        });
    }

    /**
     * Indicate that the template has high estimated cost.
     */
    public function highCost()
    {
        return $this->state(function (array $attributes) {
            return [
                'estimated_cost' => $this->faker->randomFloat(2, 2000.00, 5000.00),
            ];
        });
    }

    /**
     * Indicate that the template is newly created.
     */
    public function newlyCreated()
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the template is established.
     */
    public function established()
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('-2 years', '-6 months'),
                'updated_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            ];
        });
    }

    /**
     * Indicate that the template is legacy.
     */
    public function legacy()
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('-5 years', '-2 years'),
                'updated_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            ];
        });
    }

    /**
     * Indicate that the template has comprehensive requirements.
     */
    public function comprehensive()
    {
        return $this->state(function (array $attributes) {
            return [
                'required_skills' => $this->faker->randomElements([
                    'electrical', 'mechanical', 'plumbing', 'carpentry',
                    'welding', 'painting', 'cleaning', 'testing',
                    'calibration', 'documentation', 'safety', 'quality'
                ], 5),
                'required_tools' => $this->faker->randomElements([
                    'multimeter', 'screwdriver', 'wrench', 'pliers',
                    'hammer', 'drill', 'saw', 'level', 'tape_measure',
                    'safety_glasses', 'gloves', 'ladder'
                ], 6),
                'required_parts' => $this->faker->randomElements([
                    'fuses', 'wires', 'switches', 'motors', 'belts',
                    'filters', 'bearings', 'valves', 'pipes', 'fasteners'
                ], 4),
                'safety_requirements' => $this->faker->randomElements([
                    'PPE required', 'Lockout/Tagout', 'Confined space permit',
                    'Hot work permit', 'Fall protection', 'Respiratory protection',
                    'Hearing protection', 'Eye protection'
                ], 5),
                'technical_requirements' => $this->faker->randomElements([
                    'Voltage testing', 'Insulation check', 'Pressure testing',
                    'Temperature monitoring', 'Vibration analysis', 'Flow measurement',
                    'Level measurement', 'Quality inspection'
                ], 5),
                'quality_standards' => $this->faker->randomElements([
                    'Industry standards', 'Safety compliance', 'ISO 9001',
                    'OSHA requirements', 'Manufacturer specifications',
                    'Company procedures', 'Regulatory compliance'
                ], 5),
                'checklist_items' => $this->faker->randomElements([
                    'Check voltage', 'Test insulation', 'Verify pressure',
                    'Monitor temperature', 'Inspect connections', 'Test functionality',
                    'Verify settings', 'Check documentation'
                ], 8),
                'work_instructions' => $this->faker->randomElements([
                    'Step 1: Safety preparation', 'Step 2: Equipment isolation',
                    'Step 3: Visual inspection', 'Step 4: Testing procedures',
                    'Step 5: Documentation', 'Step 6: Cleanup',
                    'Step 7: Verification', 'Step 8: Handover'
                ], 8),
            ];
        });
    }

    /**
     * Indicate that the template has minimal requirements.
     */
    public function minimal()
    {
        return $this->state(function (array $attributes) {
            return [
                'required_skills' => $this->faker->randomElements([
                    'basic_maintenance', 'cleaning', 'inspection'
                ], 2),
                'required_tools' => $this->faker->randomElements([
                    'basic_tools', 'cleaning_supplies', 'safety_equipment'
                ], 2),
                'required_parts' => $this->faker->randomElements([
                    'basic_parts', 'consumables'
                ], 1),
                'safety_requirements' => $this->faker->randomElements([
                    'PPE required', 'Basic safety awareness'
                ], 2),
                'technical_requirements' => $this->faker->randomElements([
                    'Visual inspection', 'Basic testing'
                ], 2),
                'quality_standards' => $this->faker->randomElements([
                    'Basic standards', 'Company procedures'
                ], 2),
                'checklist_items' => $this->faker->randomElements([
                    'Basic inspection', 'Clean area', 'Verify operation'
                ], 3),
                'work_instructions' => $this->faker->randomElements([
                    'Step 1: Safety check', 'Step 2: Basic task',
                    'Step 3: Verification'
                ], 3),
            ];
        });
    }

    /**
     * Indicate that the template is for critical equipment.
     */
    public function criticalEquipment()
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'critical',
                'risk_level' => 'extreme',
                'training_required' => true,
                'certification_required' => true,
                'compliance_requirements' => $this->faker->randomElements([
                    'OSHA', 'NEC', 'NFPA', 'EPA', 'ISO', 'ANSI',
                    'ASTM', 'ASME', 'IEEE', 'UL'
                ], 6),
                'documentation_required' => $this->faker->randomElements([
                    'work_order', 'safety_checklist', 'quality_report',
                    'completion_certificate', 'inspection_report',
                    'test_results', 'calibration_record'
                ], 6),
                'environmental_considerations' => $this->faker->randomElements([
                    'waste_disposal', 'emissions', 'noise', 'vibration',
                    'chemical_handling', 'spill_prevention', 'recycling',
                    'energy_efficiency'
                ], 6),
            ];
        });
    }

    /**
     * Indicate that the template is for routine maintenance.
     */
    public function routine()
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'low',
                'risk_level' => 'low',
                'training_required' => false,
                'certification_required' => false,
                'template_type' => 'maintenance',
                'subcategory' => 'Preventive',
            ];
        });
    }

    /**
     * Indicate that the template is for emergency repairs.
     */
    public function emergency()
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'urgent',
                'risk_level' => 'high',
                'template_type' => 'repair',
                'subcategory' => 'Emergency',
                'training_required' => true,
                'certification_required' => true,
            ];
        });
    }
}
