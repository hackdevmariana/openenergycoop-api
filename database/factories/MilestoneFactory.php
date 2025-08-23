<?php

namespace Database\Factories;

use App\Models\Milestone;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MilestoneFactory extends Factory
{
    protected $model = Milestone::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(2),
            'milestone_type' => $this->faker->randomElement(Milestone::getMilestoneTypes()),
            'status' => $this->faker->randomElement(Milestone::getStatuses()),
            'priority' => $this->faker->randomElement(Milestone::getPriorities()),
            'target_date' => $this->faker->dateTimeBetween('now', '+6 months'),
            'start_date' => $this->faker->optional()->dateTimeBetween('-2 months', 'now'),
            'completion_date' => $this->faker->optional()->dateTimeBetween('now', '+6 months'),
            'progress_percentage' => $this->faker->randomFloat(2, 0, 100),
            'budget' => $this->faker->optional()->randomFloat(2, 1000, 100000),
            'actual_cost' => $this->faker->optional()->randomFloat(2, 500, 80000),
            'parent_milestone_id' => null,
            'assigned_to' => User::factory(),
            'tags' => $this->faker->optional()->randomElements(['urgent', 'phase1', 'development', 'testing', 'deployment', 'documentation'], $this->faker->numberBetween(1, 3)),
            'dependencies' => $this->faker->optional()->randomElements([1, 2, 3, 4, 5], $this->faker->numberBetween(1, 2)),
            'risk_level' => $this->faker->optional()->randomElement(['low', 'medium', 'high', 'critical']),
            'notes' => $this->faker->optional()->paragraph(1),
            'created_by' => User::factory(),
            'version' => '1.0',
        ];
    }

    public function project(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'milestone_type' => Milestone::MILESTONE_TYPE_PROJECT,
                'title' => 'Project: ' . $this->faker->words(2, true),
            ];
        });
    }

    public function phase(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'milestone_type' => Milestone::MILESTONE_TYPE_PHASE,
                'title' => 'Phase: ' . $this->faker->words(2, true),
            ];
        });
    }

    public function deliverable(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'milestone_type' => Milestone::MILESTONE_TYPE_DELIVERABLE,
                'title' => 'Deliverable: ' . $this->faker->words(2, true),
            ];
        });
    }

    public function review(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'milestone_type' => Milestone::MILESTONE_TYPE_REVIEW,
                'title' => 'Review: ' . $this->faker->words(2, true),
            ];
        });
    }

    public function notStarted(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Milestone::STATUS_NOT_STARTED,
                'progress_percentage' => 0,
                'start_date' => null,
                'completion_date' => null,
            ];
        });
    }

    public function inProgress(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Milestone::STATUS_IN_PROGRESS,
                'progress_percentage' => $this->faker->randomFloat(2, 1, 99),
                'start_date' => $this->faker->dateTimeBetween('-2 months', 'now'),
                'completion_date' => null,
            ];
        });
    }

    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Milestone::STATUS_COMPLETED,
                'progress_percentage' => 100,
                'start_date' => $this->faker->dateTimeBetween('-3 months', '-1 month'),
                'completion_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    public function onHold(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Milestone::STATUS_ON_HOLD,
                'progress_percentage' => $this->faker->randomFloat(2, 0, 80),
            ];
        });
    }

    public function cancelled(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Milestone::STATUS_CANCELLED,
                'progress_percentage' => $this->faker->randomFloat(2, 0, 50),
            ];
        });
    }

    public function lowPriority(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => Milestone::PRIORITY_LOW,
            ];
        });
    }

    public function mediumPriority(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => Milestone::PRIORITY_MEDIUM,
            ];
        });
    }

    public function highPriority(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => Milestone::PRIORITY_HIGH,
            ];
        });
    }

    public function criticalPriority(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => Milestone::PRIORITY_CRITICAL,
            ];
        });
    }

    public function overdue(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'target_date' => $this->faker->dateTimeBetween('-2 months', '-1 day'),
                'status' => Milestone::STATUS_IN_PROGRESS,
                'progress_percentage' => $this->faker->randomFloat(2, 1, 99),
            ];
        });
    }

    public function dueSoon(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'target_date' => $this->faker->dateTimeBetween('now', '+7 days'),
                'status' => Milestone::STATUS_NOT_STARTED,
                'progress_percentage' => 0,
            ];
        });
    }

    public function withBudget(): static
    {
        return $this->state(function (array $attributes) {
            $budget = $this->faker->randomFloat(2, 5000, 50000);
            return [
                'budget' => $budget,
                'actual_cost' => $this->faker->randomFloat(2, 1000, $budget * 0.8),
            ];
        });
    }

    public function overBudget(): static
    {
        return $this->state(function (array $attributes) {
            $budget = $this->faker->randomFloat(2, 5000, 50000);
            return [
                'budget' => $budget,
                'actual_cost' => $this->faker->randomFloat(2, $budget * 1.1, $budget * 1.5),
            ];
        });
    }

    public function withDependencies(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'dependencies' => $this->faker->randomElements([1, 2, 3, 4, 5], $this->faker->numberBetween(1, 3)),
            ];
        });
    }

    public function withTags(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'tags' => $this->faker->randomElements([
                    'urgent', 'phase1', 'development', 'testing', 'deployment',
                    'documentation', 'critical', 'review', 'approval', 'milestone'
                ], $this->faker->numberBetween(2, 5)),
            ];
        });
    }

    public function highRisk(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'risk_level' => 'high',
                'priority' => $this->faker->randomElement([Milestone::PRIORITY_HIGH, Milestone::PRIORITY_CRITICAL]),
            ];
        });
    }

    public function lowRisk(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'risk_level' => 'low',
                'priority' => $this->faker->randomElement([Milestone::PRIORITY_LOW, Milestone::PRIORITY_MEDIUM]),
            ];
        });
    }

    public function withNotes(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'notes' => $this->faker->paragraph(2),
            ];
        });
    }

    public function withVersion(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'version' => $this->faker->randomElement(['1.0', '1.1', '2.0', '2.1', '3.0']),
            ];
        });
    }

    public function root(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'parent_milestone_id' => null,
            ];
        });
    }

    public function child(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'parent_milestone_id' => Milestone::factory(),
            ];
        });
    }

    public function assigned(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'assigned_to' => User::factory(),
            ];
        });
    }

    public function unassigned(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'assigned_to' => null,
            ];
        });
    }
}
