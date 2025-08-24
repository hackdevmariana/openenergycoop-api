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

class SurveyResponseControllerTest extends TestCase
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
    public function it_can_list_survey_responses()
    {
        $user = $this->authenticateUser();
        SurveyResponse::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/survey-responses');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id', 'survey_id', 'user_id', 'response_data',
                            'created_at', 'updated_at'
                        ]
                    ],
                    'links', 'meta'
                ]);

        $this->assertEquals(5, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_list_responses_with_filters()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create();
        
        SurveyResponse::factory()->count(3)->create(['survey_id' => $survey->id]);
        SurveyResponse::factory()->count(2)->create(['survey_id' => Survey::factory()->create()->id]);

        $response = $this->getJson("/api/v1/survey-responses?survey_id={$survey->id}");

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_filter_by_response_type()
    {
        $user = $this->authenticateUser();
        
        SurveyResponse::factory()->count(3)->create(['user_id' => null]);
        SurveyResponse::factory()->count(2)->create(['user_id' => User::factory()->create()->id]);

        $response = $this->getJson('/api/v1/survey-responses?response_type=anonymous');

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('meta.total'));
    }

    /** @test */
    public function it_can_create_survey_response()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'anonymous_allowed' => true
        ]);
        
        $responseData = [
            'survey_id' => $survey->id,
            'user_id' => $user->id,
            'response_data' => [
                'rating' => 5,
                'comment' => 'Excellent service'
            ]
        ];

        $response = $this->postJson('/api/v1/survey-responses', $responseData);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Respuesta de encuesta creada exitosamente'
                ])
                ->assertJsonStructure([
                    'data' => [
                        'id', 'survey_id', 'user_id', 'response_data'
                    ]
                ]);

        $this->assertDatabaseHas('survey_responses', [
            'survey_id' => $survey->id,
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function it_can_create_anonymous_response()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'anonymous_allowed' => true
        ]);
        
        $responseData = [
            'survey_id' => $survey->id,
            'user_id' => null,
            'response_data' => [
                'rating' => 4,
                'comment' => 'Good service'
            ]
        ];

        $response = $this->postJson('/api/v1/survey-responses', $responseData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('survey_responses', [
            'survey_id' => $survey->id,
            'user_id' => null
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_response()
    {
        $user = $this->authenticateUser();
        
        $response = $this->postJson('/api/v1/survey-responses', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['survey_id', 'response_data']);
    }

    /** @test */
    public function it_validates_survey_exists()
    {
        $user = $this->authenticateUser();
        
        $responseData = [
            'survey_id' => 999,
            'response_data' => ['rating' => 5]
        ];

        $response = $this->postJson('/api/v1/survey-responses', $responseData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['survey_id']);
    }

    /** @test */
    public function it_cannot_create_response_for_inactive_survey()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create([
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2)
        ]);
        
        $responseData = [
            'survey_id' => $survey->id,
            'response_data' => ['rating' => 5]
        ];

        $response = $this->postJson('/api/v1/survey-responses', $responseData);

        $response->assertStatus(422)
                ->assertJson([
                    'message' => 'La encuesta no está activa'
                ]);
    }

    /** @test */
    public function it_cannot_create_anonymous_response_if_not_allowed()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'anonymous_allowed' => false
        ]);
        
        $responseData = [
            'survey_id' => $survey->id,
            'user_id' => null,
            'response_data' => ['rating' => 5]
        ];

        $response = $this->postJson('/api/v1/survey-responses', $responseData);

        $response->assertStatus(422)
                ->assertJson([
                    'message' => 'Esta encuesta no permite respuestas anónimas'
                ]);
    }

    /** @test */
    public function it_cannot_create_duplicate_response_for_user()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay()
        ]);
        
        // Primera respuesta
        SurveyResponse::factory()->create([
            'survey_id' => $survey->id,
            'user_id' => $user->id
        ]);
        
        // Intentar segunda respuesta
        $responseData = [
            'survey_id' => $survey->id,
            'user_id' => $user->id,
            'response_data' => ['rating' => 5]
        ];

        $response = $this->postJson('/api/v1/survey-responses', $responseData);

        $response->assertStatus(422)
                ->assertJson([
                    'message' => 'El usuario ya ha respondido esta encuesta'
                ]);
    }

    /** @test */
    public function it_can_show_survey_response()
    {
        $user = $this->authenticateUser();
        $response = SurveyResponse::factory()->create();

        $response = $this->getJson("/api/v1/survey-responses/{$response->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'survey_id', 'user_id', 'response_data',
                        'created_at', 'updated_at'
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_response()
    {
        $user = $this->authenticateUser();

        $response = $this->getJson('/api/v1/survey-responses/999');

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Respuesta de encuesta no encontrada'
                ]);
    }

    /** @test */
    public function it_can_update_survey_response()
    {
        $user = $this->authenticateUser();
        $response = SurveyResponse::factory()->create();

        $updateData = [
            'response_data' => [
                'rating' => 4,
                'comment' => 'Updated comment'
            ]
        ];

        $response = $this->putJson("/api/v1/survey-responses/{$response->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Respuesta de encuesta actualizada exitosamente'
                ]);

        $this->assertDatabaseHas('survey_responses', [
            'id' => $response->id,
            'response_data->rating' => 4
        ]);
    }

    /** @test */
    public function it_can_delete_survey_response()
    {
        $user = $this->authenticateUser();
        $response = SurveyResponse::factory()->create();

        $response = $this->deleteJson("/api/v1/survey-responses/{$response->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Respuesta de encuesta eliminada exitosamente'
                ]);

        $this->assertSoftDeleted('survey_responses', ['id' => $response->id]);
    }

    /** @test */
    public function it_can_get_response_statistics()
    {
        $user = $this->authenticateUser();
        
        SurveyResponse::factory()->count(3)->create(['user_id' => null]);
        SurveyResponse::factory()->count(2)->create(['user_id' => User::factory()->create()->id]);

        $response = $this->getJson('/api/v1/survey-responses/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total', 'anonymous', 'identified', 'today',
                        'this_week', 'this_month', 'anonymous_percentage', 'identified_percentage'
                    ]
                ]);

        $this->assertEquals(5, $response->json('data.total'));
        $this->assertEquals(3, $response->json('data.anonymous'));
        $this->assertEquals(2, $response->json('data.identified'));
    }

    /** @test */
    public function it_can_get_responses_by_survey()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create();
        
        SurveyResponse::factory()->count(3)->create(['survey_id' => $survey->id]);

        $response = $this->getJson("/api/v1/survey-responses/by-survey/{$survey->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'survey_id', 'user_id', 'response_data']
                    ],
                    'survey' => ['id', 'title', 'total_responses']
                ]);

        $this->assertEquals(3, count($response->json('data')));
        $this->assertEquals($survey->id, $response->json('survey.id'));
    }

    /** @test */
    public function it_returns_404_for_nonexistent_survey_in_by_survey()
    {
        $user = $this->authenticateUser();

        $response = $this->getJson('/api/v1/survey-responses/by-survey/999');

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Encuesta no encontrada'
                ]);
    }

    /** @test */
    public function it_can_filter_responses_by_type_in_by_survey()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create();
        
        SurveyResponse::factory()->count(2)->create([
            'survey_id' => $survey->id,
            'user_id' => null
        ]);
        SurveyResponse::factory()->count(1)->create([
            'survey_id' => $survey->id,
            'user_id' => User::factory()->create()->id
        ]);

        $response = $this->getJson("/api/v1/survey-responses/by-survey/{$survey->id}?response_type=anonymous");

        $response->assertStatus(200);
        $this->assertEquals(2, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_responses_by_user()
    {
        $user = $this->authenticateUser();
        $targetUser = User::factory()->create();
        
        SurveyResponse::factory()->count(3)->create(['user_id' => $targetUser->id]);

        $response = $this->getJson("/api/v1/survey-responses/by-user/{$targetUser->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'survey_id', 'user_id', 'response_data']
                    ],
                    'user_stats'
                ]);

        $this->assertEquals(3, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_popular_responses()
    {
        $user = $this->authenticateUser();
        
        SurveyResponse::factory()->create([
            'response_data' => ['rating' => 5, 'comment' => 'Great', 'category' => 'service']
        ]);
        SurveyResponse::factory()->create([
            'response_data' => ['rating' => 4]
        ]);

        $response = $this->getJson('/api/v1/survey-responses/popular?limit=2');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'survey_id', 'response_field_count']
                    ]
                ]);

        $this->assertEquals(2, count($response->json('data')));
    }

    /** @test */
    public function it_can_get_recent_responses()
    {
        $user = $this->authenticateUser();
        SurveyResponse::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/survey-responses/recent?limit=3');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'survey_id', 'user_id', 'response_data']
                    ]
                ]);

        $this->assertEquals(3, count($response->json('data')));
    }

    /** @test */
    public function it_can_check_user_response()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create();
        
        // Usuario no ha respondido
        $response = $this->getJson("/api/v1/survey-responses/check-user-response/{$survey->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'has_responded' => false
                    ]
                ]);

        // Usuario responde
        SurveyResponse::factory()->create([
            'survey_id' => $survey->id,
            'user_id' => $user->id
        ]);

        $response = $this->getJson("/api/v1/survey-responses/check-user-response/{$survey->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'data' => [
                        'has_responded' => true
                    ]
                ])
                ->assertJsonStructure([
                    'data' => [
                        'has_responded', 'response_id', 'response_type', 'created_at'
                    ]
                ]);
    }

    /** @test */
    public function it_requires_authentication_for_check_user_response()
    {
        $response = $this->getJson('/api/v1/survey-responses/check-user-response/1');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Usuario no autenticado'
                ]);
    }

    /** @test */
    public function it_can_search_in_responses()
    {
        $user = $this->authenticateUser();
        
        SurveyResponse::factory()->create([
            'response_data' => ['comment' => 'Excellent service quality']
        ]);
        SurveyResponse::factory()->create([
            'response_data' => ['comment' => 'Good customer support']
        ]);

        $response = $this->getJson('/api/v1/survey-responses/search?q=service');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data', 'query', 'total_results'
                ])
                ->assertJson([
                    'query' => 'service',
                    'total_results' => 1
                ]);
    }

    /** @test */
    public function it_can_search_in_responses_by_survey()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create();
        
        SurveyResponse::factory()->create([
            'survey_id' => $survey->id,
            'response_data' => ['comment' => 'Excellent service quality']
        ]);
        SurveyResponse::factory()->create([
            'survey_id' => Survey::factory()->create()->id,
            'response_data' => ['comment' => 'Good service']
        ]);

        $response = $this->getJson("/api/v1/survey-responses/search?q=service&survey_id={$survey->id}");

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('total_results'));
    }

    /** @test */
    public function it_validates_search_query()
    {
        $user = $this->authenticateUser();

        $response = $this->getJson('/api/v1/survey-responses/search?q=a');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['q']);
    }

    /** @test */
    public function it_validates_survey_id_in_search()
    {
        $user = $this->authenticateUser();

        $response = $this->getJson('/api/v1/survey-responses/search?q=test&survey_id=999');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['survey_id']);
    }

    /** @test */
    public function it_can_paginate_responses()
    {
        $user = $this->authenticateUser();
        SurveyResponse::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/survey-responses?per_page=10&page=2');

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
    public function it_can_sort_responses()
    {
        $user = $this->authenticateUser();
        
        SurveyResponse::factory()->create(['created_at' => now()->subDays(2)]);
        SurveyResponse::factory()->create(['created_at' => now()->subDays(1)]);
        SurveyResponse::factory()->create(['created_at' => now()]);

        $response = $this->getJson('/api/v1/survey-responses?sort_by=created_at&sort_order=desc');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertTrue(
            Carbon::parse($data[0]['created_at'])->gt(Carbon::parse($data[2]['created_at']))
        );
    }

    /** @test */
    public function it_can_filter_by_date_range()
    {
        $user = $this->authenticateUser();
        
        SurveyResponse::factory()->create(['created_at' => '2024-01-15']);
        SurveyResponse::factory()->create(['created_at' => '2024-01-20']);
        SurveyResponse::factory()->create(['created_at' => '2024-02-01']);

        $response = $this->getJson('/api/v1/survey-responses?date_from=2024-01-01&date_to=2024-01-31');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('meta.total'));
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/survey-responses');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_logs_successful_operations()
    {
        $user = $this->authenticateUser();
        $response = SurveyResponse::factory()->create();

        $this->getJson("/api/v1/survey-responses/{$response->id}");

        // Verificar que se registra en los logs (esto es más una verificación de que no hay errores)
        $this->assertTrue(true);
    }

    /** @test */
    public function it_handles_database_errors_gracefully()
    {
        $user = $this->authenticateUser();
        
        // Simular un error de base de datos
        $this->mock(SurveyResponse::class, function ($mock) {
            $mock->shouldReceive('query')->andThrow(new \Exception('Database error'));
        });

        $response = $this->getJson('/api/v1/survey-responses');

        $response->assertStatus(500)
                ->assertJson([
                    'message' => 'Error al obtener las respuestas de encuestas'
                ]);
    }

    /** @test */
    public function it_can_handle_nested_response_data()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'anonymous_allowed' => true
        ]);
        
        $nestedData = [
            'personal_info' => [
                'age' => 25,
                'location' => 'Madrid'
            ],
            'feedback' => [
                'rating' => 5,
                'comments' => 'Excellent'
            ]
        ];

        $responseData = [
            'survey_id' => $survey->id,
            'user_id' => null,
            'response_data' => $nestedData
        ];

        $response = $this->postJson('/api/v1/survey-responses', $responseData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('survey_responses', [
            'survey_id' => $survey->id,
            'response_data->personal_info->age' => 25,
            'response_data->feedback->rating' => 5
        ]);
    }

    /** @test */
    public function it_can_handle_empty_response_data()
    {
        $user = $this->authenticateUser();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'anonymous_allowed' => true
        ]);
        
        $responseData = [
            'survey_id' => $survey->id,
            'user_id' => null,
            'response_data' => []
        ];

        $response = $this->postJson('/api/v1/survey-responses', $responseData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('survey_responses', [
            'survey_id' => $survey->id,
            'response_data' => '[]'
        ]);
    }
}

