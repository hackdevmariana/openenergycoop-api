<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Company;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
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
    public function it_can_list_companies()
    {
        Sanctum::actingAs($this->user);
        
        // Crear algunas empresas de prueba
        Company::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/companies');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'cif',
                    'contact_person',
                    'company_address',
                    'created_at',
                    'updated_at'
                ]
            ],
            'links',
            'meta'
        ]);
        
        expect($response->json('data'))->toHaveCount(3);
    }

    #[Test]
    public function it_can_filter_companies_by_search()
    {
        Sanctum::actingAs($this->user);
        
        // Crear empresas con nombres específicos
        Company::factory()->create(['name' => 'Energía Solar S.L.']);
        Company::factory()->create(['name' => 'Gas Natural S.A.']);
        Company::factory()->create(['name' => 'Eólica del Norte']);

        $response = $this->getJson('/api/v1/companies?search=Energía');

        $response->assertStatus(200);
        $data = $response->json('data');
        expect($data)->toHaveCount(1);
        expect($data[0]['name'])->toBe('Energía Solar S.L.');
    }

    #[Test]
    public function it_can_create_company()
    {
        Sanctum::actingAs($this->user);
        
        $companyData = [
            'name' => 'Nueva Empresa S.L.',
            'cif' => 'B87654321',
            'contact_person' => 'María García',
            'company_address' => 'Calle Nueva 456, Madrid'
        ];

        $response = $this->postJson('/api/v1/companies', $companyData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'cif',
                'contact_person',
                'company_address',
                'created_at',
                'updated_at'
            ],
            'message'
        ]);
        
        $this->assertDatabaseHas('companies', $companyData);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_company()
    {
        Sanctum::actingAs($this->user);
        
        $response = $this->postJson('/api/v1/companies', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'cif', 'contact_person', 'company_address']);
    }

    #[Test]
    public function it_validates_unique_cif_when_creating_company()
    {
        Sanctum::actingAs($this->user);
        
        // Crear una empresa con CIF existente
        Company::factory()->create(['cif' => 'B12345678']);
        
        $companyData = [
            'name' => 'Otra Empresa S.L.',
            'cif' => 'B12345678', // CIF duplicado
            'contact_person' => 'Juan Pérez',
            'company_address' => 'Calle Otra 789, Barcelona'
        ];

        $response = $this->postJson('/api/v1/companies', $companyData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['cif']);
    }

    #[Test]
    public function it_can_show_company()
    {
        Sanctum::actingAs($this->user);
        
        $company = Company::factory()->create();

        $response = $this->getJson("/api/v1/companies/{$company->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'cif',
                'contact_person',
                'company_address',
                'created_at',
                'updated_at'
            ]
        ]);
        
        expect($response->json('data.id'))->toBe($company->id);
        expect($response->json('data.name'))->toBe($company->name);
    }

    #[Test]
    public function it_returns_404_for_nonexistent_company()
    {
        Sanctum::actingAs($this->user);
        
        $response = $this->getJson('/api/v1/companies/99999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_update_company()
    {
        Sanctum::actingAs($this->user);
        
        $company = Company::factory()->create();
        
        $updateData = [
            'name' => 'Empresa Actualizada S.L.',
            'contact_person' => 'Pedro López'
        ];

        $response = $this->putJson("/api/v1/companies/{$company->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'cif',
                'contact_person',
                'company_address',
                'created_at',
                'updated_at'
            ],
            'message'
        ]);
        
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Empresa Actualizada S.L.',
            'contact_person' => 'Pedro López'
        ]);
    }

    #[Test]
    public function it_validates_unique_cif_when_updating_company()
    {
        Sanctum::actingAs($this->user);
        
        // Crear dos empresas con CIFs diferentes
        $company1 = Company::factory()->create(['cif' => 'B11111111']);
        $company2 = Company::factory()->create(['cif' => 'B22222222']);
        
        $updateData = [
            'cif' => 'B11111111' // Intentar usar el CIF de la primera empresa
        ];

        $response = $this->putJson("/api/v1/companies/{$company2->id}", $updateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['cif']);
    }

    #[Test]
    public function it_can_delete_company()
    {
        Sanctum::actingAs($this->user);
        
        $company = Company::factory()->create();

        $response = $this->deleteJson("/api/v1/companies/{$company->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure(['message']);
        
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    #[Test]
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/companies');
        $response->assertStatus(401);
        
        $response = $this->postJson('/api/v1/companies', []);
        $response->assertStatus(401);
        
        $response = $this->getJson('/api/v1/companies/1');
        $response->assertStatus(401);
        
        $response = $this->putJson('/api/v1/companies/1', []);
        $response->assertStatus(401);
        
        $response = $this->deleteJson('/api/v1/companies/1');
        $response->assertStatus(401);
    }

    #[Test]
    public function it_respects_pagination()
    {
        Sanctum::actingAs($this->user);
        
        // Crear 25 empresas
        Company::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/companies?per_page=10');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page'
            ]
        ]);
        
        expect($response->json('meta.per_page'))->toBe(10);
        expect($response->json('meta.total'))->toBe(25);
        expect($response->json('meta.last_page'))->toBe(3);
    }
}
