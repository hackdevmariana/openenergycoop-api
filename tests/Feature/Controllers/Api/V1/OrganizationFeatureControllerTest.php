<?php

use App\Models\OrganizationFeature;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean up any existing data
    OrganizationFeature::query()->delete();
    
    $this->user = User::factory()->create();
    $this->organization = Organization::factory()->create();
    $this->otherOrganization = Organization::factory()->create();
});

describe('OrganizationFeatureController', function () {
    
    // NOTE: The controller expects a different schema than what exists in the database.
    // The controller expects: name, description, config, is_enabled
    // But the migration has: feature_key, enabled_dashboard, enabled_web, notes
    // For now, we'll create tests based on the actual model structure.
    
    describe('Basic model functionality', function () {
        it('can create organization features', function () {
            $feature = OrganizationFeature::factory()->forOrganization($this->organization)->create();

            expect($feature)->toBeInstanceOf(OrganizationFeature::class);
            expect($feature->organization_id)->toBe($this->organization->id);
            expect($feature->feature_key)->not->toBeNull();
            expect($feature->enabled_dashboard)->toBeIn([true, false]);
            expect($feature->enabled_web)->toBeIn([true, false]);
        });

        it('has relationship with organization', function () {
            $feature = OrganizationFeature::factory()->forOrganization($this->organization)->create();

            expect($feature->organization)->toBeInstanceOf(Organization::class);
            expect($feature->organization->id)->toBe($this->organization->id);
        });

        it('can have dashboard and web enabled states', function () {
            $enabledFeature = OrganizationFeature::factory()->enabled()->create();
            $disabledFeature = OrganizationFeature::factory()->disabled()->create();

            expect($enabledFeature->enabled_dashboard)->toBe(true);
            expect($enabledFeature->enabled_web)->toBe(true);
            expect($disabledFeature->enabled_dashboard)->toBe(false);
            expect($disabledFeature->enabled_web)->toBe(false);
        });

        it('can be dashboard-only or web-only', function () {
            $dashboardOnly = OrganizationFeature::factory()->dashboardOnly()->create();
            $webOnly = OrganizationFeature::factory()->webOnly()->create();

            expect($dashboardOnly->enabled_dashboard)->toBe(true);
            expect($dashboardOnly->enabled_web)->toBe(false);
            expect($webOnly->enabled_dashboard)->toBe(false);
            expect($webOnly->enabled_web)->toBe(true);
        });

        it('can have notes', function () {
            $notes = 'This feature requires special configuration';
            $feature = OrganizationFeature::factory()->withNotes($notes)->create();

            expect($feature->notes)->toBe($notes);
        });

        it('can have specific feature keys', function () {
            $featureKey = 'energy_management';
            $feature = OrganizationFeature::factory()->withFeature($featureKey)->create();

            expect($feature->feature_key)->toBe($featureKey);
        });
    });

    describe('Factory states', function () {
        it('creates energy features correctly', function () {
            $feature = OrganizationFeature::factory()->energyFeature()->create();

            $energyFeatures = ['energy_management', 'solar_panels', 'wind_turbines', 'battery_storage', 'smart_meters'];
            expect($feature->feature_key)->toBeIn($energyFeatures);
        });

        it('creates analytics features correctly', function () {
            $feature = OrganizationFeature::factory()->analyticsFeature()->create();

            $analyticsFeatures = ['consumption_analytics', 'advanced_reporting', 'audit_logs'];
            expect($feature->feature_key)->toBeIn($analyticsFeatures);
        });

        it('creates customer features correctly', function () {
            $feature = OrganizationFeature::factory()->customerFeature()->create();

            $customerFeatures = ['customer_portal', 'mobile_app', 'billing_integration'];
            expect($feature->feature_key)->toBeIn($customerFeatures);
        });

        it('can create multiple features for same organization', function () {
            $features = OrganizationFeature::factory()
                ->count(3)
                ->forOrganization($this->organization)
                ->sequence(
                    ['feature_key' => 'energy_management', 'enabled_dashboard' => true],
                    ['feature_key' => 'solar_panels', 'enabled_web' => true],
                    ['feature_key' => 'smart_meters', 'enabled_dashboard' => true, 'enabled_web' => true]
                )
                ->create();

            expect($features)->toHaveCount(3);
            foreach ($features as $feature) {
                expect($feature->organization_id)->toBe($this->organization->id);
            }
        });
    });

    describe('Data integrity and validation', function () {
        it('maintains proper foreign key relationships', function () {
            $feature = OrganizationFeature::factory()->create([
                'organization_id' => $this->organization->id
            ]);

            // Verify the relationship works
            expect($feature->organization_id)->toBe($this->organization->id);
            expect($feature->organization->id)->toBe($this->organization->id);
        });

        it('handles boolean casting correctly', function () {
            $feature = OrganizationFeature::factory()->create([
                'enabled_dashboard' => 1,  // Integer 1
                'enabled_web' => 0,        // Integer 0
            ]);

            // Should be cast to booleans
            expect($feature->enabled_dashboard)->toBe(true);
            expect($feature->enabled_web)->toBe(false);
            expect(is_bool($feature->enabled_dashboard))->toBe(true);
            expect(is_bool($feature->enabled_web))->toBe(true);
        });

        it('handles null notes gracefully', function () {
            $feature = OrganizationFeature::factory()->create([
                'notes' => null
            ]);

            expect($feature->notes)->toBeNull();
        });

        it('can store long notes', function () {
            $longNotes = str_repeat('This is a very detailed note about the feature configuration. ', 20);
            $feature = OrganizationFeature::factory()->create([
                'notes' => $longNotes
            ]);

            expect($feature->notes)->toBe($longNotes);
            expect(strlen($feature->notes))->toBeGreaterThan(500);
        });

        it('validates unique combinations', function () {
            // Create first feature
            OrganizationFeature::factory()->create([
                'organization_id' => $this->organization->id,
                'feature_key' => 'energy_management'
            ]);

            // Creating another feature with same organization and feature_key should be allowed
            // (based on current migration - no unique constraint)
            $secondFeature = OrganizationFeature::factory()->create([
                'organization_id' => $this->organization->id,
                'feature_key' => 'energy_management'
            ]);

            expect($secondFeature)->toBeInstanceOf(OrganizationFeature::class);
        });
    });

    describe('Business logic', function () {
        it('can enable/disable features separately for dashboard and web', function () {
            $feature = OrganizationFeature::factory()->disabled()->create();

            // Enable dashboard only
            $feature->update(['enabled_dashboard' => true]);
            expect($feature->enabled_dashboard)->toBe(true);
            expect($feature->enabled_web)->toBe(false);

            // Enable web too
            $feature->update(['enabled_web' => true]);
            expect($feature->enabled_dashboard)->toBe(true);
            expect($feature->enabled_web)->toBe(true);

            // Disable dashboard but keep web
            $feature->update(['enabled_dashboard' => false]);
            expect($feature->enabled_dashboard)->toBe(false);
            expect($feature->enabled_web)->toBe(true);
        });

        it('can filter features by organization', function () {
            // Create features for different organizations
            $org1Features = OrganizationFeature::factory()->count(2)->forOrganization($this->organization)->create();
            $org2Features = OrganizationFeature::factory()->count(3)->forOrganization($this->otherOrganization)->create();

            $org1Results = OrganizationFeature::where('organization_id', $this->organization->id)->get();
            $org2Results = OrganizationFeature::where('organization_id', $this->otherOrganization->id)->get();

            expect($org1Results)->toHaveCount(2);
            expect($org2Results)->toHaveCount(3);
        });

        it('can filter features by enabled status', function () {
            OrganizationFeature::factory()->enabled()->count(2)->create();
            OrganizationFeature::factory()->disabled()->count(1)->create();
            OrganizationFeature::factory()->dashboardOnly()->count(1)->create();

            $dashboardEnabled = OrganizationFeature::where('enabled_dashboard', true)->get();
            $webEnabled = OrganizationFeature::where('enabled_web', true)->get();
            $bothDisabled = OrganizationFeature::where('enabled_dashboard', false)
                ->where('enabled_web', false)
                ->get();

            expect($dashboardEnabled)->toHaveCount(3); // 2 enabled + 1 dashboard-only
            expect($webEnabled)->toHaveCount(2); // 2 enabled
            expect($bothDisabled)->toHaveCount(1); // 1 disabled
        });

        it('can group features by type', function () {
            OrganizationFeature::factory()->withFeature('energy_management')->create();
            OrganizationFeature::factory()->withFeature('solar_panels')->create();
            OrganizationFeature::factory()->withFeature('consumption_analytics')->create();
            OrganizationFeature::factory()->withFeature('customer_portal')->create();

            $allFeatures = OrganizationFeature::all();
            $featureKeys = $allFeatures->pluck('feature_key')->toArray();

            expect($featureKeys)->toContain('energy_management');
            expect($featureKeys)->toContain('solar_panels');
            expect($featureKeys)->toContain('consumption_analytics');
            expect($featureKeys)->toContain('customer_portal');
        });
    });

    describe('Edge cases and scenarios', function () {
        it('handles organization with no features', function () {
            $emptyOrg = Organization::factory()->create();
            $features = OrganizationFeature::where('organization_id', $emptyOrg->id)->get();

            expect($features)->toHaveCount(0);
            expect($features)->toBeEmpty();
        });

        it('handles organization with many features', function () {
            $manyFeatures = OrganizationFeature::factory()
                ->count(20)
                ->forOrganization($this->organization)
                ->create();

            $features = OrganizationFeature::where('organization_id', $this->organization->id)->get();

            expect($features)->toHaveCount(20);
            expect($features->pluck('organization_id')->unique())->toHaveCount(1);
        });

        it('can update feature configurations independently', function () {
            $feature = OrganizationFeature::factory()->create([
                'feature_key' => 'energy_management',
                'enabled_dashboard' => false,
                'enabled_web' => false,
                'notes' => 'Initial configuration'
            ]);

            // Update only dashboard setting
            $feature->update(['enabled_dashboard' => true]);
            expect($feature->fresh()->enabled_dashboard)->toBe(true);
            expect($feature->fresh()->enabled_web)->toBe(false);
            expect($feature->fresh()->notes)->toBe('Initial configuration');

            // Update only notes
            $feature->update(['notes' => 'Updated configuration with special settings']);
            expect($feature->fresh()->notes)->toBe('Updated configuration with special settings');
            expect($feature->fresh()->enabled_dashboard)->toBe(true);
            expect($feature->fresh()->enabled_web)->toBe(false);
        });

        it('maintains data integrity during organization deletion', function () {
            $feature = OrganizationFeature::factory()->forOrganization($this->organization)->create();
            
            // Verify feature exists
            expect(OrganizationFeature::where('id', $feature->id)->exists())->toBe(true);

            // Delete organization (should cascade delete features based on migration)
            $this->organization->delete();

            // Feature should be deleted due to cascade
            expect(OrganizationFeature::where('id', $feature->id)->exists())->toBe(false);
        });

        it('handles special characters in feature keys', function () {
            $specialFeatureKey = 'energy-management_v2.0';
            $feature = OrganizationFeature::factory()->create([
                'feature_key' => $specialFeatureKey
            ]);

            expect($feature->feature_key)->toBe($specialFeatureKey);
        });

        it('can store complex notes with line breaks and special characters', function () {
            $complexNotes = "Feature Configuration:\n- Solar panels: 100 units\n- Max capacity: 500kW\n- Special chars: àáâãäåæçèéêë";
            $feature = OrganizationFeature::factory()->create([
                'notes' => $complexNotes
            ]);

            expect($feature->notes)->toBe($complexNotes);
            expect($feature->notes)->toContain("\n");
            expect($feature->notes)->toContain("àáâãäåæçèéêë");
        });
    });

    // NOTE: Controller tests are omitted because the current controller expects 
    // a completely different schema than what exists in the database.
    // The controller would need to be refactored to match the actual model schema
    // before meaningful controller tests can be written.
    
    describe('Integration notes', function () {
        it('documents the schema mismatch issue', function () {
            // This test serves as documentation of the issue
            $feature = OrganizationFeature::factory()->enabled()->create();
            
            // What the model actually has:
            $actualFields = [
                'organization_id', 'feature_key', 'enabled_dashboard', 'enabled_web', 'notes'
            ];
            
            // What the controller expects:
            $expectedByController = [
                'name', 'description', 'config', 'is_enabled'
            ];
            
            // Verify model has actual fields
            foreach ($actualFields as $field) {
                expect(array_key_exists($field, $feature->getAttributes()))->toBe(true);
            }
            
            // Verify model doesn't have controller-expected fields
            foreach ($expectedByController as $field) {
                expect(array_key_exists($field, $feature->getAttributes()))->toBe(false);
            }
            
            expect(true)->toBe(true); // Test passes to document the issue
        });
    });
});
