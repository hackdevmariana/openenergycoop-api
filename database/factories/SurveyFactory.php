<?php

namespace Database\Factories;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Survey>
 */
class SurveyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Survey::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $endsAt = Carbon::parse($startsAt)->addDays($this->faker->numberBetween(1, 30));

        return [
            'title' => $this->faker->sentence(3, 6),
            'description' => $this->faker->paragraphs(2, true),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'anonymous_allowed' => $this->faker->boolean(70), // 70% de probabilidad de permitir anónimos
            'visible_results' => $this->faker->boolean(80), // 80% de probabilidad de mostrar resultados
        ];
    }

    /**
     * Estado para encuestas activas
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            $now = now();
            return [
                'starts_at' => $now->copy()->subDays($this->faker->numberBetween(1, 10)),
                'ends_at' => $now->copy()->addDays($this->faker->numberBetween(1, 20)),
            ];
        });
    }

    /**
     * Estado para encuestas futuras
     */
    public function upcoming(): static
    {
        return $this->state(function (array $attributes) {
            $now = now();
            return [
                'starts_at' => $now->copy()->addDays($this->faker->numberBetween(1, 30)),
                'ends_at' => $now->copy()->addDays($this->faker->numberBetween(31, 60)),
            ];
        });
    }

    /**
     * Estado para encuestas pasadas
     */
    public function past(): static
    {
        return $this->state(function (array $attributes) {
            $now = now();
            return [
                'starts_at' => $now->copy()->subDays($this->faker->numberBetween(31, 60)),
                'ends_at' => $now->copy()->subDays($this->faker->numberBetween(1, 30)),
            ];
        });
    }

    /**
     * Estado para encuestas que expiran pronto
     */
    public function expiringSoon(): static
    {
        return $this->state(function (array $attributes) {
            $now = now();
            return [
                'starts_at' => $now->copy()->subDays($this->faker->numberBetween(5, 15)),
                'ends_at' => $now->copy()->addDays($this->faker->numberBetween(1, 7)),
            ];
        });
    }

    /**
     * Estado para encuestas que comienzan hoy
     */
    public function startingToday(): static
    {
        return $this->state(function (array $attributes) {
            $today = now()->startOfDay();
            return [
                'starts_at' => $today->copy()->addHours($this->faker->numberBetween(0, 23)),
                'ends_at' => $today->copy()->addDays($this->faker->numberBetween(1, 30)),
            ];
        });
    }

    /**
     * Estado para encuestas que permiten respuestas anónimas
     */
    public function anonymousAllowed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'anonymous_allowed' => true,
            ];
        });
    }

    /**
     * Estado para encuestas que no permiten respuestas anónimas
     */
    public function anonymousNotAllowed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'anonymous_allowed' => false,
            ];
        });
    }

    /**
     * Estado para encuestas con resultados visibles
     */
    public function resultsVisible(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'visible_results' => true,
            ];
        });
    }

    /**
     * Estado para encuestas con resultados ocultos
     */
    public function resultsHidden(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'visible_results' => false,
            ];
        });
    }

    /**
     * Estado para encuestas cortas (1-3 días)
     */
    public function short(): static
    {
        return $this->state(function (array $attributes) {
            $startsAt = $this->faker->dateTimeBetween('-1 month', '+1 month');
            $endsAt = Carbon::parse($startsAt)->addDays($this->faker->numberBetween(1, 3));
            
            return [
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ];
        });
    }

    /**
     * Estado para encuestas largas (30-90 días)
     */
    public function long(): static
    {
        return $this->state(function (array $attributes) {
            $startsAt = $this->faker->dateTimeBetween('-1 month', '+1 month');
            $endsAt = Carbon::parse($startsAt)->addDays($this->faker->numberBetween(30, 90));
            
            return [
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ];
        });
    }

    /**
     * Estado para encuestas de satisfacción
     */
    public function satisfaction(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'title' => $this->faker->randomElement([
                    'Encuesta de Satisfacción del Cliente',
                    'Evaluación de Nuestros Servicios',
                    'Opinión sobre la Experiencia del Usuario',
                    'Valoración de la Calidad del Servicio',
                    'Feedback sobre Nuestros Productos'
                ]),
                'description' => $this->faker->paragraphs(1, true) . ' Tu opinión es muy importante para nosotros.',
                'anonymous_allowed' => true,
                'visible_results' => true,
            ];
        });
    }

    /**
     * Estado para encuestas de investigación
     */
    public function research(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'title' => $this->faker->randomElement([
                    'Encuesta de Investigación de Mercado',
                    'Estudio sobre Hábitos de Consumo',
                    'Investigación sobre Preferencias del Usuario',
                    'Análisis de Comportamiento del Cliente',
                    'Estudio de Satisfacción del Empleado'
                ]),
                'description' => $this->faker->paragraphs(2, true) . ' Esta encuesta forma parte de un estudio académico.',
                'anonymous_allowed' => true,
                'visible_results' => false,
            ];
        });
    }

    /**
     * Estado para encuestas de eventos
     */
    public function event(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'title' => $this->faker->randomElement([
                    'Evaluación del Evento Anual',
                    'Feedback sobre la Conferencia',
                    'Opinión sobre el Taller',
                    'Valoración del Seminario',
                    'Encuesta Post-Evento'
                ]),
                'description' => $this->faker->paragraphs(1, true) . ' Ayúdanos a mejorar nuestros eventos futuros.',
                'anonymous_allowed' => false,
                'visible_results' => true,
            ];
        });
    }

    /**
     * Estado para encuestas de producto
     */
    public function product(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'title' => $this->faker->randomElement([
                    'Encuesta sobre Nuevo Producto',
                    'Evaluación de Características',
                    'Opinión sobre Funcionalidades',
                    'Valoración de la Usabilidad',
                    'Feedback sobre el Diseño'
                ]),
                'description' => $this->faker->paragraphs(2, true) . ' Tu feedback nos ayuda a mejorar nuestros productos.',
                'anonymous_allowed' => false,
                'visible_results' => true,
            ];
        });
    }

    /**
     * Estado para encuestas urgentes
     */
    public function urgent(): static
    {
        return $this->state(function (array $attributes) {
            $now = now();
            return [
                'starts_at' => $now->copy()->subHours($this->faker->numberBetween(1, 6)),
                'ends_at' => $now->copy()->addHours($this->faker->numberBetween(1, 24)),
                'title' => $this->faker->randomElement([
                    'Encuesta Urgente - Tu Opinión Importa',
                    'Feedback Inmediato Necesario',
                    'Encuesta Crítica - Respuesta Rápida',
                    'Evaluación de Emergencia',
                    'Encuesta de Prioridad Alta'
                ]),
                'description' => 'Esta es una encuesta de alta prioridad que requiere tu atención inmediata.',
            ];
        });
    }

    /**
     * Estado para encuestas de prueba
     */
    public function test(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'title' => 'Encuesta de Prueba - ' . $this->faker->words(3, true),
                'description' => 'Esta es una encuesta de prueba para verificar la funcionalidad del sistema.',
                'anonymous_allowed' => true,
                'visible_results' => true,
            ];
        });
    }
}
