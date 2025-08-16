<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Menu;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MenuControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
    }

    public function test_can_list_published_menus()
    {
        $publishedMenu = Menu::factory()->published()->active()->create([
            'order' => 1
        ]);

        $draftMenu = Menu::factory()->draft()->active()->create();

        $response = $this->getJson('/api/v1/menus');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'text',
                        'is_active',
                        'order'
                    ]
                ]
            ]);

        // Solo debe devolver menús publicados
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_filter_menus_by_group()
    {
        $headerMenu = Menu::factory()->group('header')->published()->active()->create();

        $footerMenu = Menu::factory()->group('footer')->published()->active()->create();

        $response = $this->getJson('/api/v1/menus?menu_group=header');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('header', $data[0]['menu_group']);
    }

    public function test_can_filter_menus_by_language()
    {
        $spanishMenu = Menu::factory()->language('es')->published()->active()->create();

        $englishMenu = Menu::factory()->language('en')->published()->active()->create();

        $response = $this->getJson('/api/v1/menus?language=es');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('es', $data[0]['language']);
    }

    public function test_can_filter_root_items()
    {
        $parentMenu = Menu::factory()->root()->published()->active()->create();

        $childMenu = Menu::factory()->child($parentMenu)->published()->active()->create();

        $response = $this->getJson('/api/v1/menus?parent_id=null');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertNull($data[0]['parent_id']);
    }

    public function test_can_filter_by_parent_id()
    {
        $parentMenu = Menu::factory()->create([
            'is_published' => true,
            'is_active' => true
        ]);

        $childMenu = Menu::factory()->create([
            'parent_id' => $parentMenu->id,
            'is_published' => true,
            'is_active' => true
        ]);

        $response = $this->getJson("/api/v1/menus?parent_id={$parentMenu->id}");

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($parentMenu->id, $data[0]['parent_id']);
    }

    public function test_can_include_children()
    {
        $parentMenu = Menu::factory()->create([
            'is_published' => true,
            'is_active' => true
        ]);

        $childMenu = Menu::factory()->create([
            'parent_id' => $parentMenu->id,
            'is_published' => true,
            'is_active' => true
        ]);

        $response = $this->getJson('/api/v1/menus?include_children=true');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'children' => [
                            '*' => [
                                'id',
                                'title'
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_authenticated_user_can_create_menu()
    {
        Sanctum::actingAs($this->user);

        $menuData = [
            'text' => 'Test Menu',
            'internal_link' => '/test',
            'menu_group' => 'header',
            'language' => 'es',
            'is_active' => true,
            'order' => 1,
            'organization_id' => $this->organization->id
        ];

        $response = $this->postJson('/api/v1/menus', $menuData);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'text',
                    'menu_group',
                    'language'
                ],
                'message'
            ]);

        $this->assertDatabaseHas('menus', [
            'text' => 'Test Menu',
            'internal_link' => '/test',
            'created_by_user_id' => $this->user->id
        ]);
    }

    public function test_guest_cannot_create_menu()
    {
        $menuData = [
            'title' => 'Test Menu',
            'url' => '/test'
        ];

        $response = $this->postJson('/api/v1/menus', $menuData);

        $response->assertUnauthorized();
    }

    public function test_can_show_published_menu()
    {
        $menu = Menu::factory()->create([
            'is_published' => true,
            'is_active' => true
        ]);

        $response = $this->getJson("/api/v1/menus/{$menu->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'url',
                    'menu_group',
                    'language'
                ]
            ]);
    }

    public function test_cannot_show_unpublished_menu()
    {
        $menu = Menu::factory()->create([
            'is_published' => false,
            'is_active' => true
        ]);

        $response = $this->getJson("/api/v1/menus/{$menu->id}");

        $response->assertNotFound();
    }

    public function test_cannot_show_inactive_menu()
    {
        $menu = Menu::factory()->create([
            'is_published' => true,
            'is_active' => false
        ]);

        $response = $this->getJson("/api/v1/menus/{$menu->id}");

        $response->assertNotFound();
    }

    public function test_can_show_menu_with_hierarchy()
    {
        $parentMenu = Menu::factory()->create([
            'is_published' => true,
            'is_active' => true
        ]);

        $childMenu = Menu::factory()->create([
            'parent_id' => $parentMenu->id,
            'is_published' => true,
            'is_active' => true
        ]);

        $response = $this->getJson("/api/v1/menus/{$parentMenu->id}?include_hierarchy=true");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'children' => [
                        '*' => ['id', 'title']
                    ]
                ]
            ]);
    }

    public function test_authenticated_user_can_update_menu()
    {
        Sanctum::actingAs($this->user);

        $menu = Menu::factory()->create();

        $updateData = [
            'title' => 'Updated Menu Title',
            'url' => '/updated-url'
        ];

        $response = $this->putJson("/api/v1/menus/{$menu->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Elemento de menú actualizado exitosamente'
            ]);

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'title' => 'Updated Menu Title',
            'url' => '/updated-url',
            'updated_by_user_id' => $this->user->id
        ]);
    }

    public function test_guest_cannot_update_menu()
    {
        $menu = Menu::factory()->create();

        $updateData = [
            'title' => 'Hacked Title'
        ];

        $response = $this->putJson("/api/v1/menus/{$menu->id}", $updateData);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_delete_menu_without_children()
    {
        Sanctum::actingAs($this->user);

        $menu = Menu::factory()->create();

        $response = $this->deleteJson("/api/v1/menus/{$menu->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Elemento de menú eliminado exitosamente'
            ]);

        $this->assertDatabaseMissing('menus', [
            'id' => $menu->id
        ]);
    }

    public function test_cannot_delete_menu_with_children()
    {
        Sanctum::actingAs($this->user);

        $parentMenu = Menu::factory()->create();
        $childMenu = Menu::factory()->create(['parent_id' => $parentMenu->id]);

        $response = $this->deleteJson("/api/v1/menus/{$parentMenu->id}");

        $response->assertUnprocessable()
            ->assertJsonFragment([
                'message' => 'No se puede eliminar un elemento de menú que tiene elementos hijos'
            ]);

        $this->assertDatabaseHas('menus', [
            'id' => $parentMenu->id
        ]);
    }

    public function test_guest_cannot_delete_menu()
    {
        $menu = Menu::factory()->create();

        $response = $this->deleteJson("/api/v1/menus/{$menu->id}");

        $response->assertUnauthorized();
    }

    public function test_can_get_menus_by_valid_group()
    {
        $headerMenu = Menu::factory()->create([
            'menu_group' => 'header',
            'parent_id' => null,
            'is_published' => true,
            'is_active' => true
        ]);

        $response = $this->getJson('/api/v1/menus/group/header');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'group',
                'group_label',
                'total_items'
            ]);

        $this->assertEquals('header', $response->json('group'));
        $this->assertEquals(1, $response->json('total_items'));
    }

    public function test_cannot_get_menus_by_invalid_group()
    {
        $response = $this->getJson('/api/v1/menus/group/invalid-group');

        $response->assertUnprocessable()
            ->assertJsonFragment([
                'message' => 'Grupo de menú no válido'
            ]);
    }

    public function test_can_get_menus_by_group_with_language_filter()
    {
        $spanishMenu = Menu::factory()->create([
            'menu_group' => 'header',
            'language' => 'es',
            'parent_id' => null,
            'is_published' => true,
            'is_active' => true
        ]);

        $englishMenu = Menu::factory()->create([
            'menu_group' => 'header',
            'language' => 'en',
            'parent_id' => null,
            'is_published' => true,
            'is_active' => true
        ]);

        $response = $this->getJson('/api/v1/menus/group/header?language=es');

        $response->assertOk();
        $this->assertEquals(1, $response->json('total_items'));
    }

    public function test_can_get_menu_hierarchy()
    {
        // Crear menús para diferentes grupos
        $headerMenu = Menu::factory()->create([
            'menu_group' => 'header',
            'parent_id' => null,
            'is_published' => true,
            'is_active' => true
        ]);

        $footerMenu = Menu::factory()->create([
            'menu_group' => 'footer',
            'parent_id' => null,
            'is_published' => true,
            'is_active' => true
        ]);

        $response = $this->getJson('/api/v1/menus/hierarchy');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'label',
                        'items',
                        'count'
                    ]
                ],
                'total_groups',
                'total_items'
            ]);

        $data = $response->json('data');
        $this->assertArrayHasKey('header', $data);
        $this->assertArrayHasKey('footer', $data);
        $this->assertEquals(2, $response->json('total_items'));
    }

    public function test_can_get_menu_hierarchy_with_language_filter()
    {
        $spanishMenu = Menu::factory()->create([
            'menu_group' => 'header',
            'language' => 'es',
            'parent_id' => null,
            'is_published' => true,
            'is_active' => true
        ]);

        $englishMenu = Menu::factory()->create([
            'menu_group' => 'header',
            'language' => 'en',
            'parent_id' => null,
            'is_published' => true,
            'is_active' => true
        ]);

        $response = $this->getJson('/api/v1/menus/hierarchy?language=es');

        $response->assertOk();
        $this->assertEquals(1, $response->json('total_items'));
    }

    public function test_menu_hierarchy_includes_nested_children()
    {
        $parentMenu = Menu::factory()->create([
            'menu_group' => 'header',
            'parent_id' => null,
            'is_published' => true,
            'is_active' => true
        ]);

        $childMenu = Menu::factory()->create([
            'parent_id' => $parentMenu->id,
            'is_published' => true,
            'is_active' => true
        ]);

        $grandchildMenu = Menu::factory()->create([
            'parent_id' => $childMenu->id,
            'is_published' => true,
            'is_active' => true
        ]);

        $response = $this->getJson('/api/v1/menus/hierarchy');

        $response->assertOk();
        
        // Debe incluir relaciones anidadas
        $headerMenus = $response->json('data.header.items');
        $this->assertCount(1, $headerMenus); // Solo el parent
        $this->assertArrayHasKey('children', $headerMenus[0]);
    }

    public function test_validation_works_for_menu_creation()
    {
        Sanctum::actingAs($this->user);

        $invalidData = [
            'title' => '', // requerido
            'url' => 'invalid-url', // debe ser URL válida
            'menu_group' => 'invalid-group' // debe estar en lista válida
        ];

        $response = $this->postJson('/api/v1/menus', $invalidData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'menu_group']);
    }

    public function test_validation_works_for_menu_update()
    {
        Sanctum::actingAs($this->user);

        $menu = Menu::factory()->create();

        $invalidData = [
            'title' => '',
            'sort_order' => 'not-a-number'
        ];

        $response = $this->putJson("/api/v1/menus/{$menu->id}", $invalidData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'sort_order']);
    }

    public function test_menus_are_ordered_by_sort_order()
    {
        $menu3 = Menu::factory()->create([
            'sort_order' => 3,
            'is_published' => true,
            'is_active' => true
        ]);

        $menu1 = Menu::factory()->create([
            'sort_order' => 1,
            'is_published' => true,
            'is_active' => true
        ]);

        $menu2 = Menu::factory()->create([
            'sort_order' => 2,
            'is_published' => true,
            'is_active' => true
        ]);

        $response = $this->getJson('/api/v1/menus');

        $response->assertOk();
        $data = $response->json('data');
        
        // Verificar que están ordenados por sort_order
        $this->assertEquals(1, $data[0]['sort_order']);
        $this->assertEquals(2, $data[1]['sort_order']);
        $this->assertEquals(3, $data[2]['sort_order']);
    }
}
