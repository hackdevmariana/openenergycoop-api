<?php

namespace Database\Factories;

use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SurveyResponse>
 */
class SurveyResponseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SurveyResponse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'survey_id' => Survey::factory(),
            'user_id' => User::factory(),
            'response_data' => $this->generateSampleResponseData(),
        ];
    }

    /**
     * Estado para respuestas anónimas
     */
    public function anonymous(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => null,
                'response_data' => $this->generateSampleResponseData(),
            ];
        });
    }

    /**
     * Estado para respuestas identificadas
     */
    public function identified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => User::factory(),
                'response_data' => $this->generateSampleResponseData(),
            ];
        });
    }

    /**
     * Estado para respuestas recientes (últimas 24 horas)
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('-24 hours', 'now'),
            ];
        });
    }

    /**
     * Estado para respuestas antiguas (más de 30 días)
     */
    public function old(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('-6 months', '-30 days'),
            ];
        });
    }

    /**
     * Estado para respuestas de hoy
     */
    public function today(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('today', 'today'),
            ];
        });
    }

    /**
     * Estado para respuestas de esta semana
     */
    public function thisWeek(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('monday this week', 'sunday this week'),
            ];
        });
    }

    /**
     * Estado para respuestas de este mes
     */
    public function thisMonth(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('first day of this month', 'last day of this month'),
            ];
        });
    }

    /**
     * Estado para respuestas con datos mínimos
     */
    public function minimal(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'response_data' => [
                    'rating' => $this->faker->numberBetween(1, 5),
                    'comment' => $this->faker->sentence(),
                ],
            ];
        });
    }

    /**
     * Estado para respuestas con datos extensos
     */
    public function extensive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'response_data' => [
                    'rating' => $this->faker->numberBetween(1, 5),
                    'comment' => $this->faker->paragraphs(3, true),
                    'satisfaction' => $this->faker->numberBetween(1, 10),
                    'recommendation' => $this->faker->boolean(),
                    'improvements' => $this->faker->paragraphs(2, true),
                    'category' => $this->faker->randomElement(['producto', 'servicio', 'atención', 'precio', 'calidad']),
                    'urgency' => $this->faker->randomElement(['baja', 'media', 'alta', 'crítica']),
                    'contact_preference' => $this->faker->randomElement(['email', 'teléfono', 'no contactar']),
                ],
            ];
        });
    }

    /**
     * Estado para respuestas de satisfacción
     */
    public function satisfaction(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'response_data' => [
                    'overall_satisfaction' => $this->faker->numberBetween(1, 5),
                    'service_quality' => $this->faker->numberBetween(1, 5),
                    'staff_friendliness' => $this->faker->numberBetween(1, 5),
                    'value_for_money' => $this->faker->numberBetween(1, 5),
                    'would_recommend' => $this->faker->boolean(),
                    'main_comment' => $this->faker->paragraph(),
                    'improvement_suggestions' => $this->faker->optional(0.7)->paragraph(),
                ],
            ];
        });
    }

    /**
     * Estado para respuestas de investigación
     */
    public function research(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'response_data' => [
                    'age_group' => $this->faker->randomElement(['18-25', '26-35', '36-45', '46-55', '55+']),
                    'gender' => $this->faker->randomElement(['masculino', 'femenino', 'no binario', 'prefiero no decir']),
                    'education_level' => $this->faker->randomElement(['secundaria', 'universitaria', 'postgrado', 'otro']),
                    'income_range' => $this->faker->randomElement(['<20k', '20k-40k', '40k-60k', '60k-80k', '80k+']),
                    'usage_frequency' => $this->faker->randomElement(['diario', 'semanal', 'mensual', 'ocasional', 'nunca']),
                    'preferences' => $this->faker->randomElements(['calidad', 'precio', 'conveniencia', 'sostenibilidad', 'innovación'], $this->faker->numberBetween(1, 3)),
                    'pain_points' => $this->faker->optional(0.8)->paragraph(),
                    'wishlist_features' => $this->faker->optional(0.6)->paragraph(),
                ],
            ];
        });
    }

    /**
     * Estado para respuestas de eventos
     */
    public function event(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'response_data' => [
                    'event_rating' => $this->faker->numberBetween(1, 5),
                    'organization_rating' => $this->faker->numberBetween(1, 5),
                    'content_quality' => $this->faker->numberBetween(1, 5),
                    'venue_rating' => $this->faker->numberBetween(1, 5),
                    'speaker_rating' => $this->faker->numberBetween(1, 5),
                    'would_attend_again' => $this->faker->boolean(),
                    'event_highlights' => $this->faker->paragraph(),
                    'improvement_areas' => $this->faker->optional(0.7)->paragraph(),
                    'suggested_topics' => $this->faker->optional(0.5)->paragraph(),
                ],
            ];
        });
    }

    /**
     * Estado para respuestas de producto
     */
    public function product(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'response_data' => [
                    'product_rating' => $this->faker->numberBetween(1, 5),
                    'ease_of_use' => $this->faker->numberBetween(1, 5),
                    'design_rating' => $this->faker->numberBetween(1, 5),
                    'performance_rating' => $this->faker->numberBetween(1, 5),
                    'value_rating' => $this->faker->numberBetween(1, 5),
                    'would_purchase_again' => $this->faker->boolean(),
                    'main_features_liked' => $this->faker->paragraph(),
                    'issues_encountered' => $this->faker->optional(0.6)->paragraph(),
                    'feature_requests' => $this->faker->optional(0.4)->paragraph(),
                ],
            ];
        });
    }

    /**
     * Estado para respuestas con calificaciones altas
     */
    public function highRating(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'response_data' => [
                    'rating' => $this->faker->numberBetween(4, 5),
                    'satisfaction' => $this->faker->numberBetween(8, 10),
                    'comment' => $this->faker->randomElement([
                        'Excelente servicio, muy satisfecho',
                        'Muy buena experiencia, lo recomiendo',
                        'Superó mis expectativas',
                        'Servicio de alta calidad',
                        'Muy profesional y eficiente'
                    ]),
                ],
            ];
        });
    }

    /**
     * Estado para respuestas con calificaciones bajas
     */
    public function lowRating(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'response_data' => [
                    'rating' => $this->faker->numberBetween(1, 2),
                    'satisfaction' => $this->faker->numberBetween(1, 3),
                    'comment' => $this->faker->randomElement([
                        'No cumplió con mis expectativas',
                        'Servicio deficiente',
                        'Necesita mejoras significativas',
                        'No lo recomiendo',
                        'Experiencia negativa'
                    ]),
                ],
            ];
        });
    }

    /**
     * Estado para respuestas con calificaciones mixtas
     */
    public function mixedRating(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'response_data' => [
                    'rating' => $this->faker->numberBetween(2, 4),
                    'satisfaction' => $this->faker->numberBetween(4, 7),
                    'comment' => $this->faker->randomElement([
                        'Bueno pero podría mejorar',
                        'Aceptable, hay aspectos a mejorar',
                        'Regular, ni bueno ni malo',
                        'Tiene pros y contras',
                        'Satisfactorio con reservas'
                    ]),
                ],
            ];
        });
    }

    /**
     * Estado para respuestas con comentarios largos
     */
    public function longComment(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'response_data' => [
                    'rating' => $this->faker->numberBetween(1, 5),
                    'detailed_feedback' => $this->faker->paragraphs(4, true),
                    'specific_examples' => $this->faker->paragraphs(2, true),
                    'suggestions' => $this->faker->paragraphs(2, true),
                ],
            ];
        });
    }

    /**
     * Estado para respuestas con comentarios cortos
     */
    public function shortComment(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'response_data' => [
                    'rating' => $this->faker->numberBetween(1, 5),
                    'comment' => $this->faker->sentence(),
                ],
            ];
        });
    }

    /**
     * Estado para respuestas con datos estructurados
     */
    public function structured(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'response_data' => [
                    'demographics' => [
                        'age' => $this->faker->numberBetween(18, 80),
                        'location' => $this->faker->city(),
                        'occupation' => $this->faker->jobTitle(),
                    ],
                    'preferences' => [
                        'primary_interest' => $this->faker->randomElement(['tecnología', 'salud', 'educación', 'entretenimiento', 'negocios']),
                        'communication_channel' => $this->faker->randomElement(['email', 'sms', 'app', 'web', 'teléfono']),
                        'frequency' => $this->faker->randomElement(['diario', 'semanal', 'mensual']),
                    ],
                    'feedback' => [
                        'rating' => $this->faker->numberBetween(1, 5),
                        'comment' => $this->faker->paragraph(),
                    ],
                ],
            ];
        });
    }

    /**
     * Generar datos de respuesta de muestra
     */
    private function generateSampleResponseData(): array
    {
        $types = ['satisfaction', 'research', 'event', 'product', 'minimal', 'extensive'];
        $type = $this->faker->randomElement($types);

        return match ($type) {
            'satisfaction' => [
                'overall_satisfaction' => $this->faker->numberBetween(1, 5),
                'service_quality' => $this->faker->numberBetween(1, 5),
                'would_recommend' => $this->faker->boolean(),
                'comment' => $this->faker->paragraph(),
            ],
            'research' => [
                'age_group' => $this->faker->randomElement(['18-25', '26-35', '36-45', '46-55', '55+']),
                'usage_frequency' => $this->faker->randomElement(['diario', 'semanal', 'mensual']),
                'preferences' => $this->faker->randomElements(['calidad', 'precio', 'conveniencia'], 2),
            ],
            'event' => [
                'event_rating' => $this->faker->numberBetween(1, 5),
                'would_attend_again' => $this->faker->boolean(),
                'highlights' => $this->faker->paragraph(),
            ],
            'product' => [
                'product_rating' => $this->faker->numberBetween(1, 5),
                'ease_of_use' => $this->faker->numberBetween(1, 5),
                'would_purchase_again' => $this->faker->boolean(),
            ],
            'minimal' => [
                'rating' => $this->faker->numberBetween(1, 5),
                'comment' => $this->faker->sentence(),
            ],
            'extensive' => [
                'rating' => $this->faker->numberBetween(1, 5),
                'satisfaction' => $this->faker->numberBetween(1, 10),
                'comment' => $this->faker->paragraph(),
                'category' => $this->faker->randomElement(['producto', 'servicio', 'atención']),
                'improvements' => $this->faker->paragraph(),
            ],
            default => [
                'rating' => $this->faker->numberBetween(1, 5),
                'comment' => $this->faker->sentence(),
            ],
        };
    }
}
