<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $messageType = $this->faker->randomElement(['contact', 'support', 'complaint', 'suggestion']);
        $priority = $this->faker->randomElement(['low', 'normal', 'high', 'urgent']);
        $status = $this->faker->randomElement(['pending', 'read', 'replied', 'archived']);

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->optional(0.7)->phoneNumber(),
            'subject' => $this->generateSubjectByType($messageType),
            'message' => $this->generateMessageByType($messageType),
            'status' => $status,
            'priority' => $priority,
            'message_type' => $messageType,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'read_at' => $status !== 'pending' ? $this->faker->dateTimeBetween('-7 days', 'now') : null,
            'replied_at' => $status === 'replied' ? $this->faker->dateTimeBetween('-5 days', 'now') : null,
            'replied_by_user_id' => $status === 'replied' ? User::factory() : null,
            'internal_notes' => $this->faker->optional(0.3)->paragraph(),
            'assigned_to_user_id' => $this->faker->optional(0.4)->randomElement([User::factory(), null]),
            'organization_id' => null, // Can be overridden
        ];
    }

    /**
     * Indicate that the message is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'read_at' => null,
            'replied_at' => null,
            'replied_by_user_id' => null,
        ]);
    }

    /**
     * Indicate that the message has been read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'read',
            'read_at' => $this->faker->dateTimeBetween('-3 days', 'now'),
            'replied_at' => null,
            'replied_by_user_id' => null,
        ]);
    }

    /**
     * Indicate that the message has been replied to.
     */
    public function replied(): static
    {
        $readAt = $this->faker->dateTimeBetween('-7 days', '-1 day');
        $repliedAt = $this->faker->dateTimeBetween($readAt, 'now');

        return $this->state(fn (array $attributes) => [
            'status' => 'replied',
            'read_at' => $readAt,
            'replied_at' => $repliedAt,
            'replied_by_user_id' => User::factory(),
        ]);
    }

    /**
     * Indicate that the message is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
            'read_at' => $this->faker->dateTimeBetween('-10 days', '-5 days'),
        ]);
    }

    /**
     * Indicate that the message is marked as spam.
     */
    public function spam(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'spam',
            'read_at' => $this->faker->dateTimeBetween('-3 days', 'now'),
        ]);
    }

    /**
     * Create a contact message.
     */
    public function contact(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'contact',
            'subject' => $this->faker->randomElement([
                'Consulta sobre membresía',
                'Información sobre servicios',
                'Solicitud de presupuesto',
                'Pregunta general',
                'Interés en colaboración'
            ]),
            'priority' => 'normal',
        ]);
    }

    /**
     * Create a support message.
     */
    public function support(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'support',
            'subject' => $this->faker->randomElement([
                'Problema técnico con la plataforma',
                'No puedo acceder a mi cuenta',
                'Error en el sistema',
                'Ayuda con configuración',
                'Solicitud de soporte técnico'
            ]),
            'priority' => $this->faker->randomElement(['normal', 'high']),
        ]);
    }

    /**
     * Create a complaint message.
     */
    public function complaint(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'complaint',
            'subject' => $this->faker->randomElement([
                'Queja sobre el servicio',
                'Problema con facturación',
                'Disconformidad con atención',
                'Reclamo por demora',
                'Queja sobre calidad'
            ]),
            'priority' => $this->faker->randomElement(['high', 'urgent']),
        ]);
    }

    /**
     * Create a suggestion message.
     */
    public function suggestion(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'suggestion',
            'subject' => $this->faker->randomElement([
                'Sugerencia de mejora',
                'Propuesta de nueva funcionalidad',
                'Idea para el servicio',
                'Feedback sobre la plataforma',
                'Recomendación de proceso'
            ]),
            'priority' => 'low',
        ]);
    }

    /**
     * Create a high priority message.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Create an urgent message.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
            'message_type' => $this->faker->randomElement(['support', 'complaint']),
        ]);
    }

    /**
     * Create a message with internal notes.
     */
    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'internal_notes' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create an assigned message.
     */
    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to_user_id' => User::factory(),
            'status' => $this->faker->randomElement(['read', 'replied']),
        ]);
    }

    /**
     * Create an unread message.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'read_at' => null,
            'replied_at' => null,
            'replied_by_user_id' => null,
        ]);
    }

    /**
     * Create a message with specific organization.
     */
    public function withOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Create a message from specific email.
     */
    public function fromEmail(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }

    /**
     * Create a message with phone number.
     */
    public function withPhone(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => $this->faker->randomElement([
                '+34 600 123 456',
                '912 345 678',
                '+34 655 987 321',
                '985 456 789'
            ]),
        ]);
    }

    /**
     * Create a recent message.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-2 days', 'now'),
        ]);
    }

    /**
     * Create an old message.
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-30 days', '-10 days'),
        ]);
    }

    /**
     * Generate subject based on message type.
     */
    private function generateSubjectByType(string $type): string
    {
        return match ($type) {
            'contact' => $this->faker->randomElement([
                'Consulta sobre membresía',
                'Información sobre servicios',
                'Solicitud de información',
                'Pregunta general'
            ]),
            'support' => $this->faker->randomElement([
                'Problema técnico',
                'Error en la plataforma',
                'Solicitud de ayuda',
                'Soporte técnico'
            ]),
            'complaint' => $this->faker->randomElement([
                'Queja sobre el servicio',
                'Problema con facturación',
                'Reclamo',
                'Disconformidad'
            ]),
            'suggestion' => $this->faker->randomElement([
                'Sugerencia de mejora',
                'Propuesta',
                'Feedback',
                'Idea para mejorar'
            ]),
            default => $this->faker->sentence(4),
        };
    }

    /**
     * Generate message content based on type.
     */
    private function generateMessageByType(string $type): string
    {
        return match ($type) {
            'contact' => $this->faker->paragraph() . "\n\n" . 
                       "Espero su respuesta. Muchas gracias.",
            'support' => "Tengo un problema con " . $this->faker->words(3, true) . ". " .
                        $this->faker->paragraph() . "\n\n" .
                        "¿Podrían ayudarme a resolverlo?",
            'complaint' => "Quiero expresar mi disconformidad con " . $this->faker->words(3, true) . ". " .
                          $this->faker->paragraph() . "\n\n" .
                          "Espero una solución pronta.",
            'suggestion' => "Tengo una sugerencia para mejorar " . $this->faker->words(3, true) . ". " .
                           $this->faker->paragraph() . "\n\n" .
                           "Creo que sería beneficioso implementarlo.",
            default => $this->faker->paragraph(),
        };
    }
}
