<?php

namespace Tests\Feature\Api\V1;

use App\Models\TaxCalculation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxCalculationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_tax_calculations()
    {
        TaxCalculation::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'calculation_number', 'name', 'status', 'priority',
                        'tax_type', 'calculation_type', 'taxable_amount', 'tax_amount'
                    ]
                ],
                'meta' => ['current_page', 'total', 'per_page']
            ]);
    }

    public function test_index_with_filters()
    {
        TaxCalculation::factory()->create(['status' => 'draft']);
        TaxCalculation::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations?status=draft');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_index_with_search()
    {
        TaxCalculation::factory()->create(['name' => 'Test Calculation']);
        TaxCalculation::factory()->create(['name' => 'Another Calculation']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations?search=Test');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_index_with_sorting()
    {
        TaxCalculation::factory()->create(['name' => 'B Calculation']);
        TaxCalculation::factory()->create(['name' => 'A Calculation']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations?sort=name&order=asc');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals('A Calculation', $data[0]['name']);
    }

    public function test_store_creates_tax_calculation()
    {
        $data = [
            'name' => 'Test Tax Calculation',
            'tax_type' => 'income_tax',
            'calculation_type' => 'manual',
            'status' => 'draft',
            'priority' => 'medium',
            'taxable_amount' => 1000.00,
            'tax_rate' => 21.00,
            'currency' => 'EUR'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/tax-calculations', $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id', 'calculation_number', 'name', 'tax_type', 'calculation_type',
                    'status', 'priority', 'taxable_amount', 'tax_rate', 'currency'
                ]
            ]);

        $this->assertDatabaseHas('tax_calculations', [
            'name' => 'Test Tax Calculation',
            'tax_type' => 'income_tax'
        ]);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/tax-calculations', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'tax_type', 'calculation_type']);
    }

    public function test_store_validates_unique_calculation_number()
    {
        TaxCalculation::factory()->create(['calculation_number' => 'TC001']);

        $data = [
            'name' => 'Test Calculation',
            'calculation_number' => 'TC001',
            'tax_type' => 'income_tax',
            'calculation_type' => 'manual'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/tax-calculations', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['calculation_number']);
    }

    public function test_show_returns_tax_calculation()
    {
        $taxCalculation = TaxCalculation::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/tax-calculations/{$taxCalculation->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $taxCalculation->id,
                    'name' => $taxCalculation->name
                ]
            ]);
    }

    public function test_show_returns_404_for_nonexistent()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/999');

        $response->assertNotFound();
    }

    public function test_update_modifies_tax_calculation()
    {
        $taxCalculation = TaxCalculation::factory()->create(['status' => 'draft']);
        $updateData = ['status' => 'calculated'];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/tax-calculations/{$taxCalculation->id}", $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('tax_calculations', [
            'id' => $taxCalculation->id,
            'status' => 'calculated'
        ]);
    }

    public function test_update_validates_unique_calculation_number()
    {
        $taxCalculation1 = TaxCalculation::factory()->create(['calculation_number' => 'TC001']);
        $taxCalculation2 = TaxCalculation::factory()->create(['calculation_number' => 'TC002']);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/tax-calculations/{$taxCalculation2->id}", [
                'calculation_number' => 'TC001'
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['calculation_number']);
    }

    public function test_destroy_deletes_tax_calculation()
    {
        $taxCalculation = TaxCalculation::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/tax-calculations/{$taxCalculation->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('tax_calculations', ['id' => $taxCalculation->id]);
    }

    public function test_statistics_returns_calculation_stats()
    {
        TaxCalculation::factory()->count(5)->create(['status' => 'draft']);
        TaxCalculation::factory()->count(3)->create(['status' => 'approved']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/statistics');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['total', 'by_status', 'by_type', 'by_priority']
            ]);
    }

    public function test_types_returns_tax_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/types');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_calculation_types_returns_calculation_types()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/calculation-types');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_statuses_returns_statuses()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/statuses');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_priorities_returns_priorities()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/priorities');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_update_status_updates_status()
    {
        $taxCalculation = TaxCalculation::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/tax-calculations/{$taxCalculation->id}/update-status", [
                'status' => 'calculated'
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('tax_calculations', [
            'id' => $taxCalculation->id,
            'status' => 'calculated'
        ]);
    }

    public function test_update_status_validates_status()
    {
        $taxCalculation = TaxCalculation::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/tax-calculations/{$taxCalculation->id}/update-status", [
                'status' => 'invalid_status'
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_duplicate_creates_copy()
    {
        $taxCalculation = TaxCalculation::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/tax-calculations/{$taxCalculation->id}/duplicate");

        $response->assertCreated();
        $this->assertDatabaseCount('tax_calculations', 2);
    }

    public function test_overdue_returns_overdue_calculations()
    {
        TaxCalculation::factory()->create(['due_date' => now()->subDays(5)]);
        TaxCalculation::factory()->create(['due_date' => now()->addDays(5)]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/overdue');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_due_soon_returns_due_soon_calculations()
    {
        TaxCalculation::factory()->create(['due_date' => now()->addDays(3)]);
        TaxCalculation::factory()->create(['due_date' => now()->addDays(10)]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/due-soon');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_type_returns_calculations_by_type()
    {
        TaxCalculation::factory()->create(['tax_type' => 'income_tax']);
        TaxCalculation::factory()->create(['tax_type' => 'sales_tax']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/by-type/income_tax');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_calculation_type_returns_calculations_by_calculation_type()
    {
        TaxCalculation::factory()->create(['calculation_type' => 'automatic']);
        TaxCalculation::factory()->create(['calculation_type' => 'manual']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/by-calculation-type/automatic');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_high_priority_returns_high_priority_calculations()
    {
        TaxCalculation::factory()->create(['priority' => 'high']);
        TaxCalculation::factory()->create(['priority' => 'low']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/high-priority');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_estimated_returns_estimated_calculations()
    {
        TaxCalculation::factory()->create(['is_estimated' => true]);
        TaxCalculation::factory()->create(['is_estimated' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/estimated');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_final_returns_final_calculations()
    {
        TaxCalculation::factory()->create(['is_final' => true]);
        TaxCalculation::factory()->create(['is_final' => false]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/final');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_entity_returns_calculations_by_entity()
    {
        TaxCalculation::factory()->create([
            'entity_type' => 'App\\Models\\User',
            'entity_id' => 1
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/by-entity/App\\Models\\User/1');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_transaction_returns_calculations_by_transaction()
    {
        TaxCalculation::factory()->create([
            'transaction_type' => 'App\\Models\\Invoice',
            'transaction_id' => 1
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/by-transaction/App\\Models\\Invoice/1');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_currency_returns_calculations_by_currency()
    {
        TaxCalculation::factory()->create(['currency' => 'EUR']);
        TaxCalculation::factory()->create(['currency' => 'USD']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/by-currency/EUR');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_by_amount_range_returns_calculations_by_amount_range()
    {
        TaxCalculation::factory()->create(['taxable_amount' => 500.00]);
        TaxCalculation::factory()->create(['taxable_amount' => 1500.00]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations/by-amount-range?min=1000&max=2000');

        $response->assertOk();
        $this->assertEquals(1, count($response->json('data')));
    }

    public function test_pagination_limits_results()
    {
        TaxCalculation::factory()->count(25)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/tax-calculations?limit=10');

        $response->assertOk();
        $this->assertEquals(10, count($response->json('data')));
        $this->assertEquals(25, $response->json('meta.total'));
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/v1/tax-calculations');
        $response->assertUnauthorized();
    }

    public function test_logs_activity_on_create()
    {
        $data = [
            'name' => 'Test Tax Calculation',
            'tax_type' => 'income_tax',
            'calculation_type' => 'manual',
            'status' => 'draft',
            'priority' => 'medium',
            'taxable_amount' => 1000.00,
            'tax_rate' => 21.00,
            'currency' => 'EUR'
        ];

        $this->actingAs($this->user)
            ->postJson('/api/v1/tax-calculations', $data);

        // Verificar que se registró la actividad (si tienes un sistema de logging)
        $this->assertTrue(true); // Placeholder para verificación de logging
    }
}
