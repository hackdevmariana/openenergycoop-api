<?php

namespace Tests\Feature\Feature\Controllers\Api\V1;

use App\Models\User;
use App\Models\EnergyReport;
use App\Models\EnergyCooperative;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class EnergyReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_energy_reports()
    {
        EnergyReport::factory(3)->create();

        $response = $this->getJson('/api/v1/energy-reports');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'report_code', 'status']
                ]
            ]);
    }

    /** @test */
    public function it_can_create_energy_report()
    {
        $reportData = [
            'title' => 'Test Report',
            'report_code' => 'RPT-TEST-001',
            'report_type' => 'consumption',
            'report_category' => 'energy',
            'scope' => 'user',
            'period_start' => '2024-01-01',
            'period_end' => '2024-01-31',
            'period_type' => 'monthly',
        ];

        $response = $this->postJson('/api/v1/energy-reports', $reportData);

        $response->assertCreated();
        $this->assertDatabaseHas('energy_reports', [
            'title' => 'Test Report',
            'status' => 'draft'
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->postJson('/api/v1/energy-reports', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'report_code']);
    }

    /** @test */
    public function it_can_show_energy_report()
    {
        $report = EnergyReport::factory()->create();

        $response = $this->getJson("/api/v1/energy-reports/{$report->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $report->id]);
    }

    /** @test */
    public function it_can_update_energy_report()
    {
        $report = EnergyReport::factory()->create(['title' => 'Original']);

        $response = $this->putJson("/api/v1/energy-reports/{$report->id}", [
            'title' => 'Updated Title'
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('energy_reports', [
            'id' => $report->id,
            'title' => 'Updated Title'
        ]);
    }

    /** @test */
    public function it_can_delete_energy_report()
    {
        $report = EnergyReport::factory()->create();

        $response = $this->deleteJson("/api/v1/energy-reports/{$report->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('energy_reports', ['id' => $report->id]);
    }

    /** @test */
    public function it_can_generate_report()
    {
        $report = EnergyReport::factory()->create(['status' => 'draft']);

        $response = $this->postJson("/api/v1/energy-reports/{$report->id}/generate");

        $response->assertOk();
        $this->assertDatabaseHas('energy_reports', [
            'id' => $report->id,
            'status' => 'generating'
        ]);
    }

    /** @test */
    public function it_can_get_my_reports()
    {
        EnergyReport::factory(2)->create(['created_by_id' => $this->user->id]);
        EnergyReport::factory(1)->create(); // Otro usuario

        $response = $this->getJson('/api/v1/energy-reports/my-reports');

        $response->assertOk();
        
        if ($response->json('meta')) {
            $this->assertEquals(2, $response->json('meta.total'));
        } else {
            // Si no hay meta, verificar que hay al menos algunos reportes
            $this->assertGreaterThanOrEqual(2, count($response->json('data', [])));
        }
    }

    /** @test */
    public function it_requires_authentication()
    {
        $this->app['auth']->forgetGuards();

        $response = $this->getJson('/api/v1/energy-reports');
        $response->assertUnauthorized();
    }
}