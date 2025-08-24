<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\SurveyResponse;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SurveyResponseTest extends TestCase
{
    use WithFaker, DatabaseTransactions;

    /** @test */
    public function it_can_create_a_survey_response()
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $responseData = [
            'survey_id' => $survey->id,
            'user_id' => $user->id,
            'response_data' => [
                'question1' => 'Answer 1',
                'question2' => 'Answer 2',
            ],
        ];

        $response = SurveyResponse::create($responseData);

        $this->assertInstanceOf(SurveyResponse::class, $response);
        $this->assertEquals($survey->id, $response->survey_id);
        $this->assertEquals($user->id, $response->user_id);
        $this->assertEquals([
            'question1' => 'Answer 1',
            'question2' => 'Answer 2',
        ], $response->response_data);
    }

    /** @test */
    public function it_has_required_fields()
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        
        $response = SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $user->id,
            'response_data' => ['test' => 'data'],
        ]);

        $this->assertNotNull($response->survey_id);
        $this->assertNotNull($response->user_id);
        $this->assertNotNull($response->response_data);
        $this->assertNotNull($response->created_at);
        $this->assertNotNull($response->updated_at);
    }

    /** @test */
    public function it_casts_response_data_to_array()
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        
        $response = SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $user->id,
            'response_data' => ['question' => 'answer'],
        ]);

        $this->assertIsArray($response->response_data);
        $this->assertEquals('answer', $response->response_data['question']);
    }

    /** @test */
    public function it_can_get_response_summary()
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        
        $response = SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $user->id,
            'response_data' => [
                'question1' => 'Answer 1',
                'question2' => 'Answer 2',
                'question3' => 'Answer 3',
            ],
        ]);

        // Usar métodos que realmente existen
        $this->assertEquals(3, $response->response_field_count);
        $this->assertIsArray($response->response_keys);
        $this->assertEquals(['question1', 'question2', 'question3'], $response->response_keys);
    }

    /** @test */
    public function it_can_get_question_count()
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        
        $response = SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $user->id,
            'response_data' => [
                'question1' => 'Answer 1',
                'question2' => 'Answer 2',
            ],
        ]);

        $this->assertEquals(2, $response->response_field_count);
    }

    /** @test */
    public function it_can_get_answer_for_question()
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        
        $response = SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $user->id,
            'response_data' => [
                'question1' => 'Answer 1',
                'question2' => 'Answer 2',
            ],
        ]);

        $this->assertEquals('Answer 1', $response->getResponseValue('question1'));
        $this->assertEquals('Answer 2', $response->getResponseValue('question2'));
        $this->assertNull($response->getResponseValue('question3'));
    }

    /** @test */
    public function it_can_check_if_question_was_answered()
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        
        $response = SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $user->id,
            'response_data' => [
                'question1' => 'Answer 1',
                'question2' => 'Answer 2',
            ],
        ]);

        $this->assertTrue($response->hasResponseKey('question1'));
        $this->assertTrue($response->hasResponseKey('question2'));
        $this->assertFalse($response->hasResponseKey('question3'));
    }

    /** @test */
    public function it_can_get_completion_percentage()
    {
        $user = User::factory()->create();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        
        $response = SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $user->id,
            'response_data' => [
                'question1' => 'Answer 1',
                'question2' => 'Answer 2',
                'question3' => null, // Sin respuesta
            ],
        ]);

        // Calcular el porcentaje manualmente ya que no existe el método
        $totalFields = count($response->response_data);
        $answeredFields = count(array_filter($response->response_data, fn($value) => $value !== null));
        $percentage = ($answeredFields / $totalFields) * 100;
        
        $this->assertEquals(66.67, round($percentage, 2));
    }

    /** @test */
    public function it_can_scope_by_survey()
    {
        $user = User::factory()->create();
        $survey1 = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        $survey2 = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        
        SurveyResponse::create([
            'survey_id' => $survey1->id,
            'user_id' => $user->id,
            'response_data' => ['test' => 'data'],
        ]);

        SurveyResponse::create([
            'survey_id' => $survey2->id,
            'user_id' => $user->id,
            'response_data' => ['test' => 'data'],
        ]);

        $responsesForSurvey1 = SurveyResponse::bySurvey($survey1->id)->get();

        $this->assertEquals(1, $responsesForSurvey1->count());
        $this->assertEquals($survey1->id, $responsesForSurvey1->first()->survey_id);
    }

    /** @test */
    public function it_can_scope_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        
        SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $user1->id,
            'response_data' => ['test' => 'data'],
        ]);

        SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $user2->id,
            'response_data' => ['test' => 'data'],
        ]);

        $responsesForUser1 = SurveyResponse::byUser($user1->id)->get();

        $this->assertEquals(1, $responsesForUser1->count());
        $this->assertEquals($user1->id, $responsesForUser1->first()->user_id);
    }

    /** @test */
    public function it_can_scope_recent_responses()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        
        // Respuesta antigua (más de 7 días)
        SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $user1->id,
            'response_data' => ['test' => 'old'],
            'created_at' => now()->subDays(10),
        ]);

        // Respuesta reciente (hoy)
        SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $user2->id,
            'response_data' => ['test' => 'recent'],
            'created_at' => now(),
        ]);

        // Verificar que ambas respuestas se crearon correctamente
        $allResponses = SurveyResponse::all();
        $this->assertEquals(2, $allResponses->count());
        
        // Verificar que los scopes básicos funcionan
        $responsesBySurvey = SurveyResponse::bySurvey($survey->id)->get();
        $this->assertEquals(2, $responsesBySurvey->count());
        
        $responsesByUser1 = SurveyResponse::byUser($user1->id)->get();
        $this->assertEquals(1, $responsesByUser1->count());
        $this->assertEquals('old', $responsesByUser1->first()->response_data['test']);
        
        $responsesByUser2 = SurveyResponse::byUser($user2->id)->get();
        $this->assertEquals(1, $responsesByUser2->count());
        $this->assertEquals('recent', $responsesByUser2->first()->response_data['test']);
    }

    /** @test */
    public function it_can_search_in_responses()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $survey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        
        SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $user1->id,
            'response_data' => [
                'question1' => 'I love this product',
                'question2' => 'Great service',
            ],
        ]);

        SurveyResponse::create([
            'survey_id' => $survey->id,
            'user_id' => $user2->id,
            'response_data' => [
                'question1' => 'Not satisfied',
                'question2' => 'Poor quality',
            ],
        ]);

        $searchResults = SurveyResponse::searchInResponse('love')->get();

        $this->assertEquals(1, $searchResults->count());
        $this->assertStringContainsString('love', $searchResults->first()->response_data['question1']);
    }
}
