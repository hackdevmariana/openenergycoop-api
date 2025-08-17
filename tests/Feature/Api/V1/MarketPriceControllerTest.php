<?php

namespace Tests\Feature\Api\V1;

use App\Models\MarketPrice;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class MarketPriceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function guest_can_access_public_market_prices()
    {
        MarketPrice::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/market-prices');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id', 'market_name', 'commodity_type', 'price',
                            'currency', 'unit', 'price_datetime'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function guest_can_get_latest_prices()
    {
        MarketPrice::factory()->electricity()->count(3)->create();
        MarketPrice::factory()->carbonCredits()->count(2)->create();

        $response = $this->getJson('/api/v1/market-prices/latest');

        $response->assertOk();
        $this->assertEquals(5, count($response->json('data')));
    }

    /** @test */
    public function guest_can_filter_latest_prices_by_commodity()
    {
        MarketPrice::factory()->electricity()->count(3)->create();
        MarketPrice::factory()->carbonCredits()->count(2)->create();

        $response = $this->getJson('/api/v1/market-prices/latest?commodity_type=electricity');

        $response->assertOk();
        $this->assertEquals(3, count($response->json('data')));
    }

    /** @test */
    public function guest_can_get_available_markets()
    {
        MarketPrice::factory()->create(['market_name' => 'OMIE', 'country' => 'España']);
        MarketPrice::factory()->create(['market_name' => 'EPEX SPOT', 'country' => 'Francia']);

        $response = $this->getJson('/api/v1/market-prices/markets');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        '*' => [
                            'market_name', 'country', 'commodity_type'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function guest_can_get_market_analytics()
    {
        MarketPrice::factory()->electricity()->count(10)->create([
            'price_datetime' => now()->startOfDay()
        ]);

        $response = $this->getJson('/api/v1/market-prices/analytics?commodity_type=electricity&period=day');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'avg_price', 'min_price', 'max_price',
                    'total_volume', 'markets_count', 'price_trends'
                ]
            ]);
    }

    /** @test */
    public function user_can_create_market_price()
    {
        Sanctum::actingAs($this->user);

        $priceData = [
            'market_name' => 'OMIE',
            'country' => 'España',
            'commodity_type' => 'electricity',
            'product_name' => 'Base Load',
            'price_datetime' => now()->format('Y-m-d H:i:s'),
            'period_type' => 'hourly',
            'delivery_start_date' => now()->addHour()->format('Y-m-d H:i:s'),
            'delivery_end_date' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'delivery_period' => 'next_day',
            'price' => 85.50,
            'currency' => 'EUR',
            'unit' => 'EUR/MWh',
            'data_source' => 'Official Exchange',
        ];

        $response = $this->postJson('/api/v1/market-prices', $priceData);

        $response->assertCreated()
            ->assertJsonFragment([
                'market_name' => 'OMIE',
                'price' => 85.50,
                'commodity_type' => 'electricity'
            ]);
    }

    /** @test */
    public function user_can_update_market_price()
    {
        Sanctum::actingAs($this->user);
        
        $price = MarketPrice::factory()->create();

        $response = $this->putJson("/api/v1/market-prices/{$price->id}", [
            'price' => 95.25,
            'volume' => 1500.0,
            'market_status' => 'open'
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'price' => 95.25,
                'volume' => 1500.0,
                'market_status' => 'open'
            ]);
    }

    /** @test */
    public function user_can_filter_prices_by_market()
    {
        Sanctum::actingAs($this->user);
        
        MarketPrice::factory()->count(3)->create(['market_name' => 'OMIE']);
        MarketPrice::factory()->count(2)->create(['market_name' => 'EPEX SPOT']);

        $response = $this->getJson('/api/v1/market-prices?market_name=OMIE');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.meta.total'));
    }

    /** @test */
    public function user_can_filter_prices_by_date_range()
    {
        Sanctum::actingAs($this->user);
        
        MarketPrice::factory()->create(['price_datetime' => '2024-01-15 10:00:00']);
        MarketPrice::factory()->create(['price_datetime' => '2024-01-20 14:00:00']);
        MarketPrice::factory()->create(['price_datetime' => '2024-01-25 16:00:00']);

        $response = $this->getJson('/api/v1/market-prices?date_from=2024-01-18&date_to=2024-01-22');

        $response->assertOk();
        $this->assertEquals(1, $response->json('data.meta.total'));
    }

    /** @test */
    public function guest_cannot_create_market_price()
    {
        $response = $this->postJson('/api/v1/market-prices', [
            'market_name' => 'Test Market',
            'commodity_type' => 'electricity',
            'price' => 100.00
        ]);

        $response->assertUnauthorized();
    }

    /** @test */
    public function market_price_creation_requires_valid_data()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/market-prices', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'market_name', 'country', 'commodity_type', 'product_name',
                'price_datetime', 'period_type', 'delivery_start_date',
                'delivery_end_date', 'delivery_period', 'price',
                'currency', 'unit', 'data_source'
            ]);
    }

    /** @test */
    public function delivery_end_date_must_be_after_start_date()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/market-prices', [
            'market_name' => 'Test Market',
            'country' => 'España',
            'commodity_type' => 'electricity',
            'product_name' => 'Test Product',
            'price_datetime' => now()->format('Y-m-d H:i:s'),
            'period_type' => 'hourly',
            'delivery_start_date' => '2024-12-31 23:59:59',
            'delivery_end_date' => '2024-01-01 00:00:00',
            'delivery_period' => 'spot',
            'price' => 100.00,
            'currency' => 'EUR',
            'unit' => 'EUR/MWh',
            'data_source' => 'Test Source',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['delivery_end_date']);
    }

    /** @test */
    public function user_can_view_specific_price()
    {
        Sanctum::actingAs($this->user);
        
        $price = MarketPrice::factory()->create();

        $response = $this->getJson("/api/v1/market-prices/{$price->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $price->id,
                'market_name' => $price->market_name,
                'price' => $price->price
            ]);
    }

    /** @test */
    public function guest_can_view_specific_price()
    {
        $price = MarketPrice::factory()->create();

        $response = $this->getJson("/api/v1/market-prices/{$price->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $price->id,
                'market_name' => $price->market_name
            ]);
    }

    /** @test */
    public function user_can_delete_price()
    {
        Sanctum::actingAs($this->user);
        
        $price = MarketPrice::factory()->create();

        $response = $this->deleteJson("/api/v1/market-prices/{$price->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('market_prices', ['id' => $price->id]);
    }
}