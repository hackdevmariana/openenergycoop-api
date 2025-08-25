<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\DashboardView;
use App\Models\DashboardWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class DashboardViewControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $user;
    protected $dashboardView;
    protected $widget;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario autenticado
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        
        // Crear vista del dashboard
        $this->dashboardView = DashboardView::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Dashboard',
            'is_default' => false
        ]);
        
        // Crear widget de prueba
        $this->widget = DashboardWidget::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'energy_production'
        ]);
    }

    /** @test */
    public function it_can_list_dashboard_views()
    {
        $response = $this->getJson('/api/v1/dashboard-views');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id', 'user_id', 'name', 'layout_json', 'is_default',
                            'created_at', 'updated_at'
                        ]
                    ],
                    'links', 'meta'
                ]);
    }

    /** @test */
    public function it_can_create_dashboard_view()
    {
        $viewData = [
            'name' => 'New Dashboard',
            'layout_json' => [
                [
                    'module' => 'wallet',
                    'position' => 1,
                    'visible' => true,
                    'settings' => ['currency' => 'EUR']
                ]
            ],
            'is_default' => false
        ];

        $response = $this->postJson('/api/v1/dashboard-views', $viewData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'user_id', 'name', 'layout_json', 'is_default',
                        'created_at', 'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('dashboard_views', [
            'name' => 'New Dashboard',
            'is_default' => false
        ]);
    }

    /** @test */
    public function it_can_show_dashboard_view()
    {
        $response = $this->getJson("/api/v1/dashboard-views/{$this->dashboardView->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'user_id', 'name', 'layout_json', 'is_default',
                        'created_at', 'updated_at'
                    ]
                ]);
    }

    /** @test */
    public function it_can_update_dashboard_view()
    {
        $updateData = [
            'name' => 'Updated Dashboard',
            'is_default' => true
        ];

        $response = $this->putJson("/api/v1/dashboard-views/{$this->dashboardView->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'user_id', 'name', 'layout_json', 'is_default',
                        'created_at', 'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('dashboard_views', [
            'id' => $this->dashboardView->id,
            'name' => 'Updated Dashboard',
            'is_default' => true
        ]);
    }

    /** @test */
    public function it_can_delete_dashboard_view()
    {
        $response = $this->deleteJson("/api/v1/dashboard-views/{$this->dashboardView->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Dashboard view deleted successfully']);

        $this->assertSoftDeleted('dashboard_views', ['id' => $this->dashboardView->id]);
    }

    /** @test */
    public function it_can_set_dashboard_view_as_default()
    {
        $response = $this->postJson("/api/v1/dashboard-views/{$this->dashboardView->id}/set-as-default");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Dashboard view set as default successfully']);

        $this->assertDatabaseHas('dashboard_views', [
            'id' => $this->dashboardView->id,
            'is_default' => true
        ]);
    }

    /** @test */
    public function it_can_duplicate_dashboard_view()
    {
        $response = $this->postJson("/api/v1/dashboard-views/{$this->dashboardView->id}/duplicate");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'user_id', 'name', 'layout_json', 'is_default',
                        'created_at', 'updated_at'
                    ]
                ]);

        $this->assertDatabaseCount('dashboard_views', 2);
    }

    /** @test */
    public function it_can_add_widget_to_dashboard_view()
    {
        $widgetData = [
            'widget_id' => $this->widget->id,
            'position' => 1,
            'settings' => ['refresh_interval' => 30]
        ];

        $response = $this->postJson("/api/v1/dashboard-views/{$this->dashboardView->id}/add-widget", $widgetData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Widget added to dashboard view successfully']);
    }

    /** @test */
    public function it_can_remove_widget_from_dashboard_view()
    {
        $widgetData = [
            'widget_id' => $this->widget->id
        ];

        $response = $this->postJson("/api/v1/dashboard-views/{$this->dashboardView->id}/remove-widget", $widgetData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Widget removed from dashboard view successfully']);
    }

    /** @test */
    public function it_can_move_widget_in_dashboard_view()
    {
        $moveData = [
            'widget_id' => $this->widget->id,
            'new_position' => 3
        ];

        $response = $this->postJson("/api/v1/dashboard-views/{$this->dashboardView->id}/move-widget", $moveData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Widget moved successfully']);
    }

    /** @test */
    public function it_can_update_module_settings()
    {
        $settingsData = [
            'module' => 'wallet',
            'settings' => ['currency' => 'USD', 'locale' => 'en']
        ];

        $response = $this->postJson("/api/v1/dashboard-views/{$this->dashboardView->id}/update-module-settings", $settingsData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Module settings updated successfully']);
    }

    /** @test */
    public function it_can_share_dashboard_view()
    {
        $shareData = [
            'user_id' => User::factory()->create()->id,
            'permissions' => ['view', 'edit']
        ];

        $response = $this->postJson("/api/v1/dashboard-views/{$this->dashboardView->id}/share", $shareData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Dashboard view shared successfully']);
    }

    /** @test */
    public function it_can_unshare_dashboard_view()
    {
        $unshareData = [
            'user_id' => User::factory()->create()->id
        ];

        $response = $this->postJson("/api/v1/dashboard-views/{$this->dashboardView->id}/unshare", $unshareData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Dashboard view unshared successfully']);
    }

    /** @test */
    public function it_can_get_dashboard_view_statistics()
    {
        $response = $this->getJson('/api/v1/dashboard-views/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_views', 'default_views', 'shared_views',
                        'views_by_user', 'recent_activity'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_themes()
    {
        $response = $this->getJson('/api/v1/dashboard-views/themes');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'name', 'description', 'colors', 'preview_url']
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_color_schemes()
    {
        $response = $this->getJson('/api/v1/dashboard-views/color-schemes');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'name', 'primary', 'secondary', 'accent', 'background']
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_default_layout()
    {
        $response = $this->getJson('/api/v1/dashboard-views/default-layout');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'layout' => [
                            '*' => ['module', 'position', 'visible', 'settings']
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_validates_required_fields_on_create()
    {
        $response = $this->postJson('/api/v1/dashboard-views', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['layout_json']);
    }

    /** @test */
    public function it_validates_layout_json_structure()
    {
        $invalidData = [
            'name' => 'Test View',
            'layout_json' => 'invalid_json_string'
        ];

        $response = $this->postJson('/api/v1/dashboard-views', $invalidData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['layout_json']);
    }

    /** @test */
    public function it_requires_authentication()
    {
        auth()->logout();

        $response = $this->getJson('/api/v1/dashboard-views');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_filter_dashboard_views_by_user()
    {
        $otherUser = User::factory()->create();
        DashboardView::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson('/api/v1/dashboard-views?user_id=' . $this->user->id);

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_filter_dashboard_views_by_default_status()
    {
        $defaultView = DashboardView::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true
        ]);

        $response = $this->getJson('/api/v1/dashboard-views?is_default=1');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_sort_dashboard_views()
    {
        $response = $this->getJson('/api/v1/dashboard-views?sort=name&order=desc');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_paginate_dashboard_views()
    {
        DashboardView::factory()->count(15)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/dashboard-views?per_page=10');

        $response->assertStatus(200)
                ->assertJsonCount(10, 'data');
    }
}
