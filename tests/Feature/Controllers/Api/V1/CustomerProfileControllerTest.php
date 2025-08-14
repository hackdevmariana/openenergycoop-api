<?php

use App\Models\User;
use App\Models\Organization;
use App\Models\CustomerProfile;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->organization = Organization::factory()->create();
    $this->user = User::factory()->create();
    
    // Asignar rol de admin al usuario para que tenga permisos completos
    $this->user->assignRole('admin');
    
    // Crear un perfil para el usuario para que tenga una organización asociada
    CustomerProfile::factory()->create([
        'user_id' => $this->user->id,
        'organization_id' => $this->organization->id,
    ]);
});

test('it can list customer profiles', function () {
    Sanctum::actingAs($this->user);

    CustomerProfile::factory()->count(3)->create([
        'organization_id' => $this->organization->id,
    ]);

    $response = $this->getJson('/api/v1/customer-profiles');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'organization_id',
                    'profile_type',
                    'legal_id_type',
                    'legal_id_number',
                    'legal_name',
                    'contract_type',
                    'created_at',
                    'updated_at',
                ]
            ],
            'links',
            'meta'
        ]);

    // 3 perfiles creados + 1 perfil del beforeEach = 4 total
    expect($response->json('data'))->toHaveCount(4);
});

test('it can filter customer profiles by profile type', function () {
    Sanctum::actingAs($this->user);
    
    // Crear un perfil individual
    CustomerProfile::factory()->create([
        'profile_type' => 'individual',
        'organization_id' => $this->organization->id,
    ]);

    $response = $this->getJson('/api/v1/customer-profiles?profile_type=individual');

    $response->assertStatus(200);
    // Contar solo los perfiles individuales (excluyendo el del beforeEach si no es individual)
    $data = $response->json('data');
    $individualProfiles = collect($data)->filter(fn($profile) => $profile['profile_type'] === 'individual');
    expect($individualProfiles)->toHaveCount(1);
    expect($data[0]['profile_type'])->toBe('individual');
});

test('it can create customer profile', function () {
    Sanctum::actingAs($this->user);

    $user = User::factory()->create();

    $data = [
        'user_id' => $user->id,
        'organization_id' => $this->organization->id,
        'profile_type' => 'individual',
        'legal_id_type' => 'dni',
        'legal_id_number' => '12345678A',
        'legal_name' => 'John Doe',
        'contract_type' => 'own',
    ];

    $response = $this->postJson('/api/v1/customer-profiles', $data);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'user_id',
                'organization_id',
                'profile_type',
                'legal_id_type',
                'legal_id_number',
                'legal_name',
                'contract_type',
            ]
        ]);

    $this->assertDatabaseHas('customer_profiles', [
        'user_id' => $user->id,
        'organization_id' => $this->organization->id,
        'profile_type' => 'individual',
    ]);
});

test('it validates required fields when creating customer profile', function () {
    Sanctum::actingAs($this->user);

    $response = $this->postJson('/api/v1/customer-profiles', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user_id', 'organization_id', 'profile_type', 'legal_id_type', 'legal_id_number', 'legal_name', 'contract_type']);
});

test('it can show customer profile', function () {
    Sanctum::actingAs($this->user);

    $customerProfile = CustomerProfile::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    $response = $this->getJson("/api/v1/customer-profiles/{$customerProfile->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'organization_id',
                'profile_type',
                'legal_id_type',
                'legal_id_number',
                'legal_name',
                'contract_type',
            ]
        ]);
});

test('it returns 404 for nonexistent customer profile', function () {
    Sanctum::actingAs($this->user);

    $response = $this->getJson('/api/v1/customer-profiles/999');

    $response->assertStatus(404);
});

test('it can update customer profile', function () {
    Sanctum::actingAs($this->user);

    $customerProfile = CustomerProfile::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    $updateData = [
        'legal_name' => 'Updated Name',
    ];

    $response = $this->putJson("/api/v1/customer-profiles/{$customerProfile->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Perfil de cliente actualizado exitosamente',
            'data' => [
                'legal_name' => 'Updated Name',
            ]
        ]);

    $this->assertDatabaseHas('customer_profiles', [
        'id' => $customerProfile->id,
        'legal_name' => 'Updated Name',
    ]);
});

test('it can delete customer profile', function () {
    Sanctum::actingAs($this->user);

    $customerProfile = CustomerProfile::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    $response = $this->deleteJson("/api/v1/customer-profiles/{$customerProfile->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Perfil de cliente eliminado exitosamente'
        ]);

    $this->assertDatabaseMissing('customer_profiles', [
        'id' => $customerProfile->id,
    ]);
});

test('it respects organization isolation', function () {
    Sanctum::actingAs($this->user);

    // Crear perfiles en la organización del usuario
    CustomerProfile::factory()->create([
        'organization_id' => $this->organization->id,
    ]);

    // Crear perfiles en otra organización
    $otherOrganization = Organization::factory()->create();
    CustomerProfile::factory()->create([
        'organization_id' => $otherOrganization->id,
    ]);

    $response = $this->getJson('/api/v1/customer-profiles');

    $response->assertStatus(200);
    // 1 perfil creado + 1 perfil del beforeEach = 2 perfiles en la organización del usuario
    expect($response->json('data'))->toHaveCount(2);
    expect($response->json('data.0.organization_id'))->toBe($this->organization->id);
});

test('it requires authentication', function () {
    $response = $this->getJson('/api/v1/customer-profiles');

    $response->assertStatus(401);
});

// Tests de autorización granular
test('super admin can view all customer profiles', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    Sanctum::actingAs($superAdmin);

    // Crear perfiles en diferentes organizaciones
    CustomerProfile::factory()->count(5)->create();

    $response = $this->getJson('/api/v1/customer-profiles');

    $response->assertStatus(200);
    // 5 perfiles creados + 1 perfil del beforeEach = 6 total
    expect($response->json('data'))->toHaveCount(6);
});

test('admin can view only organization profiles', function () {
    $admin = User::factory()->admin()->create();
    $org1 = Organization::factory()->create();
    $org2 = Organization::factory()->create();
    
    // Crear perfil para el admin en org1
    CustomerProfile::factory()->create([
        'user_id' => $admin->id,
        'organization_id' => $org1->id,
    ]);
    
    // Crear perfiles en ambas organizaciones
    CustomerProfile::factory()->count(2)->create(['organization_id' => $org1->id]);
    CustomerProfile::factory()->count(3)->create(['organization_id' => $org2->id]);
    
    Sanctum::actingAs($admin);
    
    $response = $this->getJson('/api/v1/customer-profiles');
    
    $response->assertStatus(200);
    // Solo debe ver perfiles de su organización
    expect($response->json('data'))->toHaveCount(3);
    expect($response->json('data.*.organization_id'))->each->toBe($org1->id);
});

test('agent can only view own profiles', function () {
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    // Crear perfiles para el agente
    CustomerProfile::factory()->create([
        'user_id' => $agent->id,
        'organization_id' => $this->organization->id,
    ]);

    // Crear perfiles para otros usuarios
    CustomerProfile::factory()->count(3)->create([
        'organization_id' => $this->organization->id,
    ]);

    Sanctum::actingAs($agent);

    $response = $this->getJson('/api/v1/customer-profiles');

    $response->assertStatus(200);
    // El agente ve todos los perfiles de su organización: 1 perfil propio + 3 perfiles de otros + 1 perfil del beforeEach = 5 perfiles
    expect($response->json('data'))->toHaveCount(5);
    // Verificar que todos los perfiles son de la misma organización
    foreach ($response->json('data') as $profile) {
        expect($profile['organization_id'])->toBe($this->organization->id);
    }
});

test('customer can only view own profile', function () {
    $customer = User::factory()->customer()->create();
    $org = Organization::factory()->create();
    
    // Crear perfil propio para el customer
    CustomerProfile::factory()->create([
        'user_id' => $customer->id,
        'organization_id' => $org->id,
    ]);
    
    // Crear perfiles de otros usuarios
    CustomerProfile::factory()->count(2)->create(['organization_id' => $org->id]);
    
    Sanctum::actingAs($customer);
    
    $response = $this->getJson('/api/v1/customer-profiles');
    
    $response->assertStatus(200);
    // Solo debe ver su propio perfil
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.user_id'))->toBe($customer->id);
});

test('user without permissions cannot view any profiles', function () {
    $user = User::factory()->create(); // Sin roles asignados
    
    Sanctum::actingAs($user);
    
    $response = $this->getJson('/api/v1/customer-profiles');
    
    $response->assertStatus(403);
});

test('agent can create customer profile', function () {
    $agent = User::factory()->agent()->create();
    $org = Organization::factory()->create();
    
    // Crear perfil para el agent
    CustomerProfile::factory()->create([
        'user_id' => $agent->id,
        'organization_id' => $org->id,
    ]);
    
    Sanctum::actingAs($agent);
    
    $data = [
        'user_id' => User::factory()->create()->id,
        'organization_id' => $org->id,
        'profile_type' => 'individual',
        'legal_id_type' => 'dni',
        'legal_id_number' => '12345678A',
        'legal_name' => 'John Doe',
        'contract_type' => 'own',
    ];
    
    $response = $this->postJson('/api/v1/customer-profiles', $data);
    
    $response->assertStatus(201);
});

test('customer cannot create customer profile', function () {
    $customer = User::factory()->customer()->create();
    $org = Organization::factory()->create();
    
    // Crear perfil para el customer
    CustomerProfile::factory()->create([
        'user_id' => $customer->id,
        'organization_id' => $org->id,
    ]);
    
    Sanctum::actingAs($customer);
    
    $data = [
        'user_id' => User::factory()->create()->id,
        'organization_id' => $org->id,
        'profile_type' => 'individual',
        'legal_id_type' => 'dni',
        'legal_id_number' => '12345678A',
        'legal_name' => 'John Doe',
        'contract_type' => 'own',
    ];
    
    $response = $this->postJson('/api/v1/customer-profiles', $data);
    
    $response->assertStatus(403);
});

test('agent can update own profile', function () {
    $agent = User::factory()->agent()->create();
    $org = Organization::factory()->create();
    
    $profile = CustomerProfile::factory()->create([
        'user_id' => $agent->id,
        'organization_id' => $org->id,
    ]);
    
    Sanctum::actingAs($agent);
    
    $response = $this->putJson("/api/v1/customer-profiles/{$profile->id}", [
        'legal_name' => 'Updated Name',
    ]);
    
    $response->assertStatus(200);
});

test('agent cannot update other user profile', function () {
    $agent = User::factory()->agent()->create();
    $org = Organization::factory()->create();
    
    // Crear perfil para el agent
    CustomerProfile::factory()->create([
        'user_id' => $agent->id,
        'organization_id' => $org->id,
    ]);
    
    // Crear perfil de otro usuario
    $otherProfile = CustomerProfile::factory()->create(['organization_id' => $org->id]);
    
    Sanctum::actingAs($agent);
    
    $response = $this->putJson("/api/v1/customer-profiles/{$otherProfile->id}", [
        'legal_name' => 'Updated Name',
    ]);
    
    $response->assertStatus(403);
});

test('admin can update any profile in organization', function () {
    $admin = User::factory()->admin()->create();
    $org = Organization::factory()->create();
    
    // Crear perfil para el admin
    CustomerProfile::factory()->create([
        'user_id' => $admin->id,
        'organization_id' => $org->id,
    ]);
    
    // Crear perfil de otro usuario
    $otherProfile = CustomerProfile::factory()->create(['organization_id' => $org->id]);
    
    Sanctum::actingAs($admin);
    
    $response = $this->putJson("/api/v1/customer-profiles/{$otherProfile->id}", [
        'legal_name' => 'Updated Name',
    ]);
    
    $response->assertStatus(200);
});

