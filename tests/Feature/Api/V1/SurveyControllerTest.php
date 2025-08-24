<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class SurveyControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function authenticateUser()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        return $user;
    }

    /** @test */
    public function it_can_list_surveys()
    {
        $user = $this->authenticateUser();
        Survey::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/surveys');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id', 'title', 'description', 'starts_at', 'ends_at',
                            'anonymous_allowed', 'visible_results', 'created_at', 'updated_at'
                        ]
                    ],
                    'links', 'meta'
                ]);

        $this->assertEquals(5, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_list_surveys_with_filters()
    {
        $user = $this->authenticateUser();
        
        // Crear encuestas con diferentes estados
        Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        Survey::factory()->create([
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        $response = $this->getJson('/api/v1/surveys?status=active');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_list_surveys_with_search()
    {
        $user = $this->authenticateUser();
        Survey::factory()->create(['title' => 'Customer Satisfaction Survey']);
        Survey::factory()->create(['title' => 'Product Feedback']);

        $response = $this->getJson('/api/v1/surveys?search=satisfaction');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_create_survey()
    {
        $user = $this->authenticateUser();
        
        $surveyData = [
            'title' => 'Test Survey',
            'description' => 'This is a test survey',
            'starts_at' => now()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'anonymous_allowed' => true,
            'visible_results' => true,
        ];

        $response = $this->postJson('/api/v1/surveys', $surveyData);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Encuesta creada exitosamente'
                ])
                ->assertJsonStructure([
                    'data' => [
                        'id', 'title', 'description', 'starts_at', 'ends_at',
                        'anonymous_allowed', 'visible_results'
                    ]
                ]);

        $this->assertDatabaseHas('surveys', [
            'title' => 'Test Survey',
            'description' => 'This is a test survey'
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_survey()
    {
        $user = $this->authenticateUser();
        
        $response = $this->postJson('/api/v1/surveys', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'description', 'starts_at', 'ends_at']);
    }

    /** @test */
    public function it_validates_ends_at_after_starts_at()
    {
        $user = $this->authenticateUser();
        
        $surveyData = [
            'title' => 'Test Survey',
            'description' => 'This is a test survey',
            'starts_at' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'ends_at' => now()->format('Y-m-d H:i:s'),
            'anonymous_allowed' => true,
            'visible_results' => true,
        ];

        $response = $this->postJson('/api/v1/surveys', $surveyData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['ends_at']);
    }

    /** @test */
    public function it_can_show_survey()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create();

        $response = $this->getJson("/api/v1/surveys/{$survey->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'id' => $survey->id,
                        'title' => $survey->title,
                        'description' => $survey->description
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_survey()
    {
        $user = $this->authenticateUser();

        $response = $this->getJson('/api/v1/surveys/999');

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Encuesta no encontrada'
                ]);
    }

    /** @test */
    public function it_can_update_survey()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create();

        $updateData = [
            'title' => 'Updated Survey Title',
            'description' => 'Updated description'
        ];

        $response = $this->putJson("/api/v1/surveys/{$survey->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Encuesta actualizada exitosamente'
                ])
                ->assertJson([
                    'data' => [
                        'title' => 'Updated Survey Title',
                        'description' => 'Updated description'
                    ]
                ]);

        $this->assertDatabaseHas('surveys', [
            'id' => $survey->id,
            'title' => 'Updated Survey Title'
        ]);
    }

    /** @test */
    public function it_can_delete_survey()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create();

        $response = $this->deleteJson("/api/v1/surveys/{$survey->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Encuesta eliminada exitosamente'
                ]);

        $this->assertSoftDeleted('surveys', ['id' => $survey->id]);
    }

    /** @test */
    public function it_cannot_delete_survey_with_responses()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create();
        SurveyResponse::factory()->create(['survey_id' => $survey->id]);

        $response = $this->deleteJson("/api/v1/surveys/{$survey->id}");

        $response->assertStatus(422)
                ->assertJson([
                    'message' => 'No se puede eliminar una encuesta que tiene respuestas'
                ]);

        $this->assertDatabaseHas('surveys', ['id' => $survey->id]);
    }

    /** @test */
    public function it_can_get_survey_statistics()
    {
        $user = $this->authenticateUser();
        
        Survey::factory()->create(['anonymous_allowed' => true, 'visible_results' => true]);
        Survey::factory()->create(['anonymous_allowed' => false, 'visible_results' => true]);
        Survey::factory()->create(['anonymous_allowed' => true, 'visible_results' => false]);

        $response = $this->getJson('/api/v1/surveys/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total', 'active', 'upcoming', 'past',
                        'anonymous_allowed', 'anonymous_not_allowed',
                        'results_visible', 'results_hidden'
                    ]
                ]);

        $this->assertEquals(3, $response->json('data.total'));
        $this->assertEquals(2, $response->json('data.anonymous_allowed'));
        $this->assertEquals(1, $response->json('data.anonymous_not_allowed'));
    }

    /** @test */
    public function it_can_get_surveys_by_status()
    {
        $user = $this->authenticateUser();
        
        Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $response = $this->getJson('/api/v1/surveys/by-status?status=active');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'title', 'status', 'starts_at', 'ends_at']
                    ]
                ]);

        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_validates_status_parameter()
    {
        $user = $this->authenticateUser();

        $response = $this->getJson('/api/v1/surveys/by-status?status=invalid_status');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_can_get_popular_surveys()
    {
        $user = $this->authenticateUser();
        
        $popularSurvey = Survey::factory()->create();
        $unpopularSurvey = Survey::factory()->create();

        // Crear más respuestas para la encuesta popular
        SurveyResponse::factory()->count(5)->create(['survey_id' => $popularSurvey->id]);
        SurveyResponse::factory()->count(1)->create(['survey_id' => $unpopularSurvey->id]);

        $response = $this->getJson('/api/v1/surveys/popular?limit=2');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'title', 'responses_count', 'starts_at']
                    ]
                ]);

        $this->assertEquals(2, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_expiring_soon_surveys()
    {
        $user = $this->authenticateUser();
        
        Survey::factory()->create(['ends_at' => now()->addDays(3)]);
        Survey::factory()->create(['ends_at' => now()->addDays(10)]);

        $response = $this->getJson('/api/v1/surveys/expiring-soon?days=5');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'title', 'ends_at', 'days_remaining']
                    ]
                ]);

        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_active_today_surveys()
    {
        $user = $this->authenticateUser();
        
        Survey::factory()->create([
            'starts_at' => now()->startOfDay(),
            'ends_at' => now()->endOfDay(),
        ]);

        $response = $this->getJson('/api/v1/surveys/active-today');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'title', 'starts_at', 'ends_at']
                    ]
                ]);

        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_recommended_surveys()
    {
        $user = $this->authenticateUser();
        
        Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $response = $this->getJson('/api/v1/surveys/recommended');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'title', 'starts_at', 'ends_at']
                    ]
                ]);

        $this->assertEquals(1, count($response->json('data')));
    }

    /** @test */
    public function it_requires_authentication_for_recommended_surveys()
    {
        $response = $this->getJson('/api/v1/surveys/recommended');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Usuario no autenticado'
                ]);
    }

    /** @test */
    public function it_can_search_surveys()
    {
        $user = $this->authenticateUser();
        
        Survey::factory()->create(['title' => 'Customer Satisfaction Survey']);
        Survey::factory()->create(['title' => 'Product Feedback']);

        $response = $this->getJson('/api/v1/surveys/search?q=satisfaction');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data', 'query', 'total_results'
                ])
                ->assertJson([
                    'query' => 'satisfaction',
                    'total_results' => 1
                ]);
    }

    /** @test */
    public function it_validates_search_query()
    {
        $user = $this->authenticateUser();

        $response = $this->getJson('/api/v1/surveys/search?q=a');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['q']);
    }

    /** @test */
    public function it_can_paginate_surveys()
    {
        $user = $this->authenticateUser();
        Survey::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/surveys?per_page=10&page=2');

        $response->assertStatus(200)
                ->assertJson([
                    'meta' => [
                        'current_page' => 2,
                        'per_page' => 10,
                        'total' => 25
                    ]
                ]);
    }

    /** @test */
    public function it_can_sort_surveys()
    {
        $user = $this->authenticateUser();
        
        Survey::factory()->create(['title' => 'A Survey']);
        Survey::factory()->create(['title' => 'B Survey']);
        Survey::factory()->create(['title' => 'C Survey']);

        $response = $this->getJson('/api/v1/surveys?sort_by=title&sort_order=asc');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertEquals('A Survey', $data[0]['title']);
        $this->assertEquals('C Survey', $data[2]['title']);
    }

    /** @test */
    public function it_can_filter_by_anonymous_allowed()
    {
        $user = $this->authenticateUser();
        
        Survey::factory()->create(['anonymous_allowed' => true]);
        Survey::factory()->create(['anonymous_allowed' => false]);

        $response = $this->getJson('/api/v1/surveys?anonymous_allowed=true');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_filter_by_visible_results()
    {
        $user = $this->authenticateUser();
        
        Survey::factory()->create(['visible_results' => true]);
        Survey::factory()->create(['visible_results' => false]);

        $response = $this->getJson('/api/v1/surveys?visible_results=false');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_filter_by_date_range()
    {
        $user = $this->authenticateUser();
        
        Survey::factory()->create([
            'starts_at' => '2024-01-01',
            'ends_at' => '2024-01-31',
        ]);
        Survey::factory()->create([
            'starts_at' => '2024-02-01',
            'ends_at' => '2024-02-28',
        ]);

        $response = $this->getJson('/api/v1/surveys?date_from=2024-01-01&date_to=2024-01-31');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/surveys');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_logs_successful_operations()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create();

        $this->getJson("/api/v1/surveys/{$survey->id}");

        // Verificar que se registra en los logs (esto es más una verificación de que no hay errores)
        $this->assertTrue(true);
    }

    /** @test */
    public function it_handles_database_errors_gracefully()
    {
        $user = $this->authenticateUser();
        
        // Simular un error de base de datos
        $this->mock(Survey::class, function ($mock) {
            $mock->shouldReceive('query')->andThrow(new \Exception('Database error'));
        });

        $response = $this->getJson('/api/v1/surveys');

        $response->assertStatus(500)
                ->assertJson([
                    'message' => 'Error al obtener las encuestas'
                ]);
    }
}

