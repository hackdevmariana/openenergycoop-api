<?php

namespace Database\Seeders;

use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear encuestas de ejemplo
        $surveys = [
            [
                'title' => 'Encuesta de Satisfacción del Cliente',
                'description' => 'Tu opinión es muy importante para nosotros. Ayúdanos a mejorar nuestros servicios respondiendo esta breve encuesta.',
                'starts_at' => now()->subDays(15),
                'ends_at' => now()->addDays(15),
                'anonymous_allowed' => true,
                'visible_results' => true,
            ],
            [
                'title' => 'Evaluación de la Experiencia del Usuario',
                'description' => 'Queremos conocer tu experiencia con nuestra plataforma. Tu feedback nos ayudará a hacer mejoras importantes.',
                'starts_at' => now()->subDays(10),
                'ends_at' => now()->addDays(20),
                'anonymous_allowed' => false,
                'visible_results' => true,
            ],
            [
                'title' => 'Encuesta de Investigación de Mercado',
                'description' => 'Esta encuesta forma parte de un estudio académico sobre hábitos de consumo de energía renovable.',
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(25),
                'anonymous_allowed' => true,
                'visible_results' => false,
            ],
            [
                'title' => 'Feedback sobre Nuevos Productos',
                'description' => 'Estamos desarrollando nuevos productos y necesitamos tu opinión. Tu input es crucial para el diseño final.',
                'starts_at' => now()->subDays(3),
                'ends_at' => now()->addDays(17),
                'anonymous_allowed' => false,
                'visible_results' => true,
            ],
            [
                'title' => 'Encuesta de Satisfacción del Empleado',
                'description' => 'Queremos conocer tu satisfacción laboral y sugerencias para mejorar el ambiente de trabajo.',
                'starts_at' => now()->subDays(20),
                'ends_at' => now()->addDays(10),
                'anonymous_allowed' => true,
                'visible_results' => false,
            ],
            [
                'title' => 'Evaluación del Evento Anual 2024',
                'description' => 'Ayúdanos a mejorar nuestros eventos futuros compartiendo tu experiencia en el evento anual.',
                'starts_at' => now()->subDays(25),
                'ends_at' => now()->addDays(5),
                'anonymous_allowed' => false,
                'visible_results' => true,
            ],
            [
                'title' => 'Encuesta sobre Sostenibilidad',
                'description' => 'Tu opinión sobre prácticas sostenibles es importante para nuestro compromiso con el medio ambiente.',
                'starts_at' => now()->subDays(8),
                'ends_at' => now()->addDays(22),
                'anonymous_allowed' => true,
                'visible_results' => true,
            ],
            [
                'title' => 'Feedback sobre la Atención al Cliente',
                'description' => 'Queremos evaluar la calidad de nuestro servicio de atención al cliente. Tu opinión es valiosa.',
                'starts_at' => now()->subDays(12),
                'ends_at' => now()->addDays(18),
                'anonymous_allowed' => true,
                'visible_results' => true,
            ],
            [
                'title' => 'Encuesta de Preferencias de Comunicación',
                'description' => 'Ayúdanos a mejorar nuestros canales de comunicación contándonos cómo prefieres recibir información.',
                'starts_at' => now()->subDays(6),
                'ends_at' => now()->addDays(24),
                'anonymous_allowed' => false,
                'visible_results' => true,
            ],
            [
                'title' => 'Evaluación de la Calidad del Producto',
                'description' => 'Tu evaluación de la calidad de nuestros productos nos ayuda a mantener altos estándares.',
                'starts_at' => now()->subDays(18),
                'ends_at' => now()->addDays(12),
                'anonymous_allowed' => false,
                'visible_results' => true,
            ],
            [
                'title' => 'Encuesta sobre Innovación Tecnológica',
                'description' => 'Queremos conocer tu opinión sobre las últimas innovaciones tecnológicas en el sector energético.',
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(28),
                'anonymous_allowed' => true,
                'visible_results' => false,
            ],
            [
                'title' => 'Feedback sobre la Plataforma Web',
                'description' => 'Tu experiencia con nuestra plataforma web es importante para nosotros. Ayúdanos a mejorarla.',
                'starts_at' => now()->subDays(14),
                'ends_at' => now()->addDays(16),
                'anonymous_allowed' => false,
                'visible_results' => true,
            ],
            [
                'title' => 'Encuesta de Satisfacción del Proveedor',
                'description' => 'Si eres proveedor de servicios, tu opinión nos ayuda a mejorar nuestras relaciones comerciales.',
                'starts_at' => now()->subDays(9),
                'ends_at' => now()->addDays(21),
                'anonymous_allowed' => true,
                'visible_results' => true,
            ],
            [
                'title' => 'Evaluación de la Formación y Capacitación',
                'description' => 'Tu opinión sobre nuestros programas de formación nos ayuda a mejorar la calidad educativa.',
                'starts_at' => now()->subDays(16),
                'ends_at' => now()->addDays(14),
                'anonymous_allowed' => false,
                'visible_results' => false,
            ],
            [
                'title' => 'Encuesta sobre Diversidad e Inclusión',
                'description' => 'Tu perspectiva sobre diversidad e inclusión es valiosa para crear un ambiente más inclusivo.',
                'starts_at' => now()->subDays(7),
                'ends_at' => now()->addDays(23),
                'anonymous_allowed' => true,
                'visible_results' => true,
            ],
        ];

        foreach ($surveys as $surveyData) {
            Survey::create($surveyData);
        }

        // Crear algunas respuestas de ejemplo
        $this->createSampleResponses();
    }

    /**
     * Crear respuestas de ejemplo para las encuestas
     */
    private function createSampleResponses(): void
    {
        $surveys = Survey::all();
        $users = User::all();

        if ($surveys->isEmpty() || $users->isEmpty()) {
            return;
        }

        // Crear respuestas para cada encuesta
        foreach ($surveys as $survey) {
            $this->createResponsesForSurvey($survey, $users);
        }
    }

    /**
     * Crear respuestas para una encuesta específica
     */
    private function createResponsesForSurvey(Survey $survey, $users): void
    {
        $numResponses = rand(5, 20); // Entre 5 y 20 respuestas por encuesta
        $usedUserIds = []; // Para evitar duplicados
        
        for ($i = 0; $i < $numResponses; $i++) {
            $isAnonymous = $survey->anonymous_allowed && rand(1, 10) <= 3; // 30% de probabilidad de ser anónima
            
            $responseData = $this->generateResponseData($survey->title);
            
            if ($isAnonymous) {
                $userId = null;
            } else {
                // Seleccionar un usuario que no haya respondido esta encuesta
                $availableUsers = $users->whereNotIn('id', $usedUserIds);
                if ($availableUsers->isEmpty()) {
                    // Si no hay usuarios disponibles, crear respuesta anónima
                    $userId = null;
                } else {
                    $user = $availableUsers->random();
                    $userId = $user->id;
                    $usedUserIds[] = $userId;
                }
            }
            
            // Usar createQuietly para evitar las validaciones del modelo
            SurveyResponse::createQuietly([
                'survey_id' => $survey->id,
                'user_id' => $userId,
                'response_data' => $responseData,
                'created_at' => fake()->dateTimeBetween($survey->starts_at, now()),
            ]);
        }
    }

    /**
     * Generar datos de respuesta basados en el título de la encuesta
     */
    private function generateResponseData(string $surveyTitle): array
    {
        if (str_contains(strtolower($surveyTitle), 'satisfacción')) {
            return [
                'overall_satisfaction' => rand(3, 5),
                'service_quality' => rand(3, 5),
                'staff_friendliness' => rand(3, 5),
                'value_for_money' => rand(3, 5),
                'would_recommend' => rand(0, 1),
                'main_comment' => $this->getRandomComment('satisfaction'),
                'improvement_suggestions' => rand(0, 1) ? $this->getRandomComment('improvement') : null,
            ];
        }

        if (str_contains(strtolower($surveyTitle), 'experiencia') || str_contains(strtolower($surveyTitle), 'usuario')) {
            return [
                'ease_of_use' => rand(3, 5),
                'interface_design' => rand(3, 5),
                'functionality' => rand(3, 5),
                'performance' => rand(3, 5),
                'overall_rating' => rand(3, 5),
                'main_comment' => $this->getRandomComment('experience'),
                'issues_encountered' => rand(0, 1) ? $this->getRandomComment('issue') : null,
            ];
        }

        if (str_contains(strtolower($surveyTitle), 'investigación') || str_contains(strtolower($surveyTitle), 'mercado')) {
            return [
                'age_group' => $this->getRandomAgeGroup(),
                'gender' => $this->getRandomGender(),
                'education_level' => $this->getRandomEducationLevel(),
                'income_range' => $this->getRandomIncomeRange(),
                'usage_frequency' => $this->getRandomUsageFrequency(),
                'preferences' => $this->getRandomPreferences(),
                'pain_points' => rand(0, 1) ? $this->getRandomComment('pain_point') : null,
            ];
        }

        if (str_contains(strtolower($surveyTitle), 'producto')) {
            return [
                'product_rating' => rand(3, 5),
                'ease_of_use' => rand(3, 5),
                'design_rating' => rand(3, 5),
                'performance_rating' => rand(3, 5),
                'value_rating' => rand(3, 5),
                'would_purchase_again' => rand(0, 1),
                'main_features_liked' => $this->getRandomComment('feature'),
                'issues_encountered' => rand(0, 1) ? $this->getRandomComment('issue') : null,
            ];
        }

        if (str_contains(strtolower($surveyTitle), 'evento')) {
            return [
                'event_rating' => rand(3, 5),
                'organization_rating' => rand(3, 5),
                'content_quality' => rand(3, 5),
                'venue_rating' => rand(3, 5),
                'speaker_rating' => rand(3, 5),
                'would_attend_again' => rand(0, 1),
                'event_highlights' => $this->getRandomComment('event'),
                'improvement_areas' => rand(0, 1) ? $this->getRandomComment('improvement') : null,
            ];
        }

        // Respuesta genérica
        return [
            'rating' => rand(3, 5),
            'satisfaction' => rand(6, 10),
            'comment' => $this->getRandomComment('general'),
            'category' => $this->getRandomCategory(),
            'urgency' => $this->getRandomUrgency(),
        ];
    }

    /**
     * Obtener comentarios aleatorios por categoría
     */
    private function getRandomComment(string $category): string
    {
        $comments = [
            'satisfaction' => [
                'Muy satisfecho con el servicio',
                'Excelente atención al cliente',
                'Superó mis expectativas',
                'Muy profesional y eficiente',
                'Recomiendo ampliamente',
            ],
            'experience' => [
                'La interfaz es muy intuitiva',
                'Fácil de usar y navegar',
                'Funcionalidades muy útiles',
                'Rendimiento excelente',
                'Diseño moderno y atractivo',
            ],
            'improvement' => [
                'Podría mejorar la velocidad de carga',
                'Sería útil tener más opciones de personalización',
                'La documentación podría ser más clara',
                'Necesita mejor soporte móvil',
                'Podría incluir más funcionalidades',
            ],
            'issue' => [
                'Algunas veces la aplicación se cuelga',
                'La búsqueda no siempre funciona bien',
                'El proceso de registro es complicado',
                'Faltan algunas características importantes',
                'La interfaz móvil necesita mejoras',
            ],
            'pain_point' => [
                'El proceso de pago es muy largo',
                'La información no está bien organizada',
                'Falta transparencia en los precios',
                'El servicio al cliente es lento',
                'La aplicación consume mucha batería',
            ],
            'feature' => [
                'Me gusta la funcionalidad de búsqueda',
                'La sincronización en tiempo real es genial',
                'El sistema de notificaciones es útil',
                'La interfaz es muy intuitiva',
                'Las opciones de personalización son buenas',
            ],
            'event' => [
                'El contenido fue muy interesante',
                'Los ponentes eran excelentes',
                'La organización fue impecable',
                'El lugar era perfecto',
                'La experiencia fue muy enriquecedora',
            ],
            'general' => [
                'Buen servicio en general',
                'Cumple con lo esperado',
                'Recomiendo el servicio',
                'Buena relación calidad-precio',
                'Satisfecho con la experiencia',
            ],
        ];

        $categoryComments = $comments[$category] ?? $comments['general'];
        return $categoryComments[array_rand($categoryComments)];
    }

    /**
     * Obtener grupo de edad aleatorio
     */
    private function getRandomAgeGroup(): string
    {
        $ageGroups = ['18-25', '26-35', '36-45', '46-55', '55+'];
        return $ageGroups[array_rand($ageGroups)];
    }

    /**
     * Obtener género aleatorio
     */
    private function getRandomGender(): string
    {
        $genders = ['masculino', 'femenino', 'no binario', 'prefiero no decir'];
        return $genders[array_rand($genders)];
    }

    /**
     * Obtener nivel educativo aleatorio
     */
    private function getRandomEducationLevel(): string
    {
        $levels = ['secundaria', 'universitaria', 'postgrado', 'otro'];
        return $levels[array_rand($levels)];
    }

    /**
     * Obtener rango de ingresos aleatorio
     */
    private function getRandomIncomeRange(): string
    {
        $ranges = ['<20k', '20k-40k', '40k-60k', '60k-80k', '80k+'];
        return $ranges[array_rand($ranges)];
    }

    /**
     * Obtener frecuencia de uso aleatoria
     */
    private function getRandomUsageFrequency(): string
    {
        $frequencies = ['diario', 'semanal', 'mensual', 'ocasional', 'nunca'];
        return $frequencies[array_rand($frequencies)];
    }

    /**
     * Obtener preferencias aleatorias
     */
    private function getRandomPreferences(): array
    {
        $allPreferences = ['calidad', 'precio', 'conveniencia', 'sostenibilidad', 'innovación', 'seguridad', 'velocidad'];
        $numPreferences = rand(2, 4);
        return array_rand(array_flip($allPreferences), $numPreferences);
    }

    /**
     * Obtener categoría aleatoria
     */
    private function getRandomCategory(): string
    {
        $categories = ['producto', 'servicio', 'atención', 'precio', 'calidad', 'innovación'];
        return $categories[array_rand($categories)];
    }

    /**
     * Obtener urgencia aleatoria
     */
    private function getRandomUrgency(): string
    {
        $urgencies = ['baja', 'media', 'alta', 'crítica'];
        return $urgencies[array_rand($urgencies)];
    }
}
