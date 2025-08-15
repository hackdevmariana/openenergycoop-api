<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\InvitationToken;
use App\Models\Organization;
use App\Models\OrganizationRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvitationTokenControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;
    protected OrganizationRole $role;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organization = Organization::factory()->create();
        $this->role = OrganizationRole::factory()->create([
            'organization_id' => $this->organization->id
        ]);
        
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }

    #[Test]
    public function it_can_list_invitation_tokens()
    {
        Sanctum::actingAs($this->user);
        
        // Crear algunos tokens de prueba
        InvitationToken::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'organization_role_id' => $this->role->id,
            'invited_by' => $this->user->id
        ]);

        $response = $this->getJson('/api/v1/invitation-tokens');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'token',
                    'email',
                    'status',
                    'expires_at',
                    'used_at',
                    'invitation_url',
                    'is_valid',
                    'is_expired',
                    'created_at',
                    'updated_at',
                    'organization',
                    'organization_role',
                    'invited_by'
                ]
            ]
        ]);
    }

    #[Test]
    public function it_can_filter_tokens_by_status()
    {
        Sanctum::actingAs($this->user);
        
        InvitationToken::factory()->pending()->count(2)->create([
            'organization_id' => $this->organization->id,
            'organization_role_id' => $this->role->id,
            'invited_by' => $this->user->id
        ]);
        
        InvitationToken::factory()->used()->count(1)->create([
            'organization_id' => $this->organization->id,
            'organization_role_id' => $this->role->id,
            'invited_by' => $this->user->id
        ]);

        $response = $this->getJson('/api/v1/invitation-tokens?status=pending');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $token) {
            $this->assertEquals('pending', $token['status']);
        }
    }

    #[Test]
    public function it_can_filter_tokens_by_organization()
    {
        Sanctum::actingAs($this->user);
        
        $anotherOrg = Organization::factory()->create();
        $anotherRole = OrganizationRole::factory()->create([
            'organization_id' => $anotherOrg->id
        ]);
        
        InvitationToken::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'organization_role_id' => $this->role->id,
            'invited_by' => $this->user->id
        ]);
        
        InvitationToken::factory()->count(1)->create([
            'organization_id' => $anotherOrg->id,
            'organization_role_id' => $anotherRole->id,
            'invited_by' => $this->user->id
        ]);

        $response = $this->getJson("/api/v1/invitation-tokens?organization_id={$this->organization->id}");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        
        foreach ($response->json('data') as $token) {
            $this->assertEquals($this->organization->id, $token['organization']['id']);
        }
    }

    #[Test]
    public function it_can_create_invitation_token()
    {
        Sanctum::actingAs($this->user);

        $tokenData = [
            'organization_id' => $this->organization->id,
            'organization_role_id' => $this->role->id,
            'email' => 'test@example.com',
            'expires_at' => now()->addDays(7)->toISOString()
        ];

        $response = $this->postJson('/api/v1/invitation-tokens', $tokenData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'token',
                'email',
                'status',
                'expires_at',
                'invitation_url',
                'is_valid',
                'organization',
                'organization_role',
                'invited_by'
            ]
        ]);
        
        $response->assertJson([
            'data' => [
                'email' => 'test@example.com',
                'status' => 'pending',
                'is_valid' => true
            ]
        ]);
        
        $this->assertDatabaseHas('invitation_tokens', [
            'email' => 'test@example.com',
            'organization_id' => $this->organization->id,
            'organization_role_id' => $this->role->id,
            'invited_by' => $this->user->id,
            'status' => 'pending'
        ]);
    }

    #[Test]
    public function it_can_create_token_without_email()
    {
        Sanctum::actingAs($this->user);

        $tokenData = [
            'organization_id' => $this->organization->id,
            'organization_role_id' => $this->role->id
        ];

        $response = $this->postJson('/api/v1/invitation-tokens', $tokenData);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'email' => null,
                'status' => 'pending'
            ]
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_token()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/invitation-tokens', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'organization_id',
            'organization_role_id'
        ]);
    }

    #[Test]
    public function it_validates_role_belongs_to_organization()
    {
        Sanctum::actingAs($this->user);
        
        $anotherOrg = Organization::factory()->create();
        $anotherRole = OrganizationRole::factory()->create([
            'organization_id' => $anotherOrg->id
        ]);

        $tokenData = [
            'organization_id' => $this->organization->id,
            'organization_role_id' => $anotherRole->id, // Role from different org
        ];

        $response = $this->postJson('/api/v1/invitation-tokens', $tokenData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['organization_role_id']);
    }

    #[Test]
    public function it_can_show_specific_token()
    {
        Sanctum::actingAs($this->user);
        
        $token = InvitationToken::factory()->create([
            'organization_id' => $this->organization->id,
            'organization_role_id' => $this->role->id,
            'invited_by' => $this->user->id,
            'email' => 'test@example.com'
        ]);

        $response = $this->getJson("/api/v1/invitation-tokens/{$token->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $token->id,
                'email' => 'test@example.com'
            ]
        ]);
    }

    #[Test]
    public function it_can_revoke_pending_token()
    {
        Sanctum::actingAs($this->user);
        
        $token = InvitationToken::factory()->pending()->create([
            'organization_id' => $this->organization->id,
            'organization_role_id' => $this->role->id,
            'invited_by' => $this->user->id
        ]);

        $response = $this->postJson("/api/v1/invitation-tokens/{$token->id}/revoke");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Token revocado exitosamente',
            'data' => [
                'id' => $token->id,
                'status' => 'revoked'
            ]
        ]);
        
        $this->assertDatabaseHas('invitation_tokens', [
            'id' => $token->id,
            'status' => 'revoked'
        ]);
    }

    #[Test]
    public function it_cannot_revoke_non_pending_token()
    {
        Sanctum::actingAs($this->user);
        
        $token = InvitationToken::factory()->used()->create([
            'organization_id' => $this->organization->id,
            'organization_role_id' => $this->role->id,
            'invited_by' => $this->user->id
        ]);

        $response = $this->postJson("/api/v1/invitation-tokens/{$token->id}/revoke");

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Solo se pueden revocar tokens pendientes'
        ]);
    }

    #[Test]
    public function it_can_validate_valid_token()
    {
        $token = InvitationToken::factory()->pending()->create([
            'organization_id' => $this->organization->id,
            'organization_role_id' => $this->role->id,
            'invited_by' => $this->user->id,
            'expires_at' => now()->addDays(7)
        ]);

        $response = $this->getJson("/api/v1/invitation-tokens/validate/{$token->token}");

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'data' => [
                'id' => $token->id,
                'token' => $token->token,
                'status' => 'pending'
            ]
        ]);
    }

    #[Test]
    public function it_can_validate_invalid_token()
    {
        $token = InvitationToken::factory()->expired()->create([
            'organization_id' => $this->organization->id,
            'organization_role_id' => $this->role->id,
            'invited_by' => $this->user->id
        ]);

        $response = $this->getJson("/api/v1/invitation-tokens/validate/{$token->token}");

        $response->assertStatus(400);
        $response->assertJson([
            'valid' => false,
            'message' => 'Token inválido o expirado'
        ]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_token_validation()
    {
        $response = $this->getJson('/api/v1/invitation-tokens/validate/non-existent-token');

        $response->assertStatus(404);
        $response->assertJson([
            'valid' => false,
            'message' => 'Token no encontrado'
        ]);
    }

    #[Test]
    public function it_requires_authentication_for_most_endpoints()
    {
        $token = InvitationToken::factory()->create([
            'organization_id' => $this->organization->id,
            'organization_role_id' => $this->role->id,
            'invited_by' => $this->user->id
        ]);

        // Endpoints que requieren autenticación
        $this->getJson('/api/v1/invitation-tokens')->assertStatus(401);
        $this->postJson('/api/v1/invitation-tokens', [])->assertStatus(401);
        $this->getJson("/api/v1/invitation-tokens/{$token->id}")->assertStatus(401);
        $this->postJson("/api/v1/invitation-tokens/{$token->id}/revoke")->assertStatus(401);
        
        // Endpoint público (validación) - debería devolver 200 para un token válido
        $this->getJson("/api/v1/invitation-tokens/validate/{$token->token}")->assertStatus(200);
    }

    #[Test]
    public function it_generates_unique_tokens()
    {
        Sanctum::actingAs($this->user);

        $tokenData = [
            'organization_id' => $this->organization->id,
            'organization_role_id' => $this->role->id
        ];

        $response1 = $this->postJson('/api/v1/invitation-tokens', $tokenData);
        $response2 = $this->postJson('/api/v1/invitation-tokens', $tokenData);

        $response1->assertStatus(201);
        $response2->assertStatus(201);
        
        $token1 = $response1->json('data.token');
        $token2 = $response2->json('data.token');
        
        $this->assertNotEquals($token1, $token2);
    }

    #[Test]
    public function it_sets_default_expiration_if_not_provided()
    {
        Sanctum::actingAs($this->user);

        $tokenData = [
            'organization_id' => $this->organization->id,
            'organization_role_id' => $this->role->id
        ];

        $response = $this->postJson('/api/v1/invitation-tokens', $tokenData);

        $response->assertStatus(201);
        
        $expiresAt = $response->json('data.expires_at');
        $this->assertNotNull($expiresAt);
        
        // Verificar que expira en aproximadamente 7 días
        $expectedExpiration = now()->addDays(7);
        $actualExpiration = \Carbon\Carbon::parse($expiresAt);
        
        $this->assertTrue($actualExpiration->diffInMinutes($expectedExpiration) < 5); // Tolerancia de 5 minutos
    }
}
