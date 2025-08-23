<?php

namespace Database\Factories;

use App\Models\AutomationRule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AutomationRuleFactory extends Factory
{
    protected $model = AutomationRule::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'rule_type' => $this->faker->randomElement(array_keys(AutomationRule::getRuleTypes())),
            'trigger_type' => $this->faker->randomElement(array_keys(AutomationRule::getTriggerTypes())),
            'trigger_conditions' => [
                'condition1' => $this->faker->word(),
                'condition2' => $this->faker->word(),
            ],
            'action_type' => $this->faker->randomElement(array_keys(AutomationRule::getActionTypes())),
            'action_parameters' => [
                'param1' => $this->faker->word(),
                'param2' => $this->faker->word(),
            ],
            'target_entity_id' => $this->faker->optional()->numberBetween(1, 100),
            'target_entity_type' => $this->faker->optional()->randomElement(['App\\Models\\User', 'App\\Models\\Company', 'App\\Models\\EnergyInstallation']),
            'is_active' => $this->faker->boolean(80),
            'priority' => $this->faker->numberBetween(1, 10),
            'execution_frequency' => $this->faker->randomElement(array_keys(AutomationRule::getExecutionFrequencies())),
            'last_executed_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'next_execution_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'execution_count' => $this->faker->numberBetween(0, 100),
            'max_executions' => $this->faker->optional()->numberBetween(1, 1000),
            'success_count' => $this->faker->numberBetween(0, 50),
            'failure_count' => $this->faker->numberBetween(0, 20),
            'last_error_message' => $this->faker->optional()->sentence(),
            'schedule_cron' => $this->faker->optional()->randomElement([
                '0 0 * * *',      // Daily at midnight
                '0 */6 * * *',    // Every 6 hours
                '0 9 * * 1-5',    // Weekdays at 9 AM
                '0 12 * * 0',     // Sundays at noon
                '*/15 * * * *',   // Every 15 minutes
            ]),
            'timezone' => $this->faker->randomElement(['UTC', 'Europe/Madrid', 'America/New_York', 'Asia/Tokyo']),
            'retry_on_failure' => $this->faker->boolean(60),
            'max_retries' => $this->faker->optional()->numberBetween(1, 5),
            'retry_delay_minutes' => $this->faker->optional()->numberBetween(1, 60),
            'notification_emails' => $this->faker->optional()->randomElements([
                $this->faker->email(),
                $this->faker->email(),
                $this->faker->email(),
            ], $this->faker->numberBetween(1, 3)),
            'webhook_url' => $this->faker->optional()->url(),
            'webhook_headers' => $this->faker->optional()->array([
                'Authorization' => 'Bearer ' . $this->faker->uuid(),
                'Content-Type' => 'application/json',
            ]),
            'tags' => $this->faker->optional()->words(3),
            'notes' => $this->faker->optional()->paragraph(),
            'created_by' => User::factory(),
            'approved_by' => $this->faker->optional()->randomElement([User::factory(), null]),
            'approved_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'rule_type' => 'scheduled',
            'trigger_type' => 'time',
        ]);
    }

    public function eventDriven(): static
    {
        return $this->state(fn (array $attributes) => [
            'rule_type' => 'event_driven',
            'trigger_type' => 'event',
        ]);
    }

    public function conditionBased(): static
    {
        return $this->state(fn (array $attributes) => [
            'rule_type' => 'condition_based',
            'trigger_type' => 'condition',
        ]);
    }

    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'rule_type' => 'manual',
            'trigger_type' => 'external',
        ]);
    }

    public function webhook(): static
    {
        return $this->state(fn (array $attributes) => [
            'rule_type' => 'webhook',
            'trigger_type' => 'external',
        ]);
    }

    public function timeTriggered(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger_type' => 'time',
        ]);
    }

    public function eventTriggered(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger_type' => 'event',
        ]);
    }

    public function conditionTriggered(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger_type' => 'condition',
        ]);
    }

    public function thresholdTriggered(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger_type' => 'threshold',
        ]);
    }

    public function patternTriggered(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger_type' => 'pattern',
        ]);
    }

    public function externalTriggered(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger_type' => 'external',
        ]);
    }

    public function emailAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => 'email',
        ]);
    }

    public function smsAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => 'sms',
        ]);
    }

    public function webhookAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => 'webhook',
        ]);
    }

    public function databaseAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => 'database',
        ]);
    }

    public function apiCallAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => 'api_call',
        ]);
    }

    public function systemCommandAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => 'system_command',
        ]);
    }

    public function notificationAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => 'notification',
        ]);
    }

    public function reportAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => 'report',
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->numberBetween(8, 10),
        ]);
    }

    public function mediumPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->numberBetween(4, 7),
        ]);
    }

    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->numberBetween(1, 3),
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 10,
        ]);
    }

    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 9,
        ]);
    }

    public function once(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_frequency' => 'once',
        ]);
    }

    public function hourly(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_frequency' => 'hourly',
        ]);
    }

    public function daily(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_frequency' => 'daily',
        ]);
    }

    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_frequency' => 'weekly',
        ]);
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_frequency' => 'monthly',
        ]);
    }

    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_frequency' => 'custom',
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

    public function withRetry(): static
    {
        return $this->state(fn (array $attributes) => [
            'retry_on_failure' => true,
            'max_retries' => $this->faker->numberBetween(1, 5),
            'retry_delay_minutes' => $this->faker->numberBetween(1, 60),
        ]);
    }

    public function withoutRetry(): static
    {
        return $this->state(fn (array $attributes) => [
            'retry_on_failure' => false,
            'max_retries' => null,
            'retry_delay_minutes' => null,
        ]);
    }

    public function withWebhook(): static
    {
        return $this->state(fn (array $attributes) => [
            'webhook_url' => $this->faker->url(),
            'webhook_headers' => [
                'Authorization' => 'Bearer ' . $this->faker->uuid(),
                'Content-Type' => 'application/json',
                'X-Custom-Header' => $this->faker->word(),
            ],
        ]);
    }

    public function withNotifications(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_emails' => [
                $this->faker->email(),
                $this->faker->email(),
                $this->faker->email(),
            ],
        ]);
    }

    public function withCronSchedule(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_cron' => $this->faker->randomElement([
                '0 0 * * *',      // Daily at midnight
                '0 */6 * * *',    // Every 6 hours
                '0 9 * * 1-5',    // Weekdays at 9 AM
                '0 12 * * 0',     // Sundays at noon
                '*/15 * * * *',   // Every 15 minutes
                '0 2 * * 1',      // Mondays at 2 AM
                '30 14 * * *',    // Daily at 2:30 PM
            ]),
            'timezone' => $this->faker->randomElement(['UTC', 'Europe/Madrid', 'America/New_York', 'Asia/Tokyo']),
        ]);
    }

    public function withTags(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $this->faker->words($this->faker->numberBetween(2, 5)),
        ]);
    }

    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $this->faker->paragraphs($this->faker->numberBetween(1, 3), true),
        ]);
    }

    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_count' => $this->faker->numberBetween(10, 100),
            'success_count' => $this->faker->numberBetween(8, 100),
            'failure_count' => $this->faker->numberBetween(0, 5),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_count' => $this->faker->numberBetween(10, 100),
            'success_count' => $this->faker->numberBetween(0, 5),
            'failure_count' => $this->faker->numberBetween(8, 100),
            'last_error_message' => $this->faker->sentence(),
        ]);
    }

    public function neverExecuted(): static
    {
        return $this->state(fn (array $attributes) => [
            'execution_count' => 0,
            'success_count' => 0,
            'failure_count' => 0,
            'last_executed_at' => null,
            'next_execution_at' => now()->addDays($this->faker->numberBetween(1, 30)),
        ]);
    }

    public function readyToExecute(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'approved_at' => now(),
            'next_execution_at' => now()->subHour(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'next_execution_at' => now()->subDays($this->faker->numberBetween(1, 7)),
        ]);
    }

    public function withTargetEntity(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_entity_id' => $this->faker->numberBetween(1, 100),
            'target_entity_type' => $this->faker->randomElement([
                'App\\Models\\User',
                'App\\Models\\Company',
                'App\\Models\\EnergyInstallation',
                'App\\Models\\EnergyMeter',
                'App\\Models\\EnergyReading',
            ]),
        ]);
    }

    public function withComplexConditions(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger_conditions' => [
                'temperature' => [
                    'operator' => '>',
                    'value' => 25,
                    'unit' => 'celsius'
                ],
                'humidity' => [
                    'operator' => '<',
                    'value' => 60,
                    'unit' => 'percent'
                ],
                'time_of_day' => [
                    'operator' => 'between',
                    'start' => '09:00',
                    'end' => '17:00'
                ]
            ],
        ]);
    }

    public function withComplexParameters(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_parameters' => [
                'recipients' => [
                    'emails' => [$this->faker->email(), $this->faker->email()],
                    'roles' => ['admin', 'manager']
                ],
                'template' => 'energy_alert',
                'subject' => 'Energy Consumption Alert',
                'priority' => 'high',
                'attachments' => [
                    'include_charts' => true,
                    'include_tables' => true,
                    'format' => 'pdf'
                ]
            ],
        ]);
    }
}
