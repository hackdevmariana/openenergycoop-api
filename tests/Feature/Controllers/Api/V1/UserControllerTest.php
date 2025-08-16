<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected User $regularUser;
    protected Role $adminRole;
    protected Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear permisos y roles usando firstOrCreate para evitar duplicados
        $manageUsersPermission = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);
        
        $this->adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Crear usuarios de prueba
        $this->admin = User::factory()->create();
        $this->admin->assignRole($this->adminRole);
        $this->admin->givePermissionTo($manageUsersPermission);

        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole($this->userRole);
    }

    /** @test */
    public function admin_can_list_users()
    {
        Sanctum::actingAs($this->admin);

        // Crear algunos usuarios adicionales
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/users');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'email_verified_at',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    /** @test */
    public function regular_user_cannot_list_users()
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->getJson('/api/v1/users');

        $response->assertForbidden();
    }

    /** @test */
    public function guest_cannot_list_users()
    {
        $response = $this->getJson('/api/v1/users');

        $response->assertUnauthorized();
    }

    /** @test */
    public function admin_can_filter_users_by_search()
    {
        Sanctum::actingAs($this->admin);

        $searchUser = User::factory()->create([
            'name' => 'John Doe Test',
            'email' => 'john.test@example.com'
        ]);

        User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com'
        ]);

        // Buscar por nombre
        $response = $this->getJson('/api/v1/users?search=John');
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));

        // Buscar por email
        $response = $this->getJson('/api/v1/users?search=john.test');
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    /** @test */
    public function admin_can_filter_users_by_role()
    {
        Sanctum::actingAs($this->admin);

        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $managerUser = User::factory()->create();
        $managerUser->assignRole($managerRole);

        $response = $this->getJson('/api/v1/users?role=manager');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    /** @test */
    public function admin_can_filter_users_by_verified_status()
    {
        Sanctum::actingAs($this->admin);

        $verifiedUser = User::factory()->create(['email_verified_at' => now()]);
        $unverifiedUser = User::factory()->create(['email_verified_at' => null]);

        // Filtrar usuarios verificados
        $response = $this->getJson('/api/v1/users?verified=true');
        $response->assertOk();
        $data = $response->json('data');
        $this->assertTrue(collect($data)->every(fn($user) => $user['email_verified_at'] !== null));

        // Filtrar usuarios no verificados
        $response = $this->getJson('/api/v1/users?verified=false');
        $response->assertOk();
        $data = $response->json('data');
        $this->assertTrue(collect($data)->every(fn($user) => $user['email_verified_at'] === null));
    }

    /** @test */
    public function admin_can_create_user()
    {
        Sanctum::actingAs($this->admin);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user'
        ];

        $response = $this->postJson('/api/v1/users', $userData);



        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at'
                ],
                'message'
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com'
        ]);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue($user->hasRole('user'));
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /** @test */
    public function admin_can_create_verified_user()
    {
        Sanctum::actingAs($this->admin);

        $userData = [
            'name' => 'Verified User',
            'email' => 'verified@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'email_verified' => true
        ];

        $response = $this->postJson('/api/v1/users', $userData);

        $response->assertCreated();

        $user = User::where('email', 'verified@example.com')->first();
        $this->assertNotNull($user->email_verified_at);
    }

    /** @test */
    public function regular_user_cannot_create_user()
    {
        Sanctum::actingAs($this->regularUser);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/v1/users', $userData);

        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_view_any_user()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/v1/users/{$this->regularUser->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /** @test */
    public function user_can_view_own_profile()
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->getJson("/api/v1/users/{$this->regularUser->id}");

        $response->assertOk();
    }

    /** @test */
    public function user_cannot_view_other_users()
    {
        Sanctum::actingAs($this->regularUser);

        $otherUser = User::factory()->create();

        $response = $this->getJson("/api/v1/users/{$otherUser->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_view_user_with_stats()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/v1/users/{$this->regularUser->id}?include_stats=true");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email'],
                'stats' => [
                    'total_devices',
                    'total_settings',
                    'total_consents',
                    'account_age_days'
                ]
            ]);
    }

    /** @test */
    public function admin_can_update_any_user()
    {
        Sanctum::actingAs($this->admin);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];

        $response = $this->putJson("/api/v1/users/{$this->regularUser->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Usuario actualizado exitosamente'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->regularUser->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    /** @test */
    public function user_can_update_own_profile()
    {
        Sanctum::actingAs($this->regularUser);

        $updateData = [
            'name' => 'My Updated Name'
        ];

        $response = $this->putJson("/api/v1/users/{$this->regularUser->id}", $updateData);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $this->regularUser->id,
            'name' => 'My Updated Name'
        ]);
    }

    /** @test */
    public function user_cannot_update_other_users()
    {
        Sanctum::actingAs($this->regularUser);

        $otherUser = User::factory()->create();
        $updateData = ['name' => 'Hacked Name'];

        $response = $this->putJson("/api/v1/users/{$otherUser->id}", $updateData);

        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_update_user_password()
    {
        Sanctum::actingAs($this->admin);

        $updateData = [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->putJson("/api/v1/users/{$this->regularUser->id}", $updateData);

        $response->assertOk();

        $this->regularUser->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->regularUser->password));
    }

    /** @test */
    public function admin_can_update_user_role()
    {
        Sanctum::actingAs($this->admin);

        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);

        $updateData = [
            'name' => $this->regularUser->name,
            'role' => 'manager'
        ];

        $response = $this->putJson("/api/v1/users/{$this->regularUser->id}", $updateData);

        $response->assertOk();

        $this->regularUser->refresh();
        $this->assertTrue($this->regularUser->hasRole('manager'));
    }

    /** @test */
    public function regular_user_cannot_update_roles()
    {
        Sanctum::actingAs($this->regularUser);

        $updateData = [
            'name' => $this->regularUser->name,
            'role' => 'admin'
        ];

        $response = $this->putJson("/api/v1/users/{$this->regularUser->id}", $updateData);

        $response->assertOk(); // La actualización pasa pero el rol no cambia

        $this->regularUser->refresh();
        $this->assertFalse($this->regularUser->hasRole('admin'));
    }

    /** @test */
    public function admin_can_delete_users()
    {
        Sanctum::actingAs($this->admin);

        $userToDelete = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$userToDelete->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Usuario eliminado exitosamente'
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $userToDelete->id
        ]);
    }

    /** @test */
    public function admin_cannot_delete_themselves()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/v1/users/{$this->admin->id}");

        $response->assertUnprocessable()
            ->assertJsonFragment([
                'message' => 'No puedes eliminar tu propia cuenta'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id
        ]);
    }

    /** @test */
    public function regular_user_cannot_delete_users()
    {
        Sanctum::actingAs($this->regularUser);

        $userToDelete = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$userToDelete->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function authenticated_user_can_get_own_profile_via_me_endpoint()
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->getJson('/api/v1/users/me');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJsonFragment([
                'id' => $this->regularUser->id,
                'email' => $this->regularUser->email
            ]);
    }

    /** @test */
    public function guest_cannot_access_me_endpoint()
    {
        $response = $this->getJson('/api/v1/users/me');

        $response->assertUnauthorized();
    }

    /** @test */
    public function user_can_update_own_profile_via_update_me_endpoint()
    {
        Sanctum::actingAs($this->regularUser);

        $updateData = [
            'name' => 'Updated via Me',
            'email' => 'updated.me@example.com'
        ];

        $response = $this->putJson('/api/v1/users/me', $updateData);

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Perfil actualizado exitosamente'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->regularUser->id,
            'name' => 'Updated via Me',
            'email' => 'updated.me@example.com'
        ]);
    }

    /** @test */
    public function user_can_change_password_via_update_me_with_current_password()
    {
        Sanctum::actingAs($this->regularUser);

        // Establecer una contraseña conocida
        $this->regularUser->update(['password' => Hash::make('currentpassword')]);

        $updateData = [
            'current_password' => 'currentpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->putJson('/api/v1/users/me', $updateData);

        $response->assertOk();

        $this->regularUser->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->regularUser->password));
    }

    /** @test */
    public function user_cannot_change_password_with_wrong_current_password()
    {
        Sanctum::actingAs($this->regularUser);

        $updateData = [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->putJson('/api/v1/users/me', $updateData);

        $response->assertUnprocessable()
            ->assertJsonFragment([
                'message' => 'La contraseña actual es incorrecta'
            ]);
    }

    /** @test */
    public function pagination_works_correctly()
    {
        Sanctum::actingAs($this->admin);

        // Crear más usuarios para probar paginación
        User::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/users?per_page=10');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next'
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);

        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(10, $response->json('meta.per_page'));
    }

    /** @test */
    public function per_page_is_limited_to_maximum_50()
    {
        Sanctum::actingAs($this->admin);

        User::factory()->count(60)->create();

        $response = $this->getJson('/api/v1/users?per_page=100');

        $response->assertOk();
        $this->assertLessThanOrEqual(50, count($response->json('data')));
    }

    /** @test */
    public function validation_works_for_user_creation()
    {
        Sanctum::actingAs($this->admin);

        // Test email único
        $existingUser = User::factory()->create();

        $invalidData = [
            'name' => '',
            'email' => $existingUser->email,
            'password' => '123'
        ];

        $response = $this->postJson('/api/v1/users', $invalidData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function validation_works_for_user_update()
    {
        Sanctum::actingAs($this->admin);

        $existingUser = User::factory()->create();

        $invalidData = [
            'name' => '',
            'email' => $existingUser->email
        ];

        $response = $this->putJson("/api/v1/users/{$this->regularUser->id}", $invalidData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }
}
