<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\DashboardWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class DashboardWidgetControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $user;
    protected $widget;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario autenticado
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
        
        // Crear widget de prueba
        $this->widget = DashboardWidget::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'energy_production',
            'position' => 1,
            'visible' => true
        ]);
    }

    /** @test */
    public function it_can_list_dashboard_widgets()
    {
        $response = $this->getJson('/api/v1/dashboard-widgets');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id', 'user_id', 'type', 'position', 'settings_json',
                            'visible', 'created_at', 'updated_at'
                        ]
                    ],
                    'links', 'meta'
                ]);
    }

    /** @test */
    public function it_can_create_dashboard_widget()
    {
        $widgetData = [
            'type' => 'energy_consumption',
            'position' => 2,
            'settings_json' => [
                'refresh_interval' => 30,
                'chart_type' => 'line'
            ],
            'visible' => true
        ];

        $response = $this->postJson('/api/v1/dashboard-widgets', $widgetData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'user_id', 'type', 'position', 'settings_json',
                        'visible', 'created_at', 'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('dashboard_widgets', [
            'type' => 'energy_consumption',
            'position' => 2
        ]);
    }

    /** @test */
    public function it_can_show_dashboard_widget()
    {
        $response = $this->getJson("/api/v1/dashboard-widgets/{$this->widget->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'user_id', 'type', 'position', 'settings_json',
                        'visible', 'created_at', 'updated_at'
                    ]
                ]);
    }

    /** @test */
    public function it_can_update_dashboard_widget()
    {
        $updateData = [
            'position' => 3,
            'visible' => false
        ];

        $response = $this->putJson("/api/v1/dashboard-widgets/{$this->widget->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'user_id', 'type', 'position', 'settings_json',
                        'visible', 'created_at', 'updated_at'
                    ]
                ]);

        $this->assertDatabaseHas('dashboard_widgets', [
            'id' => $this->widget->id,
            'position' => 3,
            'visible' => false
        ]);
    }

    /** @test */
    public function it_can_delete_dashboard_widget()
    {
        $response = $this->deleteJson("/api/v1/dashboard-widgets/{$this->widget->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Dashboard widget deleted successfully']);

        $this->assertSoftDeleted('dashboard_widgets', ['id' => $this->widget->id]);
    }

    /** @test */
    public function it_can_show_widget()
    {
        $this->widget->update(['visible' => false]);

        $response = $this->postJson("/api/v1/dashboard-widgets/{$this->widget->id}/show");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Widget shown successfully']);

        $this->assertDatabaseHas('dashboard_widgets', [
            'id' => $this->widget->id,
            'visible' => true
        ]);
    }

    /** @test */
    public function it_can_hide_widget()
    {
        $this->widget->update(['visible' => true]);

        $response = $this->postJson("/api/v1/dashboard-widgets/{$this->widget->id}/hide");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Widget hidden successfully']);

        $this->assertDatabaseHas('dashboard_widgets', [
            'id' => $this->widget->id,
            'visible' => false
        ]);
    }

    /** @test */
    public function it_can_refresh_widget()
    {
        $response = $this->postJson("/api/v1/dashboard-widgets/{$this->widget->id}/refresh");

        $response->assertStatus(200)
                ->assertJson(['message' => 'Widget refreshed successfully']);
    }

    /** @test */
    public function it_can_duplicate_widget()
    {
        $response = $this->postJson("/api/v1/dashboard-widgets/{$this->widget->id}/duplicate");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'user_id', 'type', 'position', 'settings_json',
                        'visible', 'created_at', 'updated_at'
                    ]
                ]);

        $this->assertDatabaseCount('dashboard_widgets', 2);
    }

    /** @test */
    public function it_can_update_widget_position()
    {
        $positionData = [
            'new_position' => 5
        ];

        $response = $this->postJson("/api/v1/dashboard-widgets/{$this->widget->id}/update-position", $positionData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Widget position updated successfully']);

        $this->assertDatabaseHas('dashboard_widgets', [
            'id' => $this->widget->id,
            'position' => 5
        ]);
    }

    /** @test */
    public function it_can_update_widget_grid_position()
    {
        $gridData = [
            'row' => 2,
            'column' => 3,
            'width' => 2,
            'height' => 1
        ];

        $response = $this->postJson("/api/v1/dashboard-widgets/{$this->widget->id}/update-grid-position", $gridData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Widget grid position updated successfully']);
    }

    /** @test */
    public function it_can_update_widget_settings()
    {
        $settingsData = [
            'settings' => [
                'refresh_interval' => 60,
                'chart_type' => 'bar',
                'color_scheme' => 'dark'
            ]
        ];

        $response = $this->postJson("/api/v1/dashboard-widgets/{$this->widget->id}/update-settings", $settingsData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Widget settings updated successfully']);
    }

    /** @test */
    public function it_can_update_widget_filters()
    {
        $filtersData = [
            'filters' => [
                'date_range' => 'last_7_days',
                'energy_type' => 'solar',
                'group_by' => 'hour'
            ]
        ];

        $response = $this->postJson("/api/v1/dashboard-widgets/{$this->widget->id}/update-filters", $filtersData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Widget filters updated successfully']);
    }

    /** @test */
    public function it_can_get_widget_data()
    {
        $response = $this->getJson("/api/v1/dashboard-widgets/{$this->widget->id}/data");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'widget_id', 'data', 'last_updated', 'next_refresh'
                    ]
                ]);
    }

    /** @test */
    public function it_can_bulk_update_widgets()
    {
        $widget2 = DashboardWidget::factory()->create(['user_id' => $this->user->id]);
        
        $bulkData = [
            'widget_ids' => [$this->widget->id, $widget2->id],
            'updates' => ['visible' => false]
        ];

        $response = $this->postJson('/api/v1/dashboard-widgets/bulk-update', $bulkData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Widgets updated successfully']);

        $this->assertDatabaseHas('dashboard_widgets', ['id' => $this->widget->id, 'visible' => false]);
        $this->assertDatabaseHas('dashboard_widgets', ['id' => $widget2->id, 'visible' => false]);
    }

    /** @test */
    public function it_can_bulk_delete_widgets()
    {
        $widget2 = DashboardWidget::factory()->create(['user_id' => $this->user->id]);
        
        $bulkData = [
            'widget_ids' => [$this->widget->id, $widget2->id]
        ];

        $response = $this->postJson('/api/v1/dashboard-widgets/bulk-delete', $bulkData);

        $response->assertStatus(200)
                ->assertJson(['message' => 'Widgets deleted successfully']);

        $this->assertSoftDeleted('dashboard_widgets', ['id' => $this->widget->id]);
        $this->assertSoftDeleted('dashboard_widgets', ['id' => $widget2->id]);
    }

    /** @test */
    public function it_can_get_widget_statistics()
    {
        $response = $this->getJson('/api/v1/dashboard-widgets/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'total_widgets', 'visible_widgets', 'hidden_widgets',
                        'widgets_by_type', 'recent_activity'
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_widget_types()
    {
        $response = $this->getJson('/api/v1/dashboard-widgets/types');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['value', 'label', 'description', 'capabilities', 'default_settings']
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_widget_sizes()
    {
        $response = $this->getJson('/api/v1/dashboard-widgets/sizes');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['value', 'label', 'width', 'height', 'description']
                    ]
                ]);
    }

    /** @test */
    public function it_validates_required_fields_on_create()
    {
        $response = $this->postJson('/api/v1/dashboard-widgets', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['type', 'position']);
    }

    /** @test */
    public function it_validates_widget_type_on_create()
    {
        $widgetData = [
            'type' => 'invalid_type',
            'position' => 1
        ];

        $response = $this->postJson('/api/v1/dashboard-widgets', $widgetData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function it_requires_authentication()
    {
        auth()->logout();

        $response = $this->getJson('/api/v1/dashboard-widgets');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_filter_widgets_by_type()
    {
        $energyWidget = DashboardWidget::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'energy_consumption'
        ]);

        $response = $this->getJson('/api/v1/dashboard-widgets?type=energy_consumption');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_filter_widgets_by_visibility()
    {
        $visibleWidget = DashboardWidget::factory()->create([
            'user_id' => $this->user->id,
            'visible' => true
        ]);

        $response = $this->getJson('/api/v1/dashboard-widgets?visible=1');

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_can_sort_widgets()
    {
        $response = $this->getJson('/api/v1/dashboard-widgets?sort=position&order=asc');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_paginate_widgets()
    {
        DashboardWidget::factory()->count(15)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/v1/dashboard-widgets?per_page=10');

        $response->assertStatus(200)
                ->assertJsonCount(10, 'data');
    }
}
