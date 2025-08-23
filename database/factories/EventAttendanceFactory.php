<?php

namespace Database\Factories;

use App\Models\EventAttendance;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventAttendance>
 */
class EventAttendanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EventAttendance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement([
            EventAttendance::STATUS_REGISTERED,
            EventAttendance::STATUS_ATTENDED,
            EventAttendance::STATUS_CANCELLED,
            EventAttendance::STATUS_NO_SHOW,
        ]);

        $registeredAt = $this->faker->dateTimeBetween('-2 months', 'now');
        $checkedInAt = null;
        $cancellationReason = null;
        $notes = null;

        // Si asistió, generar fecha de check-in
        if ($status === EventAttendance::STATUS_ATTENDED) {
            $checkedInAt = $this->faker->dateTimeBetween($registeredAt, 'now');
        }

        // Si canceló, generar razón
        if ($status === EventAttendance::STATUS_CANCELLED) {
            $cancellationReason = $this->faker->randomElement([
                'Conflicto de horarios',
                'Enfermedad',
                'Emergencia familiar',
                'Cambio de planes',
                'Problemas de transporte',
                'Obligaciones laborales',
                'Evento cancelado',
                'No disponible',
            ]);
        }

        // Generar notas para algunos registros
        if ($this->faker->boolean(30)) {
            $notes = $this->faker->sentence();
        }

        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'status' => $status,
            'registered_at' => $registeredAt,
            'checked_in_at' => $checkedInAt,
            'cancellation_reason' => $cancellationReason,
            'notes' => $notes,
            'checkin_token' => bin2hex(random_bytes(32)),
        ];
    }

    /**
     * Asistencia registrada
     */
    public function registered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventAttendance::STATUS_REGISTERED,
            'checked_in_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Asistencia que asistió
     */
    public function attended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventAttendance::STATUS_ATTENDED,
            'checked_in_at' => $this->faker->dateTimeBetween($attributes['registered_at'], 'now'),
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Asistencia cancelada
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventAttendance::STATUS_CANCELLED,
            'checked_in_at' => null,
            'cancellation_reason' => $this->faker->randomElement([
                'Conflicto de horarios',
                'Enfermedad',
                'Emergencia familiar',
                'Cambio de planes',
                'Problemas de transporte',
                'Obligaciones laborales',
                'Evento cancelado',
                'No disponible',
            ]),
        ]);
    }

    /**
     * Asistencia que no asistió
     */
    public function noShow(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventAttendance::STATUS_NO_SHOW,
            'checked_in_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Asistencia reciente (últimos 7 días)
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'registered_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Asistencia antigua (más de 1 mes)
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'registered_at' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
        ]);
    }

    /**
     * Asistencia con notas
     */
    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Asistencia con razón de cancelación
     */
    public function withCancellationReason(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventAttendance::STATUS_CANCELLED,
            'cancellation_reason' => $this->faker->sentence(),
            'checked_in_at' => null,
        ]);
    }

    /**
     * Asistencia con check-in tardío
     */
    public function lateCheckin(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventAttendance::STATUS_ATTENDED,
            'checked_in_at' => $this->faker->dateTimeBetween(
                $attributes['registered_at']->addHours(1),
                $attributes['registered_at']->addHours(3)
            ),
        ]);
    }

    /**
     * Asistencia con check-in temprano
     */
    public function earlyCheckin(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventAttendance::STATUS_ATTENDED,
            'checked_in_at' => $this->faker->dateTimeBetween(
                $attributes['registered_at']->subHours(1),
                $attributes['registered_at']
            ),
        ]);
    }

    /**
     * Asistencia de alta prioridad (con notas especiales)
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $this->faker->randomElement([
                'VIP - Requiere atención especial',
                'Ponente principal',
                'Patrocinador del evento',
                'Invitado especial',
                'Medios de comunicación',
                'Autoridad local',
                'Representante gubernamental',
            ]),
        ]);
    }

    /**
     * Asistencia de grupo
     */
    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $this->faker->randomElement([
                'Grupo de estudiantes',
                'Equipo de trabajo',
                'Familia',
                'Grupo de amigos',
                'Delegación',
                'Equipo deportivo',
                'Grupo musical',
            ]),
        ]);
    }

    /**
     * Asistencia con requisitos especiales
     */
    public function specialRequirements(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $this->faker->randomElement([
                'Acceso para silla de ruedas',
                'Intérprete de lengua de señas',
                'Dieta especial',
                'Asiento preferente',
                'Acceso temprano',
                'Estacionamiento reservado',
                'Acompañante requerido',
            ]),
        ]);
    }

    /**
     * Asistencia de último minuto
     */
    public function lastMinute(): static
    {
        return $this->state(fn (array $attributes) => [
            'registered_at' => $this->faker->dateTimeBetween('-2 hours', 'now'),
            'notes' => 'Registro de último minuto',
        ]);
    }

    /**
     * Asistencia confirmada por teléfono
     */
    public function phoneConfirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => 'Confirmado por teléfono - ' . $this->faker->phoneNumber(),
        ]);
    }

    /**
     * Asistencia con pago pendiente
     */
    public function paymentPending(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => 'Pago pendiente - ' . $this->faker->randomElement([
                'Transferencia bancaria',
                'Tarjeta de crédito',
                'Efectivo en el evento',
                'Factura pendiente',
                'Reembolso solicitado',
            ]),
        ]);
    }
}
