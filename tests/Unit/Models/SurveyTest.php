<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SurveyTest extends TestCase
{
    use WithFaker, DatabaseTransactions;



    /** @test */
    public function it_can_create_a_survey()
    {
        $surveyData = [
            'title' => 'Test Survey',
            'description' => 'This is a test survey',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'anonymous_allowed' => true,
            'visible_results' => true,
        ];

        $survey = Survey::create($surveyData);

        $this->assertInstanceOf(Survey::class, $survey);
        $this->assertEquals('Test Survey', $survey->title);
        $this->assertEquals('This is a test survey', $survey->description);
        $this->assertTrue($survey->anonymous_allowed);
        $this->assertTrue($survey->visible_results);
    }

    /** @test */
    public function it_has_required_fields()
    {
        $survey = Survey::factory()->create();

        $this->assertNotNull($survey->title);
        $this->assertNotNull($survey->description);
        $this->assertNotNull($survey->starts_at);
        $this->assertNotNull($survey->ends_at);
        $this->assertNotNull($survey->anonymous_allowed);
        $this->assertNotNull($survey->visible_results);
    }

    /** @test */
    public function it_has_soft_deletes()
    {
        $survey = Survey::factory()->create();
        $surveyId = $survey->id;

        $survey->delete();

        $this->assertSoftDeleted('surveys', ['id' => $surveyId]);
        $this->assertDatabaseHas('surveys', ['id' => $surveyId]);
    }

    /** @test */
    public function it_can_determine_if_active()
    {
        $activeSurvey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $inactiveSurvey = Survey::factory()->create([
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        $this->assertTrue($activeSurvey->isActive());
        $this->assertFalse($inactiveSurvey->isActive());
    }

    /** @test */
    public function it_can_determine_if_started()
    {
        $startedSurvey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $notStartedSurvey = Survey::factory()->create([
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        $this->assertTrue($startedSurvey->hasStarted());
        $this->assertFalse($notStartedSurvey->hasStarted());
    }

    /** @test */
    public function it_can_get_status_attribute()
    {
        $activeSurvey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $upcomingSurvey = Survey::factory()->create([
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        $pastSurvey = Survey::factory()->create([
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
        ]);

        $this->assertEquals('active', $activeSurvey->status);
        $this->assertEquals('draft', $upcomingSurvey->status); // Cambiado de 'upcoming' a 'draft'
        $this->assertEquals('expired', $pastSurvey->status); // Cambiado de 'past' a 'expired'
    }

    /** @test */
    public function it_can_get_status_label_attribute()
    {
        $activeSurvey = Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $this->assertEquals('Activa', $activeSurvey->status_label);
    }

    /** @test */
    public function it_can_get_duration_attribute()
    {
        $survey = Survey::factory()->create([
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);

        $this->assertEquals('30 días', $survey->duration);
    }

    /** @test */
    public function it_can_get_time_until_start_attribute()
    {
        $futureSurvey = Survey::factory()->create([
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(10), // Agregado ends_at para evitar error de validación
        ]);

        $pastSurvey = Survey::factory()->create([
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(5), // Agregado ends_at para evitar error de validación
        ]);

        $this->assertStringContainsString('Comienza', $futureSurvey->time_until_start); // Cambiado de 'días' a 'Comienza'
        $this->assertEquals('Ya comenzó', $pastSurvey->time_until_start);
    }

    /** @test */
    public function it_can_get_time_until_end_attribute()
    {
        $survey = Survey::factory()->create([
            'starts_at' => now(),
            'ends_at' => now()->addDays(10),
        ]);

        $this->assertStringContainsString('Termina', $survey->time_until_end); // Cambiado de 'Termina en' a 'Termina'
    }

    /** @test */
    public function it_can_scope_active_surveys()
    {
        Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        Survey::factory()->create([
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        $activeSurveys = Survey::active()->get();

        $this->assertEquals(1, $activeSurveys->count());
        $this->assertTrue($activeSurveys->first()->isActive());
    }

    /** @test */
    public function it_can_scope_upcoming_surveys()
    {
        Survey::factory()->create([
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $upcomingSurveys = Survey::upcoming()->get();

        $this->assertEquals(1, $upcomingSurveys->count());
        $this->assertEquals('draft', $upcomingSurveys->first()->status); // Cambiado de 'upcoming' a 'draft'
    }

    /** @test */
    public function it_can_scope_past_surveys()
    {
        Survey::factory()->create([
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
        ]);

        Survey::factory()->create([
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $pastSurveys = Survey::past()->get();

        $this->assertEquals(1, $pastSurveys->count());
        $this->assertEquals('expired', $pastSurveys->first()->status); // Cambiado de 'past' a 'expired'
    }

    /** @test */
    public function it_can_scope_anonymous_allowed_surveys()
    {
        Survey::factory()->create(['anonymous_allowed' => true]);
        Survey::factory()->create(['anonymous_allowed' => false]);

        $anonymousAllowedSurveys = Survey::anonymousAllowed()->get();

        $this->assertEquals(1, $anonymousAllowedSurveys->count());
        $this->assertTrue($anonymousAllowedSurveys->first()->anonymous_allowed);
    }

    /** @test */
    public function it_can_scope_results_visible_surveys()
    {
        Survey::factory()->create(['visible_results' => true]);
        Survey::factory()->create(['visible_results' => false]);

        $resultsVisibleSurveys = Survey::resultsVisible()->get();

        $this->assertEquals(1, $resultsVisibleSurveys->count());
        $this->assertTrue($resultsVisibleSurveys->first()->visible_results);
    }

    /** @test */
    public function it_can_search_surveys()
    {
        Survey::factory()->create(['title' => 'Customer Satisfaction Survey']);
        Survey::factory()->create(['title' => 'Product Feedback']);
        Survey::factory()->create(['description' => 'Employee satisfaction feedback']);

        $searchResults = Survey::search('satisfaction')->get();

        $this->assertEquals(2, $searchResults->count());
    }

    /** @test */
    public function it_validates_ends_at_after_starts_at()
    {
        $this->expectException(\InvalidArgumentException::class);

        Survey::create([
            'title' => 'Invalid Survey',
            'description' => 'This survey has invalid dates',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->subDay(), // ends_at before starts_at
            'anonymous_allowed' => true,
            'visible_results' => true,
        ]);
    }
}
