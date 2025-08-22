<?php

namespace Database\Factories;

use App\Models\MaintenanceTask;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaintenanceTask>
 */
class MaintenanceTaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MaintenanceTask::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $taskTypes = ['inspection', 'repair', 'replacement', 'preventive', 'corrective', 'emergency', 'upgrade', 'calibration', 'cleaning', 'testing', 'other'];
        $statuses = ['pending', 'in_progress', 'paused', 'completed', 'cancelled', 'on_hold'];
        $priorities = ['low', 'medium', 'high', 'urgent', 'critical'];

        return [
            'title' => $this->faker->unique()->sentence(4, false),
            'description' => $this->faker->paragraph(3),
            'task_type' => $this->faker->randomElement($taskTypes),
            'status' => $this->faker->randomElement($statuses),
            'priority' => $this->faker->randomElement($priorities),
            'assigned_to' => User::factory(),
            'assigned_by' => User::factory(),
            'equipment_id' => null, // Will be set by specific states if needed
            'location_id' => null, // Will be set by specific states if needed
            'schedule_id' => null, // Will be set by specific states if needed
            'organization_id' => Organization::factory(),
            'due_date' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
            'estimated_hours' => $this->faker->randomFloat(1, 0.5, 24.0),
            'estimated_cost' => $this->faker->optional(0.8)->randomFloat(2, 10, 5000),
            'actual_start_time' => $this->faker->optional(0.6)->dateTimeBetween('-7 days', 'now'),
            'actual_end_time' => $this->faker->optional(0.4)->dateTimeBetween('now', '+7 days'),
            'progress_percentage' => $this->faker->optional(0.7)->numberBetween(0, 100),
            'completion_notes' => $this->faker->optional(0.5)->paragraph(2),
            'actual_hours' => $this->faker->optional(0.4)->randomFloat(1, 0.5, 48.0),
            'actual_cost' => $this->faker->optional(0.4)->randomFloat(2, 10, 10000),
            'quality_score' => $this->faker->optional(0.3)->numberBetween(0, 100),
            'materials_used' => $this->faker->optional(0.6)->sentence(3),
            'tools_required' => $this->faker->optional(0.7)->sentence(2),
            'safety_requirements' => $this->faker->optional(0.8)->sentence(2),
            'technical_requirements' => $this->faker->optional(0.6)->paragraph(1),
            'documentation_required' => $this->faker->boolean(80),
            'photos_required' => $this->faker->boolean(70),
            'signature_required' => $this->faker->boolean(60),
            'approval_required' => $this->faker->boolean(50),
            'is_recurring' => $this->faker->boolean(30),
            'recurrence_pattern' => $this->faker->optional(0.3)->randomElement(['daily', 'weekly', 'monthly', 'quarterly', 'annually']),
            'next_occurrence' => $this->faker->optional(0.3)->dateTimeBetween('+1 month', '+12 months'),
            'tags' => $this->faker->optional(0.7)->words($this->faker->numberBetween(1, 5)),
            'notes' => $this->faker->optional(0.5)->paragraph(2),
            'attachments' => $this->faker->optional(0.3)->words($this->faker->numberBetween(1, 3)),
        ];
    }

    /**
     * Indicate that the task is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'actual_start_time' => null,
            'actual_end_time' => null,
            'progress_percentage' => 0,
        ]);
    }

    /**
     * Indicate that the task is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'actual_start_time' => now(),
            'actual_end_time' => null,
            'progress_percentage' => $this->faker->numberBetween(10, 90),
        ]);
    }

    /**
     * Indicate that the task is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'actual_start_time' => now()->subDays($this->faker->numberBetween(1, 7)),
            'actual_end_time' => now(),
            'progress_percentage' => 100,
            'completion_notes' => $this->faker->sentence(),
            'actual_hours' => $this->faker->randomFloat(1, 0.5, 48.0),
            'actual_cost' => $this->faker->randomFloat(2, 10, 10000),
            'quality_score' => $this->faker->numberBetween(60, 100),
        ]);
    }

    /**
     * Indicate that the task is paused.
     */
    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paused',
            'actual_start_time' => now()->subDays($this->faker->numberBetween(1, 3)),
            'paused_at' => now(),
            'progress_percentage' => $this->faker->numberBetween(10, 80),
        ]);
    }

    /**
     * Indicate that the task is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'progress_percentage' => 0,
        ]);
    }

    /**
     * Indicate that the task is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'paused']),
        ]);
    }

    /**
     * Indicate that the task is due today.
     */
    public function dueToday(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => today(),
        ]);
    }

    /**
     * Indicate that the task is due this week.
     */
    public function dueThisWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween(now()->startOfWeek(), now()->endOfWeek()),
        ]);
    }

    /**
     * Indicate that the task has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Indicate that the task has urgent priority.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    /**
     * Indicate that the task has critical priority.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'critical',
        ]);
    }

    /**
     * Indicate that the task is an inspection.
     */
    public function inspection(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_type' => 'inspection',
        ]);
    }

    /**
     * Indicate that the task is a repair.
     */
    public function repair(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_type' => 'repair',
        ]);
    }

    /**
     * Indicate that the task is preventive maintenance.
     */
    public function preventive(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_type' => 'preventive',
        ]);
    }

    /**
     * Indicate that the task is corrective maintenance.
     */
    public function corrective(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_type' => 'corrective',
        ]);
    }

    /**
     * Indicate that the task is emergency maintenance.
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_type' => 'emergency',
            'priority' => 'critical',
        ]);
    }

    /**
     * Indicate that the task requires documentation.
     */
    public function requiresDocumentation(): static
    {
        return $this->state(fn (array $attributes) => [
            'documentation_required' => true,
        ]);
    }

    /**
     * Indicate that the task requires photos.
     */
    public function requiresPhotos(): static
    {
        return $this->state(fn (array $attributes) => [
            'photos_required' => true,
        ]);
    }

    /**
     * Indicate that the task requires signature.
     */
    public function requiresSignature(): static
    {
        return $this->state(fn (array $attributes) => [
            'signature_required' => true,
        ]);
    }

    /**
     * Indicate that the task requires approval.
     */
    public function requiresApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_required' => true,
        ]);
    }

    /**
     * Indicate that the task is recurring.
     */
    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'recurrence_pattern' => $this->faker->randomElement(['daily', 'weekly', 'monthly', 'quarterly', 'annually']),
            'next_occurrence' => $this->faker->dateTimeBetween('+1 month', '+12 months'),
        ]);
    }

    /**
     * Indicate that the task has high quality score.
     */
    public function highQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'quality_score' => $this->faker->numberBetween(90, 100),
        ]);
    }

    /**
     * Indicate that the task has low quality score.
     */
    public function lowQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'quality_score' => $this->faker->numberBetween(0, 70),
        ]);
    }

    /**
     * Indicate that the task is expensive.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'estimated_cost' => $this->faker->randomFloat(2, 1000, 50000),
            'actual_cost' => $this->faker->randomFloat(2, 1000, 50000),
        ]);
    }

    /**
     * Indicate that the task is time-consuming.
     */
    public function timeConsuming(): static
    {
        return $this->state(fn (array $attributes) => [
            'estimated_hours' => $this->faker->randomFloat(1, 8, 40),
            'actual_hours' => $this->faker->randomFloat(1, 8, 40),
        ]);
    }
}
