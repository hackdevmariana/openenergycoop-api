<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $languages = array_keys(Event::LANGUAGES);
        $isPublic = $this->faker->boolean(80); // 80% de probabilidad de ser público
        
        return [
            'title' => $this->faker->sentence(3, 6),
            'description' => $this->faker->paragraphs(rand(2, 4), true),
            'date' => $this->faker->dateTimeBetween('now', '+6 months'),
            'location' => $this->faker->address(),
            'public' => $isPublic,
            'language' => $this->faker->randomElement($languages),
            'organization_id' => Organization::factory(),
            'is_draft' => $this->faker->boolean(20), // 20% de probabilidad de ser borrador
        ];
    }

    /**
     * Evento público
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'public' => true,
        ]);
    }

    /**
     * Evento privado
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'public' => false,
        ]);
    }

    /**
     * Evento publicado (no borrador)
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => false,
        ]);
    }

    /**
     * Evento borrador
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_draft' => true,
        ]);
    }

    /**
     * Evento futuro
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween('+1 day', '+6 months'),
        ]);
    }

    /**
     * Evento pasado
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
        ]);
    }

    /**
     * Evento de hoy
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween('today 09:00', 'today 18:00'),
        ]);
    }

    /**
     * Evento de esta semana
     */
    public function thisWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween('+1 day', '+7 days'),
        ]);
    }

    /**
     * Evento de este mes
     */
    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween('+1 day', '+30 days'),
        ]);
    }

    /**
     * Evento en español
     */
    public function spanish(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'es',
        ]);
    }

    /**
     * Evento en inglés
     */
    public function english(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'en',
        ]);
    }

    /**
     * Evento en catalán
     */
    public function catalan(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'ca',
        ]);
    }

    /**
     * Evento en euskera
     */
    public function basque(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'eu',
        ]);
    }

    /**
     * Evento en gallego
     */
    public function galician(): static
    {
        return $this->state(fn (array $attributes) => [
            'language' => 'gl',
        ]);
    }

    /**
     * Evento de conferencia
     */
    public function conference(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->randomElement([
                'Conferencia sobre ' . $this->faker->words(3, true),
                'Seminario de ' . $this->faker->words(2, true),
                'Jornada de ' . $this->faker->words(2, true),
                'Congreso de ' . $this->faker->words(2, true),
            ]),
            'description' => $this->faker->paragraphs(3, true),
            'location' => $this->faker->randomElement([
                'Auditorio Principal',
                'Sala de Conferencias',
                'Centro de Convenciones',
                'Universidad',
                'Hotel de Conferencias',
            ]),
        ]);
    }

    /**
     * Evento de taller
     */
    public function workshop(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->randomElement([
                'Taller de ' . $this->faker->words(2, true),
                'Workshop sobre ' . $this->faker->words(3, true),
                'Sesión práctica de ' . $this->faker->words(2, true),
                'Formación en ' . $this->faker->words(2, true),
            ]),
            'description' => $this->faker->paragraphs(2, true),
            'location' => $this->faker->randomElement([
                'Sala de Talleres',
                'Laboratorio',
                'Aula de Prácticas',
                'Centro de Formación',
                'Espacio de Coworking',
            ]),
        ]);
    }

    /**
     * Evento de networking
     */
    public function networking(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->randomElement([
                'Networking de ' . $this->faker->words(2, true),
                'Encuentro de ' . $this->faker->words(2, true),
                'Café con ' . $this->faker->words(2, true),
                'Afterwork de ' . $this->faker->words(2, true),
            ]),
            'description' => $this->faker->paragraphs(2, true),
            'location' => $this->faker->randomElement([
                'Café Central',
                'Bar de Networking',
                'Sala de Eventos',
                'Terraza',
                'Lounge',
            ]),
        ]);
    }

    /**
     * Evento de presentación
     */
    public function presentation(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->randomElement([
                'Presentación de ' . $this->faker->words(2, true),
                'Lanzamiento de ' . $this->faker->words(2, true),
                'Demo de ' . $this->faker->words(2, true),
                'Showcase de ' . $this->faker->words(2, true),
            ]),
            'description' => $this->faker->paragraphs(2, true),
            'location' => $this->faker->randomElement([
                'Sala de Presentaciones',
                'Auditorio',
                'Showroom',
                'Galería',
                'Espacio de Eventos',
            ]),
        ]);
    }

    /**
     * Evento de formación
     */
    public function training(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->randomElement([
                'Curso de ' . $this->faker->words(2, true),
                'Formación en ' . $this->faker->words(2, true),
                'Capacitación de ' . $this->faker->words(2, true),
                'Entrenamiento de ' . $this->faker->words(2, true),
            ]),
            'description' => $this->faker->paragraphs(3, true),
            'location' => $this->faker->randomElement([
                'Centro de Formación',
                'Aula de Capacitación',
                'Sala de Entrenamiento',
                'Instituto',
                'Academia',
            ]),
        ]);
    }
}
