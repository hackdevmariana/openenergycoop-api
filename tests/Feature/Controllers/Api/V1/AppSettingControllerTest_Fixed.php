<?php

use App\Models\AppSetting;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean up any existing data
    AppSetting::query()->delete();
    
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create();
    
    // Clear cache before each test
    Cache::flush();
});

describe('AppSettingController', function () {
    describe('Index endpoint', function () {
        it('can get app settings when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $setting = AppSetting::factory()->forOrganization($this->organization)->create();

            $response = $this->getJson('/api/v1/app-settings');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            'id',
                            'name',
                            'slogan',
                            'primary_color',
                            'secondary_color',
                            'locale',
                            'custom_js',
                            'favicon_path',
                            'organization_id',
                            'created_at',
                            'updated_at',
                            'organization',
                            'logo_url',
                            'favicon_url',
                            'has_logo',
                            'has_favicon',
                            'effective_favicon',
                            'theme_colors'
                        ]
                    ]);

            expect($response->json('data.id'))->toBe($setting->id);
            expect($response->json('data.organization_id'))->toBe($this->organization->id);
        });

        it('returns first available setting when multiple exist', function () {
            Sanctum::actingAs($this->user);
            
            $org1 = Organization::factory()->create();
            $org2 = Organization::factory()->create();
            
            $setting1 = AppSetting::factory()->forOrganization($org1)->create(['name' => 'First Setting']);
            $setting2 = AppSetting::factory()->forOrganization($org2)->create(['name' => 'Second Setting']);

            $response = $this->getJson('/api/v1/app-settings');

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'name' => 'First Setting'
                        ]
                    ]);
        });

        it('returns null when no settings exist', function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/v1/app-settings');

            $response->assertStatus(200);
            expect($response->json('data'))->toBeNull();
        });

        it('includes organization relationship when loaded', function () {
            Sanctum::actingAs($this->user);
            
            $setting = AppSetting::factory()->forOrganization($this->organization)->create();

            $response = $this->getJson('/api/v1/app-settings');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            'organization' => [
                                'id',
                                'name'
                            ]
                        ]
                    ]);

            $organizationData = $response->json('data.organization');
            expect($organizationData['id'])->toBe($this->organization->id);
            expect($organizationData['name'])->toBe($this->organization->name);
        });

        it('includes computed properties', function () {
            Sanctum::actingAs($this->user);
            
            $setting = AppSetting::factory()
                ->forOrganization($this->organization)
                ->greenTheme()
                ->withFavicon()
                ->create();

            $response = $this->getJson('/api/v1/app-settings');

            $response->assertStatus(200);
            $data = $response->json('data');

            expect($data['has_logo'])->toBe(false); // No media attached
            expect($data['has_favicon'])->toBe(true); // Has favicon_path
            expect($data['effective_favicon'])->toBe('/images/favicon.ico');
            expect($data['theme_colors'])->toBe([
                'primary' => $setting->primary_color,
                'secondary' => $setting->secondary_color,
            ]);
        });

        it('handles settings with custom JavaScript', function () {
            Sanctum::actingAs($this->user);
            
            $setting = AppSetting::factory()
                ->forOrganization($this->organization)
                ->withCustomJs()
                ->create();

            $response = $this->getJson('/api/v1/app-settings');

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'custom_js' => 'console.log("Custom app loaded");'
                        ]
                    ]);
        });

        it('handles settings without custom JavaScript', function () {
            Sanctum::actingAs($this->user);
            
            $setting = AppSetting::factory()
                ->forOrganization($this->organization)
                ->withoutCustomJs()
                ->create();

            $response = $this->getJson('/api/v1/app-settings');

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'custom_js' => null
                        ]
                    ]);
        });

        it('handles different locales', function () {
            Sanctum::actingAs($this->user);
            
            $setting = AppSetting::factory()
                ->forOrganization($this->organization)
                ->english()
                ->create();

            $response = $this->getJson('/api/v1/app-settings');

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'locale' => 'en',
                            'name' => 'Energy Cooperative',
                            'slogan' => 'Renewable energy for everyone'
                        ]
                    ]);
        });

        it('handles different theme colors', function () {
            Sanctum::actingAs($this->user);
            
            $setting = AppSetting::factory()
                ->forOrganization($this->organization)
                ->blueTheme()
                ->create();

            $response = $this->getJson('/api/v1/app-settings');

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'primary_color' => '#3B82F6',
                            'secondary_color' => '#1D4ED8',
                            'theme_colors' => [
                                'primary' => '#3B82F6',
                                'secondary' => '#1D4ED8',
                            ]
                        ]
                    ]);
        });

        it('requires authentication', function () {
            $setting = AppSetting::factory()->forOrganization($this->organization)->create();

            $response = $this->getJson('/api/v1/app-settings');

            $response->assertStatus(401);
        });
    });

    describe('Show endpoint', function () {
        it('can show specific app setting when authenticated', function () {
            Sanctum::actingAs($this->user);
            
            $setting = AppSetting::factory()->forOrganization($this->organization)->create();

            $response = $this->getJson("/api/v1/app-settings/{$setting->id}");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'id' => $setting->id,
                            'name' => $setting->name,
                            'organization_id' => $this->organization->id
                        ]
                    ]);
        });

        it('returns 404 for non-existent setting', function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/v1/app-settings/999');

            $response->assertStatus(404);
        });

        it('includes all fields and relationships', function () {
            Sanctum::actingAs($this->user);
            
            $setting = AppSetting::factory()
                ->forOrganization($this->organization)
                ->complete()
                ->create();

            $response = $this->getJson("/api/v1/app-settings/{$setting->id}");

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            'id',
                            'name',
                            'slogan',
                            'primary_color',
                            'secondary_color',
                            'locale',
                            'custom_js',
                            'favicon_path',
                            'organization_id',
                            'created_at',
                            'updated_at',
                            'organization',
                            'logo_url',
                            'favicon_url',
                            'has_logo',
                            'has_favicon',
                            'effective_favicon',
                            'theme_colors'
                        ]
                    ]);
        });

        it('shows minimal settings correctly', function () {
            Sanctum::actingAs($this->user);
            
            $setting = AppSetting::factory()
                ->forOrganization($this->organization)
                ->minimal()
                ->create();

            $response = $this->getJson("/api/v1/app-settings/{$setting->id}");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'name' => 'Simple Coop',
                            'slogan' => null,
                            'primary_color' => null,
                            'secondary_color' => null,
                            'custom_js' => null,
                            'favicon_path' => null,
                            'has_favicon' => false,
                            'theme_colors' => [
                                'primary' => null,
                                'secondary' => null,
                            ]
                        ]
                    ]);
        });

        it('shows settings with special characters', function () {
            Sanctum::actingAs($this->user);
            
            $setting = AppSetting::factory()
                ->forOrganization($this->organization)
                ->withSpecialCharacters()
                ->create();

            $response = $this->getJson("/api/v1/app-settings/{$setting->id}");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'name' => 'Energía & Sostenibilidad S.L.',
                            'slogan' => 'Más energía, menos CO₂ - ¡Únete!',
                        ]
                    ]);
        });

        it('requires authentication', function () {
            $setting = AppSetting::factory()->forOrganization($this->organization)->create();

            $response = $this->getJson("/api/v1/app-settings/{$setting->id}");

            $response->assertStatus(401);
        });
    });

    describe('Model caching behavior', function () {
        it('caches settings correctly using forOrg method', function () {
            $setting = AppSetting::factory()->forOrganization($this->organization)->create([
                'name' => 'Cached Setting'
            ]);

            // First call should hit database
            $result1 = AppSetting::forOrg($this->organization->id);
            expect($result1->get('name'))->toBe('Cached Setting');

            // Clear cache to test the cache mechanism fresh
            AppSetting::clearCache($this->organization->id);

            // Cache again
            $cached = AppSetting::forOrg($this->organization->id);
            expect($cached->get('name'))->toBe('Cached Setting');

            // Update the setting directly in database without triggering model events
            \DB::table('app_settings')
                ->where('id', $setting->id)
                ->update(['name' => 'Updated Setting']);

            // Should still return cached value
            $stillCached = AppSetting::forOrg($this->organization->id);
            expect($stillCached->get('name'))->toBe('Cached Setting');

            // Clear cache
            AppSetting::clearCache($this->organization->id);

            // Should get fresh data from database
            $fresh = AppSetting::forOrg($this->organization->id);
            expect($fresh->get('name'))->toBe('Updated Setting');
        });

        it('returns default settings when no custom settings exist', function () {
            $result = AppSetting::forOrg($this->organization->id);

            expect($result->get('name'))->toBe('OpenEnergyCoop');
            expect($result->get('slogan'))->toBe('Energía renovable para todos');
            expect($result->get('primary_color'))->toBe('#10B981');
            expect($result->get('locale'))->toBe('es');
        });

        it('clears cache automatically when model is saved', function () {
            $setting = AppSetting::factory()->forOrganization($this->organization)->create([
                'name' => 'Original Name'
            ]);

            // Cache the settings
            $cached = AppSetting::forOrg($this->organization->id);
            expect($cached->get('name'))->toBe('Original Name');

            // Update the model (should clear cache automatically)
            $setting->update(['name' => 'Updated Name']);

            // Should get fresh data from database
            $fresh = AppSetting::forOrg($this->organization->id);
            expect($fresh->get('name'))->toBe('Updated Name');
        });

        it('clears cache automatically when model is deleted', function () {
            $setting = AppSetting::factory()->forOrganization($this->organization)->create([
                'name' => 'To Be Deleted'
            ]);

            // Cache the settings
            $cached = AppSetting::forOrg($this->organization->id);
            expect($cached->get('name'))->toBe('To Be Deleted');

            // Delete the model (should clear cache automatically)
            $setting->delete();

            // Should return default settings
            $fresh = AppSetting::forOrg($this->organization->id);
            expect($fresh->get('name'))->toBe('OpenEnergyCoop'); // Default
        });

        it('gets specific setting values using getSetting method', function () {
            AppSetting::factory()->forOrganization($this->organization)->create([
                'name' => 'Test Coop',
                'primary_color' => '#FF0000'
            ]);

            expect(AppSetting::getSetting('name', $this->organization->id))->toBe('Test Coop');
            expect(AppSetting::getSetting('primary_color', $this->organization->id))->toBe('#FF0000');
            expect(AppSetting::getSetting('non_existent', $this->organization->id, 'default'))->toBe('default');
        });

        it('loads settings into Laravel config', function () {
            AppSetting::factory()->forOrganization($this->organization)->create([
                'name' => 'Config Test',
                'locale' => 'fr'
            ]);

            AppSetting::loadIntoConfig($this->organization->id);

            expect(config('appsettings.name'))->toBe('Config Test');
            expect(config('appsettings.locale'))->toBe('fr');
        });

        it('warms cache correctly', function () {
            $setting = AppSetting::factory()->forOrganization($this->organization)->create([
                'name' => 'Warm Cache Test'
            ]);

            // Warm the cache
            $warmed = AppSetting::warmCache($this->organization->id);

            expect($warmed->get('name'))->toBe('Warm Cache Test');

            // Verify it's actually cached by checking if subsequent calls are fast
            $cached = AppSetting::forOrg($this->organization->id);
            expect($cached->get('name'))->toBe('Warm Cache Test');
        });

        it('gets organizations with custom settings', function () {
            $org1 = Organization::factory()->create();
            $org2 = Organization::factory()->create();
            $org3 = Organization::factory()->create();

            // Create settings for org1 and org2, but not org3
            AppSetting::factory()->forOrganization($org1)->create();
            AppSetting::factory()->forOrganization($org2)->create();

            $orgsWithSettings = AppSetting::getOrganizationsWithSettings();

            expect($orgsWithSettings)->toHaveCount(2);
            expect($orgsWithSettings->contains($org1->id))->toBe(true);
            expect($orgsWithSettings->contains($org2->id))->toBe(true);
            expect($orgsWithSettings->contains($org3->id))->toBe(false);
        });

        it('handles global settings correctly', function () {
            $setting = AppSetting::factory()->forOrganization($this->organization)->create([
                'name' => 'Global Test'
            ]);

            $global = AppSetting::global();

            expect($global->get('name'))->toBe('Global Test');
        });

        it('returns default global settings when no settings exist', function () {
            $global = AppSetting::global();

            expect($global->get('name'))->toBe('OpenEnergyCoop');
            expect($global->get('slogan'))->toBe('Energía renovable para todos');
        });
    });

    describe('Model business logic', function () {
        it('has correct fillable attributes', function () {
            $fillable = (new AppSetting())->getFillable();

            expect($fillable)->toContain('organization_id');
            expect($fillable)->toContain('name');
            expect($fillable)->toContain('slogan');
            expect($fillable)->toContain('primary_color');
            expect($fillable)->toContain('secondary_color');
            expect($fillable)->toContain('locale');
            expect($fillable)->toContain('custom_js');
            expect($fillable)->toContain('favicon_path');
        });

        it('belongs to organization', function () {
            $setting = AppSetting::factory()->forOrganization($this->organization)->create();

            expect($setting->organization)->toBeInstanceOf(Organization::class);
            expect($setting->organization->id)->toBe($this->organization->id);
        });

        it('has correct default settings constants', function () {
            expect(AppSetting::DEFAULT_SETTINGS['name'])->toBe('OpenEnergyCoop');
            expect(AppSetting::DEFAULT_SETTINGS['slogan'])->toBe('Energía renovable para todos');
            expect(AppSetting::DEFAULT_SETTINGS['primary_color'])->toBe('#10B981');
            expect(AppSetting::DEFAULT_SETTINGS['secondary_color'])->toBe('#059669');
            expect(AppSetting::DEFAULT_SETTINGS['locale'])->toBe('es');
        });

        it('has correct cache duration constant', function () {
            expect(AppSetting::CACHE_DURATION)->toBe(30);
        });

        it('registers media collections correctly', function () {
            $setting = AppSetting::factory()->forOrganization($this->organization)->create();

            // Test that media collections are available
            expect($setting->getFirstMediaUrl('logo'))->toBe('');
            expect($setting->getFirstMediaUrl('favicon'))->toBe('');
        });
    });

    describe('Edge cases and validation', function () {
        it('handles settings with long content', function () {
            Sanctum::actingAs($this->user);
            
            $setting = AppSetting::factory()
                ->forOrganization($this->organization)
                ->withLongContent()
                ->create();

            $response = $this->getJson("/api/v1/app-settings/{$setting->id}");

            $response->assertStatus(200);
            expect(strlen($response->json('data.name')))->toBeGreaterThan(50);
            expect(strlen($response->json('data.custom_js')))->toBeGreaterThan(1000);
        });

        it('handles empty string values', function () {
            Sanctum::actingAs($this->user);
            
            $setting = AppSetting::factory()
                ->forOrganization($this->organization)
                ->withEmptyValues()
                ->create();

            $response = $this->getJson("/api/v1/app-settings/{$setting->id}");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'name' => '',
                            'slogan' => '',
                            'primary_color' => '',
                            'secondary_color' => '',
                            'custom_js' => '',
                            'favicon_path' => '',
                        ]
                    ]);
        });

        it('handles null organization gracefully', function () {
            Sanctum::actingAs($this->user);
            
            // Even though the migration requires organization_id, let's test the model logic
            $global = AppSetting::global();
            expect($global)->toBeInstanceOf(\Illuminate\Support\Collection::class);
        });

        it('handles cache miss scenarios', function () {
            // Clear all cache
            Cache::flush();

            $setting = AppSetting::factory()->forOrganization($this->organization)->create([
                'name' => 'Cache Miss Test'
            ]);

            // This should work even with empty cache
            $result = AppSetting::forOrg($this->organization->id);
            expect($result->get('name'))->toBe('Cache Miss Test');
        });

        it('handles multiple cache operations', function () {
            $org1 = Organization::factory()->create();
            $org2 = Organization::factory()->create();

            AppSetting::factory()->forOrganization($org1)->create(['name' => 'Org 1 Settings']);
            AppSetting::factory()->forOrganization($org2)->create(['name' => 'Org 2 Settings']);

            // Cache both
            $result1 = AppSetting::forOrg($org1->id);
            $result2 = AppSetting::forOrg($org2->id);

            expect($result1->get('name'))->toBe('Org 1 Settings');
            expect($result2->get('name'))->toBe('Org 2 Settings');

            // Clear specific cache
            AppSetting::clearCache($org1->id);

            // Org1 should get fresh data, Org2 should still be cached
            $fresh1 = AppSetting::forOrg($org1->id);
            $cached2 = AppSetting::forOrg($org2->id);

            expect($fresh1->get('name'))->toBe('Org 1 Settings');
            expect($cached2->get('name'))->toBe('Org 2 Settings');
        });
    });
});
