<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Balance;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Provider;
use App\Models\User;
use App\Models\UserAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BalanceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create();
        
        // Asignar rol de admin al usuario para que tenga permisos completos
        $this->user->assignRole('admin');
        
        // Crear un perfil para el usuario para que tenga una organización asociada
        \App\Models\CustomerProfile::factory()->create([
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
        ]);
    }

    #[Test]
    public function it_can_list_balances()
    {
        Sanctum::actingAs($this->user);
        
        Balance::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/balances');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user',
                    'amount',
                    'transaction_type',
                    'description',
                    'status',
                    'reference_id',
                    'created_at'
                ]
            ]
        ]);
    }

    #[Test]
    public function it_can_get_my_balance()
    {
        Sanctum::actingAs($this->user);
        
        // Crear transacciones para el usuario
        Balance::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000,
            'transaction_type' => 'deposit',
            'status' => 'completed'
        ]);
        
        Balance::factory()->create([
            'user_id' => $this->user->id,
            'amount' => -200,
            'transaction_type' => 'investment',
            'status' => 'completed'
        ]);
        
        Balance::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 50,
            'transaction_type' => 'yield',
            'status' => 'completed'
        ]);

        $response = $this->getJson('/api/v1/balances/my-balance');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_balance',
            'pending_balance',
            'available_balance',
            'currency',
            'recent_transactions',
            'updated_at'
        ]);
        
        $this->assertEquals(850, $response->json('current_balance'));
        $this->assertEquals('EUR', $response->json('currency'));
    }

    #[Test]
    public function it_can_get_transaction_history()
    {
        Sanctum::actingAs($this->user);
        
        Balance::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subDays(15)
        ]);

        $response = $this->getJson('/api/v1/balances/transaction-history?months=1');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'summary' => [
                'total_transactions',
                'total_deposits',
                'total_withdrawals',
                'total_yields',
                'total_investments',
                'net_flow',
                'by_month'
            ],
            'period' => [
                'from',
                'to',
                'months'
            ]
        ]);
        
        $this->assertCount(5, $response->json('data'));
        $this->assertEquals(5, $response->json('summary.total_transactions'));
    }

    #[Test]
    public function it_can_make_a_deposit()
    {
        Sanctum::actingAs($this->user);

        $depositData = [
            'amount' => 500,
            'description' => 'Test deposit',
            'payment_method' => 'bank_transfer'
        ];

        $response = $this->postJson('/api/v1/balances/deposit', $depositData);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'amount' => 500,
            'transaction_type' => 'deposit',
            'status' => 'completed'
        ]);

        $this->assertDatabaseHas('balances', [
            'user_id' => $this->user->id,
            'amount' => 500,
            'transaction_type' => 'deposit'
        ]);
    }

    #[Test]
    public function it_validates_deposit_amount()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/balances/deposit', [
            'amount' => 0
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    #[Test]
    public function it_can_make_a_withdrawal_with_sufficient_funds()
    {
        Sanctum::actingAs($this->user);
        
        // Crear balance suficiente
        Balance::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 1000,
            'transaction_type' => 'deposit',
            'status' => 'completed'
        ]);

        $withdrawalData = [
            'amount' => 300,
            'description' => 'Test withdrawal',
            'withdrawal_method' => 'bank_transfer'
        ];

        $response = $this->postJson('/api/v1/balances/withdraw', $withdrawalData);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'amount' => -300,
            'transaction_type' => 'withdrawal',
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('balances', [
            'user_id' => $this->user->id,
            'amount' => -300,
            'transaction_type' => 'withdrawal'
        ]);
    }

    #[Test]
    public function it_prevents_withdrawal_with_insufficient_funds()
    {
        Sanctum::actingAs($this->user);
        
        // Solo 100 en balance
        Balance::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100,
            'transaction_type' => 'deposit',
            'status' => 'completed'
        ]);

        $withdrawalData = [
            'amount' => 500
        ];

        $response = $this->postJson('/api/v1/balances/withdraw', $withdrawalData);

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => 'Fondos insuficientes'
        ]);
    }

    #[Test]
    public function it_can_register_an_investment()
    {
        Sanctum::actingAs($this->user);
        
        // Crear balance suficiente
        Balance::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 2000,
            'transaction_type' => 'deposit',
            'status' => 'completed'
        ]);

        $provider = Provider::factory()->create();
        $product = Product::factory()->for($provider)->create();
        $userAsset = UserAsset::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id
        ]);

        $investmentData = [
            'amount' => 1000,
            'product_id' => $product->id,
            'user_asset_id' => $userAsset->id,
            'description' => 'Investment in solar panels'
        ];

        $response = $this->postJson('/api/v1/balances/investment', $investmentData);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'amount' => -1000,
            'transaction_type' => 'investment',
            'status' => 'completed'
        ]);

        $this->assertDatabaseHas('balances', [
            'user_id' => $this->user->id,
            'amount' => -1000,
            'transaction_type' => 'investment'
        ]);
    }

    #[Test]
    public function it_can_register_yield()
    {
        Sanctum::actingAs($this->user);

        $provider = Provider::factory()->create();
        $product = Product::factory()->for($provider)->create();
        $userAsset = UserAsset::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id
        ]);

        $yieldData = [
            'amount' => 25.50,
            'user_asset_id' => $userAsset->id,
            'description' => 'Daily yield from solar panels'
        ];

        $response = $this->postJson('/api/v1/balances/yield', $yieldData);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'amount' => 25.50,
            'transaction_type' => 'yield',
            'status' => 'completed'
        ]);

        $this->assertDatabaseHas('balances', [
            'user_id' => $this->user->id,
            'amount' => 25.50,
            'transaction_type' => 'yield'
        ]);
    }

    #[Test]
    public function it_can_get_analytics()
    {
        Sanctum::actingAs($this->user);
        
        // Crear transacciones variadas de los últimos 3 meses
        Balance::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 2000,
            'transaction_type' => 'deposit',
            'created_at' => now()->subMonths(2)
        ]);
        
        Balance::factory()->create([
            'user_id' => $this->user->id,
            'amount' => -1000,
            'transaction_type' => 'investment',
            'created_at' => now()->subMonths(1)
        ]);
        
        Balance::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'amount' => 25,
            'transaction_type' => 'yield',
            'created_at' => now()->subDays(rand(1, 60))
        ]);

        $response = $this->getJson('/api/v1/balances/analytics?period=3m');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'period',
            'income_vs_expenses' => [
                'total_income',
                'total_expenses',
                'net_flow',
                'income_sources',
                'expense_categories'
            ],
            'yield_performance' => [
                'total_yield',
                'average_monthly_yield',
                'yield_growth_rate',
                'yield_consistency'
            ],
            'monthly_trends',
            'performance_score',
            'recommendations'
        ]);
        
        $this->assertEquals('3m', $response->json('period'));
        $this->assertIsArray($response->json('monthly_trends'));
        $this->assertIsArray($response->json('recommendations'));
    }

    #[Test]
    public function it_filters_balances_by_user()
    {
        Sanctum::actingAs($this->user);
        
        $otherUser = User::factory()->create();
        
        Balance::factory()->count(3)->create(['user_id' => $this->user->id]);
        Balance::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/v1/balances?user_id={$this->user->id}");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        
        foreach ($response->json('data') as $balance) {
            $this->assertEquals($this->user->id, $balance['user']['id']);
        }
    }

    #[Test]
    public function it_filters_balances_by_transaction_type()
    {
        Sanctum::actingAs($this->user);
        
        Balance::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'transaction_type' => 'deposit'
        ]);
        
        Balance::factory()->create([
            'user_id' => $this->user->id,
            'transaction_type' => 'yield'
        ]);

        $response = $this->getJson('/api/v1/balances?type=deposit');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $balance) {
            $this->assertEquals('deposit', $balance['transaction_type']);
        }
    }

    #[Test]
    public function it_filters_balances_by_date_range()
    {
        Sanctum::actingAs($this->user);
        
        Balance::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => '2024-01-15'
        ]);
        
        Balance::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => '2024-02-15'
        ]);
        
        Balance::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => '2024-03-15'
        ]);

        $response = $this->getJson('/api/v1/balances?date_from=2024-02-01&date_to=2024-02-28');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    #[Test]
    public function it_requires_authentication_for_balance_operations()
    {
        // Test endpoints que requieren autenticación
        $this->getJson('/api/v1/balances')->assertStatus(401);
        $this->getJson('/api/v1/balances/my-balance')->assertStatus(401);
        $this->getJson('/api/v1/balances/transaction-history')->assertStatus(401);
        $this->getJson('/api/v1/balances/analytics')->assertStatus(401);
        $this->postJson('/api/v1/balances/deposit', ['amount' => 100])->assertStatus(401);
        $this->postJson('/api/v1/balances/withdraw', ['amount' => 100])->assertStatus(401);
    }

    #[Test]
    public function it_can_show_a_specific_balance()
    {
        Sanctum::actingAs($this->user);
        
        $balance = Balance::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/v1/balances/{$balance->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $balance->id,
            'amount' => $balance->amount,
            'transaction_type' => $balance->transaction_type
        ]);
    }

    #[Test]
    public function it_paginates_balance_results()
    {
        Sanctum::actingAs($this->user);
        
        Balance::factory()->count(25)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/balances?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $response->assertJsonStructure([
            'data',
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'last_page', 'per_page', 'total']
        ]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_balance()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/balances/99999');

        $response->assertStatus(404);
    }
}
