<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'message' => $this->faker->paragraph(2),
            'read_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
            'type' => $this->faker->randomElement(Notification::getTypes()),
            'delivered_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function info(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Notification::TYPE_INFO,
                'title' => 'Información: ' . $this->faker->words(2, true),
            ];
        });
    }

    public function alert(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Notification::TYPE_ALERT,
                'title' => 'Alerta: ' . $this->faker->words(2, true),
            ];
        });
    }

    public function success(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Notification::TYPE_SUCCESS,
                'title' => 'Éxito: ' . $this->faker->words(2, true),
            ];
        });
    }

    public function warning(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Notification::TYPE_WARNING,
                'title' => 'Advertencia: ' . $this->faker->words(2, true),
            ];
        });
    }

    public function error(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Notification::TYPE_ERROR,
                'title' => 'Error: ' . $this->faker->words(2, true),
            ];
        });
    }

    public function read(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'read_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            ];
        });
    }

    public function unread(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'read_at' => null,
            ];
        });
    }

    public function delivered(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'delivered_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            ];
        });
    }

    public function notDelivered(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'delivered_at' => null,
            ];
        });
    }

    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('-24 hours', 'now'),
            ];
        });
    }

    public function old(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('-1 month', '-1 week'),
            ];
        });
    }

    public function urgent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => $this->faker->randomElement([Notification::TYPE_ERROR, Notification::TYPE_ALERT]),
                'title' => 'URGENTE: ' . $this->faker->words(2, true),
            ];
        });
    }

    public function lowPriority(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Notification::TYPE_INFO,
                'title' => 'Info: ' . $this->faker->words(2, true),
            ];
        });
    }

    public function wallet(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'title' => 'Wallet: ' . $this->faker->words(2, true),
                'message' => $this->faker->randomElement([
                    'Tu transacción ha sido procesada exitosamente.',
                    'Nuevo crédito disponible en tu wallet.',
                    'Actualización de saldo disponible.',
                    'Confirmación de pago recibida.',
                ]),
            ];
        });
    }

    public function event(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'title' => 'Evento: ' . $this->faker->words(2, true),
                'message' => $this->faker->randomElement([
                    'Nuevo evento programado para mañana.',
                    'Recordatorio: Evento en 2 horas.',
                    'Evento cancelado por condiciones climáticas.',
                    'Nuevo horario disponible para el evento.',
                ]),
            ];
        });
    }

    public function message(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'title' => 'Mensaje: ' . $this->faker->words(2, true),
                'message' => $this->faker->randomElement([
                    'Tienes un nuevo mensaje del equipo.',
                    'Respuesta recibida a tu consulta.',
                    'Mensaje importante del administrador.',
                    'Nueva notificación del sistema.',
                ]),
            ];
        });
    }

    public function general(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'title' => 'General: ' . $this->faker->words(2, true),
                'message' => $this->faker->randomElement([
                    'Actualización del sistema completada.',
                    'Nuevas funcionalidades disponibles.',
                    'Mantenimiento programado para esta noche.',
                    'Información importante sobre el servicio.',
                ]),
            ];
        });
    }

    public function shortMessage(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'message' => $this->faker->sentence(),
            ];
        });
    }

    public function longMessage(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'message' => $this->faker->paragraphs(3, true),
            ];
        });
    }

    public function forUser(int $userId): static
    {
        return $this->state(function (array $attributes) use ($userId) {
            return [
                'user_id' => $userId,
            ];
        });
    }

    public function withReadTime(): static
    {
        return $this->state(function (array $attributes) {
            $createdAt = $this->faker->dateTimeBetween('-1 week', '-1 hour');
            return [
                'created_at' => $createdAt,
                'read_at' => $this->faker->dateTimeBetween($createdAt, 'now'),
            ];
        });
    }

    public function withDeliveryTime(): static
    {
        return $this->state(function (array $attributes) {
            $createdAt = $this->faker->dateTimeBetween('-1 week', '-1 hour');
            return [
                'created_at' => $createdAt,
                'delivered_at' => $this->faker->dateTimeBetween($createdAt, 'now'),
            ];
        });
    }
}
