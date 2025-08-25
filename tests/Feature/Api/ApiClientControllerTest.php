<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\ApiClient;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class ApiClientControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $user;
    protected $apiClient;
    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario autenticado
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        
        // Crear organización
        $this->organization = Organization::factory()->create();
        
        // Crear cliente API de prueba
        $this->apiClient = ApiClient::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Test API Client',
            'status' => 'active',
            'active' => true
        ]);
    }

    /** @test */
    public function it_can_list_api_clients()
    {
        $response = $this->getJson('/api/v1/api-clients');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id', 'organization_id', 'name', 'token', 'scopes',
                            'last_used_at', 'status', 'allowed_ips', 'callback_url',
                            'expires_at', 'revoked_at', 'active', 'created_at', 'updated_at'
                        ]
                    ],
                    'links', 'meta'
                ]);
    }

    /** @test */
    public function it_can_create_api_client()
    {
        $clientData = [
            'organization_id' => $this->organization->id,
            'name' => 'New API Client',
            'scopes' => ['read', 'write'],
            'allowed_ips' => ['192.168.1.1'],
            'callback_url' => 'https://example.com/callback',
            'expires_at' => now()->addYear()
        ];

        $response = $this->postJson('/api/v1/api-clients', $clientData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'organization_id', 'name', 'token', 'scopes',
                        'last_used_at', 'status', 'allowed_ips', 'callback_url',
                        'expires_at', 'revoked_at', 'active', 'created_at', 'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('api_clients', [
            'name' => 'New API Client',
            'organization_id' => $this->organization->id
        ]);
    }

    /** @test */
    public function it_can_show_api_client()
    {
        $response = $this->getJson("/api/v1/api-clients/{$this->apiClient->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'organization_id', 'name', 'token', 'scopes',
                        'last_used_at', 'status', 'allowed_ips', 'callback_url',
                        'expires_at', 'revoked_at', 'active', 'created_at', 'updated_at'
                    ]
                ]);
    }

    /** @test */
    public function it_can_update_api_client()
    {
        $updateData = [
            'name' => 'Updated API Client',
            'scopes' => ['read', 'write', 'admin'],
            'status' => 'suspended'
        ];

        $response = $this->putJson("/api/v1/api-clients/{$this->apiClient->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'organization_id', 'name', 'token', 'scopes',
                        'last_used_at', 'status', 'allowed_ips', 'callback_url',
                        'expires_at', 'revoked_at', 'active', 'created_at', 'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('api_clients', [
            'id' => $this->apiClient->id,
            'name' => 'Updated API Client',
            'status' => 'suspended'
        ]);
    }

    /** @test */
    public function it_can_delete_api_client()
    {
        $response = $this->deleteJson("/api/v1/api-clients/{$this->apiClient->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'API client deleted successfully']);

        $this->assertSoftDeleted('api_clients', ['id' => $this->apiClient->id]);
    }

    /** @test */
    public function it_can_regenerate_api_client_token()
    {
        $oldToken = $this->apiClient->token;

        $response = $this->postJson("/api/v1/api-clients/{$this->apiClient->id}/regenerate-token");

        $response->assertStatus(200)
                ->assertJson(['message' => 'API client token regenerated successfully']);

        $this->apiClient->refresh();
        $this->assertNotEquals($oldToken, $this->apiClient->token);
    }

    /** @test */
    public function it_can_suspend_api_client()
    {
        $response = $this->postJson("/api/v1/api-clients/{$this->apiClient->id}/suspend");

        $response->assertStatus(200)
                ->assertJson(['message' => 'API client suspended successfully']);

        $this->assertDatabaseHas('api_clients', [
            'id' => $this->apiClient->id,
            'status' => 'suspended'
        ]);
    }

    /** @test */
    public function it_can_activate_api_client()
    {
        $this->apiClient->update(['status' => 'suspended']);

        $response = $this->postJson("/api/v1/api-clients/{$this->apiClient->id}/activate");

        $response->assertStatus(200)
                ->assertJson(['message' => 'API client activated successfully']);

        $this->assertDatabaseHas('api_clients', [
            'id' => $this->apiClient->id,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_can_revoke_api_client()
    {
        $response = $this->postJson("/api/v1/api-clients/{$this->apiClient->id}/revoke");

        $response->assertStatus(200)
                ->assertJson(['message' => 'API client revoked successfully']);

        $this->assertDatabaseHas('api_clients', [
            'id' => $this->apiClient->id,
            'status' => 'revoked'
        ]);
    }

    /** @test */
    public function it_can_update_api_client_usage()
    {
        $usageData = [
            'last_used_at' => now(),
            'request_count' => 100,
            'last_ip' => '192.168.1.100'
        ];

        $response = $this->postJson("/api/v1/api-clients/{$this->apiClient->id}/update-usage", $usageData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'API client usage updated successfully']);
    }

    /** @test */
    public function it_can_get_api_client_scopes()
    {
        $response = $this->getJson("/api/v1/api-clients/{$this->apiClient->id}/scopes");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'client_id', 'scopes', 'available_scopes', 'permissions'
                    ]
                ]);
    }

    /** @test */
    public function it_can_update_api_client_scopes()
    {
        $scopesData = [
            'scopes' => ['read', 'write', 'admin', 'analytics']
        ];

        $response = $this->postJson("/api/v1/api-clients/{$this->apiClient->id}/update-scopes", $scopesData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'API client scopes updated successfully']);

        $this->assertDatabaseHas('api_clients', [
            'id' => $this->apiClient->id,
            'scopes' => json_encode(['read', 'write', 'admin', 'analytics'])
        ]);
    }

    /** @test */
    public function it_can_get_api_client_rate_limit_info()
    {
        $response = $this->getJson("/api/v1/api-clients/{$this->apiClient->id}/rate-limit-info");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'client_id', 'rate_limits', 'current_usage', 'reset_time'
                    ]
                ]);
    }

    /** @test */
    public function it_can_bulk_update_api_clients()
    {
        $apiClient2 = ApiClient::factory()->create(['organization_id' => $this->organization->id]);
        
        $bulkData = [
            'api_client_ids' => [$this->apiClient->id, $apiClient2->id],
            'updates' => ['status' => 'suspended']
        ];

        $response = $this->postJson('/api/v1/api-clients/bulk-update', $bulkData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'API clients updated successfully']);

        $this->assertDatabaseHas('api_clients', ['id' => $this->apiClient->id, 'status' => 'suspended']);
        $this->assertDatabaseHas('api_clients', ['id' => $apiClient2->id, 'status' => 'suspended']);
    }

    /** @test */
    public function it_can_bulk_delete_api_clients()
    {
        $apiClient2 = ApiClient::factory()->create(['organization_id' => $this->organization->id]);
        
        $bulkData = [
            'api_client_ids' => [$this->apiClient->id, $apiClient2->id]
        ];

        $response = $this->postJson('/api/v1/api-clients/bulk-delete', $bulkData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'API clients deleted successfully']);

        $this->assertSoftDeleted('api_clients', ['id' => $this->apiClient->id]);
        $this->assertSoftDeleted('api_clients', ['id' => $apiClient2->id]);
    }

    /** @test */
    public function it_can_bulk_regenerate_api_client_tokens()
    {
        $apiClient2 = ApiClient::factory()->create(['organization_id' => $this->organization->id]);
        
        $bulkData = [
            'api_client_ids' => [$this->apiClient->id, $apiClient2->id]
        ];

        $response = $this->postJson('/api/v1/api-clients/bulk-regenerate-tokens', $bulkData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'API client tokens regenerated successfully']);
    }

    /** @test */
    public function it_can_get_api_client_statistics()
    {
        $response = $this->getJson('/api/v1/api-clients/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_clients', 'active_clients', 'suspended_clients',
                        'revoked_clients', 'clients_by_organization', 'recent_activity'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_api_client_statuses()
    {
        $response = $this->getJson('/api/v1/api-clients/statuses');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['value', 'label', 'description', 'count']
                    ]
                ]);
    }

    /** @test */
    public function it_can_validate_api_client_token()
    {
        $tokenData = [
            'token' => $this->apiClient->token,
            'ip_address' => '192.168.1.1'
        ];

        $response = $this->postJson('/api/v1/api-clients/validate-token', $tokenData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'valid', 'client_id', 'scopes', 'permissions', 'rate_limit_info'
                    ]
                ]);
    }

    /** @test */
    public function it_validates_required_fields_on_create()
    {
        $response = $this->postJson('/api/v1/api-clients', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['organization_id', 'name']);
    }

    /** @test */
    public function it_validates_organization_id_on_create()
    {
        $clientData = [
            'organization_id' => 99999, // ID inexistente
            'name' => 'Test Client'
        ];

        $response = $this->postJson('/api/v1/api-clients', $clientData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['organization_id']);
    }

    /** @test */
    public function it_validates_scopes_on_create()
    {
        $clientData = [
            'organization_id' => $this->organization->id,
            'name' => 'Test Client',
            'scopes' => ['invalid_scope']
        ];

        $response = $this->postJson('/api/v1/api-clients', $clientData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['scopes']);
    }

    /** @test */
    public function it_requires_authentication()
    {
        auth()->logout();

        $response = $this->getJson('/api/v1/api-clients');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_filter_api_clients_by_organization()
    {
        $otherOrg = Organization::factory()->create();
        $otherOrgClient = ApiClient::factory()->create(['organization_id' => $otherOrg->id]);

        $response = $this->getJson('/api/v1/api-clients?organization_id=' . $this->organization->id);

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_filter_api_clients_by_status()
    {
        $suspendedClient = ApiClient::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'suspended'
        ]);

        $response = $this->getJson('/api/v1/api-clients?status=suspended');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_filter_api_clients_by_active_status()
    {
        $inactiveClient = ApiClient::factory()->create([
            'organization_id' => $this->organization->id,
            'active' => false
        ]);

        $response = $this->getJson('/api/v1/api-clients?active=0');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_sort_api_clients()
    {
        $response = $this->getJson('/api/v1/api-clients?sort=name&order=asc');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_paginate_api_clients()
    {
        ApiClient::factory()->count(15)->create(['organization_id' => $this->organization->id]);

        $response = $this->getJson('/api/v1/api-clients?per_page=10');

        $response->assertStatus(200)
                ->assertJsonCount(10, 'data');
    }

    /** @test */
    public function it_can_search_api_clients()
    {
        $response = $this->getJson('/api/v1/api-clients?search=Test');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_filter_api_clients_by_expiration()
    {
        $expiredClient = ApiClient::factory()->create([
            'organization_id' => $this->organization->id,
            'expires_at' => now()->subDay()
        ]);

        $response = $this->getJson('/api/v1/api-clients?expired=1');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }
}
