<?php

use App\Models\UserSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean up any existing data
    UserSettings::query()->delete();
    
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

describe('UserSettingsController', function () {
    describe('Show endpoint', function () {
        it('can get user settings', function () {
            Sanctum::actingAs($this->user);
            
            // Create settings for the user
            $settings = UserSettings::factory()->forUser($this->user)->create([
                'language' => 'es',
                'theme' => 'dark',
                'notifications_enabled' => true,
            ]);

            $response = $this->getJson('/api/v1/user-settings');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            'id',
                            'language',
                            'timezone',
                            'theme',
                            'notifications_enabled',
                            'email_notifications',
                            'push_notifications',
                            'sms_notifications',
                            'marketing_emails',
                            'newsletter_subscription',
                            'privacy_level',
                            'profile_visibility',
                            'show_achievements',
                            'show_statistics',
                            'show_activity',
                            'date_format',
                            'time_format',
                            'currency',
                            'measurement_unit',
                            'energy_unit',
                            'custom_settings',
                            'created_at',
                            'updated_at'
                        ]
                    ])
                    ->assertJson([
                        'data' => [
                            'language' => 'es',
                            'theme' => 'dark',
                            'notifications_enabled' => true,
                        ]
                    ]);
        });

        it('creates default settings if none exist', function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/v1/user-settings');

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'language' => 'es',
                            'timezone' => 'Europe/Madrid',
                            'theme' => 'light',
                            'notifications_enabled' => true,
                            'email_notifications' => true,
                            'push_notifications' => true,
                            'sms_notifications' => false,
                            'marketing_emails' => true,
                            'newsletter_subscription' => true,
                            'privacy_level' => 'public',
                            'profile_visibility' => 'public',
                            'show_achievements' => true,
                            'show_statistics' => true,
                            'show_activity' => true,
                            'date_format' => 'd/m/Y',
                            'time_format' => '24',
                            'currency' => 'EUR',
                            'measurement_unit' => 'metric',
                            'energy_unit' => 'kWh',
                        ]
                    ]);

            // Verify settings were created in database
            $this->assertDatabaseHas('user_settings', [
                'user_id' => $this->user->id,
                'language' => 'es',
                'theme' => 'light',
            ]);
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/v1/user-settings');
            $response->assertStatus(401);
        });
    });

    describe('Update endpoint', function () {
        it('can update user settings', function () {
            Sanctum::actingAs($this->user);
            
            // Create initial settings
            $settings = UserSettings::factory()->forUser($this->user)->create();

            $updateData = [
                'language' => 'en',
                'theme' => 'dark',
                'timezone' => 'Europe/London',
                'notifications_enabled' => false,
                'email_notifications' => false,
                'privacy_level' => 'private',
                'show_achievements' => false,
                'custom_settings' => ['dashboard_layout' => 'list'],
            ];

            $response = $this->putJson('/api/v1/user-settings', $updateData);

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            'id',
                            'language',
                            'theme',
                            'timezone',
                            'notifications_enabled',
                            'email_notifications',
                            'privacy_level',
                            'show_achievements',
                            'custom_settings'
                        ],
                        'message'
                    ])
                    ->assertJson([
                        'data' => [
                            'language' => 'en',
                            'theme' => 'dark',
                            'timezone' => 'Europe/London',
                            'notifications_enabled' => false,
                            'email_notifications' => false,
                            'privacy_level' => 'private',
                            'show_achievements' => false,
                            'custom_settings' => ['dashboard_layout' => 'list'],
                        ],
                        'message' => 'Configuraciones actualizadas exitosamente'
                    ]);

            // Verify database was updated
            $settings->refresh();
            expect($settings->language)->toBe('en');
            expect($settings->theme)->toBe('dark');
            expect($settings->notifications_enabled)->toBe(false);
            expect($settings->privacy_level)->toBe('private');
        });

        it('can partially update settings', function () {
            Sanctum::actingAs($this->user);
            
            $settings = UserSettings::factory()->forUser($this->user)->create([
                'language' => 'es',
                'theme' => 'light',
                'notifications_enabled' => true,
            ]);

            $response = $this->putJson('/api/v1/user-settings', [
                'theme' => 'dark',
            ]);

            $response->assertStatus(200);
            
            $settings->refresh();
            expect($settings->theme)->toBe('dark');
            expect($settings->language)->toBe('es'); // Should remain unchanged
            expect($settings->notifications_enabled)->toBe(true); // Should remain unchanged
        });

        it('validates language field', function () {
            Sanctum::actingAs($this->user);

            $response = $this->putJson('/api/v1/user-settings', [
                'language' => 'invalid_lang',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['language']);
        });

        it('validates timezone field', function () {
            Sanctum::actingAs($this->user);

            $response = $this->putJson('/api/v1/user-settings', [
                'timezone' => 'Invalid/Timezone',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['timezone']);
        });

        it('validates theme field', function () {
            Sanctum::actingAs($this->user);

            $response = $this->putJson('/api/v1/user-settings', [
                'theme' => 'invalid_theme',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['theme']);
        });

        it('validates privacy_level field', function () {
            Sanctum::actingAs($this->user);

            $response = $this->putJson('/api/v1/user-settings', [
                'privacy_level' => 'invalid_level',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['privacy_level']);
        });

        it('validates time_format field', function () {
            Sanctum::actingAs($this->user);

            $response = $this->putJson('/api/v1/user-settings', [
                'time_format' => 'invalid',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['time_format']);
        });

        it('validates currency field', function () {
            Sanctum::actingAs($this->user);

            $response = $this->putJson('/api/v1/user-settings', [
                'currency' => 'INVALID', // Must be exactly 3 characters
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['currency']);
        });

        it('creates settings if none exist', function () {
            Sanctum::actingAs($this->user);

            $response = $this->putJson('/api/v1/user-settings', [
                'language' => 'en',
                'theme' => 'dark',
            ]);

            $response->assertStatus(200);
            
            $this->assertDatabaseHas('user_settings', [
                'user_id' => $this->user->id,
                'language' => 'en',
                'theme' => 'dark',
            ]);
        });

        it('requires authentication', function () {
            $response = $this->putJson('/api/v1/user-settings', [
                'language' => 'en',
            ]);

            $response->assertStatus(401);
        });
    });

    describe('Notifications endpoint', function () {
        it('can get notification settings', function () {
            Sanctum::actingAs($this->user);
            
            UserSettings::factory()->forUser($this->user)->create([
                'notifications_enabled' => true,
                'email_notifications' => false,
                'push_notifications' => true,
                'sms_notifications' => false,
                'marketing_emails' => true,
                'newsletter_subscription' => false,
            ]);

            $response = $this->getJson('/api/v1/user-settings/notifications');

            $response->assertStatus(200)
                    ->assertJson([
                        'notifications_enabled' => true,
                        'email_notifications' => false,
                        'push_notifications' => true,
                        'sms_notifications' => false,
                        'marketing_emails' => true,
                        'newsletter_subscription' => false,
                    ]);
        });

        it('returns default notification settings if none exist', function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/v1/user-settings/notifications');

            $response->assertStatus(200)
                    ->assertJson([
                        'notifications_enabled' => true,
                        'email_notifications' => true,
                        'push_notifications' => true,
                        'sms_notifications' => false,
                        'marketing_emails' => true,
                        'newsletter_subscription' => true,
                    ]);
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/v1/user-settings/notifications');
            $response->assertStatus(401);
        });
    });

    describe('Update Notifications endpoint', function () {
        it('can update notification settings', function () {
            Sanctum::actingAs($this->user);
            
            $settings = UserSettings::factory()->forUser($this->user)->create();

            $updateData = [
                'notifications_enabled' => false,
                'email_notifications' => false,
                'push_notifications' => false,
                'marketing_emails' => false,
            ];

            $response = $this->putJson('/api/v1/user-settings/notifications', $updateData);

            $response->assertStatus(200)
                    ->assertJson(['message' => 'Configuración de notificaciones actualizada exitosamente']);

            $settings->refresh();
            expect($settings->notifications_enabled)->toBe(false);
            expect($settings->email_notifications)->toBe(false);
            expect($settings->push_notifications)->toBe(false);
            expect($settings->marketing_emails)->toBe(false);
        });

        it('can partially update notification settings', function () {
            Sanctum::actingAs($this->user);
            
            $settings = UserSettings::factory()->forUser($this->user)->notificationsEnabled()->create();

            $response = $this->putJson('/api/v1/user-settings/notifications', [
                'email_notifications' => false,
            ]);

            $response->assertStatus(200);
            
            $settings->refresh();
            expect($settings->email_notifications)->toBe(false);
            expect($settings->notifications_enabled)->toBe(true); // Should remain unchanged
            expect($settings->push_notifications)->toBe(true); // Should remain unchanged
        });

        it('validates boolean fields', function () {
            Sanctum::actingAs($this->user);

            $response = $this->putJson('/api/v1/user-settings/notifications', [
                'notifications_enabled' => 'invalid_boolean',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['notifications_enabled']);
        });

        it('creates settings if none exist', function () {
            Sanctum::actingAs($this->user);

            $response = $this->putJson('/api/v1/user-settings/notifications', [
                'notifications_enabled' => false,
            ]);

            $response->assertStatus(200);
            
            $this->assertDatabaseHas('user_settings', [
                'user_id' => $this->user->id,
                'notifications_enabled' => false,
            ]);
        });

        it('requires authentication', function () {
            $response = $this->putJson('/api/v1/user-settings/notifications', [
                'notifications_enabled' => false,
            ]);

            $response->assertStatus(401);
        });
    });

    describe('Privacy endpoint', function () {
        it('can get privacy settings', function () {
            Sanctum::actingAs($this->user);
            
            UserSettings::factory()->forUser($this->user)->create([
                'privacy_level' => 'private',
                'profile_visibility' => 'registered',
                'show_achievements' => false,
                'show_statistics' => true,
                'show_activity' => false,
            ]);

            $response = $this->getJson('/api/v1/user-settings/privacy');

            $response->assertStatus(200)
                    ->assertJson([
                        'privacy_level' => 'private',
                        'profile_visibility' => 'registered',
                        'show_achievements' => false,
                        'show_statistics' => true,
                        'show_activity' => false,
                    ]);
        });

        it('returns default privacy settings if none exist', function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/v1/user-settings/privacy');

            $response->assertStatus(200)
                    ->assertJson([
                        'privacy_level' => 'public',
                        'profile_visibility' => 'public',
                        'show_achievements' => true,
                        'show_statistics' => true,
                        'show_activity' => true,
                    ]);
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/v1/user-settings/privacy');
            $response->assertStatus(401);
        });
    });

    describe('Update Privacy endpoint', function () {
        it('can update privacy settings', function () {
            Sanctum::actingAs($this->user);
            
            $settings = UserSettings::factory()->forUser($this->user)->create();

            $updateData = [
                'privacy_level' => 'private',
                'profile_visibility' => 'private',
                'show_achievements' => false,
                'show_statistics' => false,
                'show_activity' => false,
            ];

            $response = $this->putJson('/api/v1/user-settings/privacy', $updateData);

            $response->assertStatus(200)
                    ->assertJson(['message' => 'Configuración de privacidad actualizada exitosamente']);

            $settings->refresh();
            expect($settings->privacy_level)->toBe('private');
            expect($settings->profile_visibility)->toBe('private');
            expect($settings->show_achievements)->toBe(false);
            expect($settings->show_statistics)->toBe(false);
            expect($settings->show_activity)->toBe(false);
        });

        it('validates privacy_level field', function () {
            Sanctum::actingAs($this->user);

            $response = $this->putJson('/api/v1/user-settings/privacy', [
                'privacy_level' => 'invalid_level',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['privacy_level']);
        });

        it('validates profile_visibility field', function () {
            Sanctum::actingAs($this->user);

            $response = $this->putJson('/api/v1/user-settings/privacy', [
                'profile_visibility' => 'invalid_visibility',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['profile_visibility']);
        });

        it('requires authentication', function () {
            $response = $this->putJson('/api/v1/user-settings/privacy', [
                'privacy_level' => 'private',
            ]);

            $response->assertStatus(401);
        });
    });

    describe('Reset endpoint', function () {
        it('can reset settings to defaults', function () {
            Sanctum::actingAs($this->user);
            
            // Create custom settings
            $settings = UserSettings::factory()->forUser($this->user)->create([
                'language' => 'en',
                'theme' => 'dark',
                'notifications_enabled' => false,
                'privacy_level' => 'private',
                'custom_settings' => ['some' => 'custom_data'],
            ]);

            $response = $this->postJson('/api/v1/user-settings/reset');

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'language' => 'es',
                            'timezone' => 'Europe/Madrid',
                            'theme' => 'light',
                            'notifications_enabled' => true,
                            'email_notifications' => true,
                            'push_notifications' => true,
                            'sms_notifications' => false,
                            'marketing_emails' => true,
                            'newsletter_subscription' => true,
                            'privacy_level' => 'public',
                            'profile_visibility' => 'public',
                            'show_achievements' => true,
                            'show_statistics' => true,
                            'show_activity' => true,
                            'date_format' => 'd/m/Y',
                            'time_format' => '24',
                            'currency' => 'EUR',
                            'measurement_unit' => 'metric',
                            'energy_unit' => 'kWh',
                            'custom_settings' => null,
                        ],
                        'message' => 'Configuraciones restablecidas exitosamente'
                    ]);

            // Verify database was reset
            $settings->refresh();
            expect($settings->language)->toBe('es');
            expect($settings->theme)->toBe('light');
            expect($settings->notifications_enabled)->toBe(true);
            expect($settings->privacy_level)->toBe('public');
            expect($settings->custom_settings)->toBeNull();
        });

        it('creates default settings if none exist', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/user-settings/reset');

            $response->assertStatus(200);
            
            $this->assertDatabaseHas('user_settings', [
                'user_id' => $this->user->id,
                'language' => 'es',
                'theme' => 'light',
                'notifications_enabled' => true,
            ]);
        });

        it('requires authentication', function () {
            $response = $this->postJson('/api/v1/user-settings/reset');
            $response->assertStatus(401);
        });
    });

    describe('Model business logic', function () {
        it('can create settings with default values', function () {
            $settings = UserSettings::getForUser($this->user->id);

            expect($settings->user_id)->toBe($this->user->id);
            expect($settings->language)->toBe('es');
            expect($settings->theme)->toBe('light');
            expect($settings->notifications_enabled)->toBe(true);
            expect($settings->currency)->toBe('EUR');
        });

        it('can check notification methods', function () {
            $settings = UserSettings::factory()->create([
                'notifications_enabled' => true,
                'email_notifications' => true,
                'push_notifications' => false,
                'sms_notifications' => true,
                'marketing_emails' => false,
                'newsletter_subscription' => true,
            ]);

            expect($settings->hasNotificationsEnabled())->toBe(true);
            expect($settings->hasEmailNotificationsEnabled())->toBe(true);
            expect($settings->hasPushNotificationsEnabled())->toBe(false);
            expect($settings->hasSmsNotificationsEnabled())->toBe(true);
            expect($settings->hasMarketingEmailsEnabled())->toBe(false);
            expect($settings->hasNewsletterSubscriptionEnabled())->toBe(true);
        });

        it('email notifications depend on general notifications being enabled', function () {
            $settings = UserSettings::factory()->create([
                'notifications_enabled' => false,
                'email_notifications' => true, // This should be ignored
            ]);

            expect($settings->hasEmailNotificationsEnabled())->toBe(false);
        });

        it('can check privacy methods', function () {
            $publicSettings = UserSettings::factory()->publicPrivacy()->create();
            $privateSettings = UserSettings::factory()->privatePrivacy()->create();

            expect($publicSettings->isProfilePublic())->toBe(true);
            expect($publicSettings->isProfilePrivate())->toBe(false);
            expect($publicSettings->shouldShowAchievements())->toBe(true);

            expect($privateSettings->isProfilePublic())->toBe(false);
            expect($privateSettings->isProfilePrivate())->toBe(true);
            expect($privateSettings->shouldShowAchievements())->toBe(false);
        });

        it('can check theme preferences', function () {
            $darkSettings = UserSettings::factory()->darkTheme()->create();
            $lightSettings = UserSettings::factory()->lightTheme()->create();
            $autoSettings = UserSettings::factory()->create(['theme' => 'auto']);

            expect($darkSettings->prefersDarkTheme())->toBe(true);
            expect($darkSettings->prefersLightTheme())->toBe(false);
            expect($darkSettings->hasAutoTheme())->toBe(false);

            expect($lightSettings->prefersDarkTheme())->toBe(false);
            expect($lightSettings->prefersLightTheme())->toBe(true);

            expect($autoSettings->hasAutoTheme())->toBe(true);
        });

        it('can format dates and times according to preferences', function () {
            $settings = UserSettings::factory()->create([
                'date_format' => 'd/m/Y',
                'time_format' => '24',
            ]);

            $date = new \DateTime('2024-12-25 14:30:00');
            
            expect($settings->formatDate($date))->toBe('25/12/2024');
            expect($settings->formatTime($date))->toBe('14:30');
            expect($settings->formatDateTime($date))->toBe('25/12/2024 14:30');
        });

        it('can format time in 12-hour format', function () {
            $settings = UserSettings::factory()->twelveHourFormat()->create();

            $date = new \DateTime('2024-12-25 14:30:00');
            
            expect($settings->formatTime($date))->toBe('2:30 PM');
        });

        it('can get notification and privacy settings as arrays', function () {
            $settings = UserSettings::factory()->create([
                'notifications_enabled' => true,
                'email_notifications' => false,
                'privacy_level' => 'private',
                'show_achievements' => false,
            ]);

            $notificationSettings = $settings->getNotificationSettings();
            expect($notificationSettings)->toBeArray();
            expect($notificationSettings['notifications_enabled'])->toBe(true);
            expect($notificationSettings['email_notifications'])->toBe(false);

            $privacySettings = $settings->getPrivacySettings();
            expect($privacySettings)->toBeArray();
            expect($privacySettings['privacy_level'])->toBe('private');
            expect($privacySettings['show_achievements'])->toBe(false);
        });

        it('can update notification settings using method', function () {
            $settings = UserSettings::factory()->create();

            $result = $settings->updateNotificationSettings([
                'notifications_enabled' => false,
                'email_notifications' => false,
            ]);

            expect($result)->toBe(true);
            
            $settings->refresh();
            expect($settings->notifications_enabled)->toBe(false);
            expect($settings->email_notifications)->toBe(false);
        });

        it('can update privacy settings using method', function () {
            $settings = UserSettings::factory()->create();

            $result = $settings->updatePrivacySettings([
                'privacy_level' => 'private',
                'show_achievements' => false,
            ]);

            expect($result)->toBe(true);
            
            $settings->refresh();
            expect($settings->privacy_level)->toBe('private');
            expect($settings->show_achievements)->toBe(false);
        });

        it('can handle custom settings', function () {
            $settings = UserSettings::factory()->create();

            // Set custom setting
            $result = $settings->setCustomSetting('dashboard_layout', 'grid');
            expect($result)->toBe(true);

            // Get custom setting
            $value = $settings->getCustomSetting('dashboard_layout');
            expect($value)->toBe('grid');

            // Get non-existent setting with default
            $defaultValue = $settings->getCustomSetting('non_existent', 'default');
            expect($defaultValue)->toBe('default');

            // Remove custom setting
            $result = $settings->removeCustomSetting('dashboard_layout');
            expect($result)->toBe(true);
            
            $settings->refresh();
            $value = $settings->getCustomSetting('dashboard_layout');
            expect($value)->toBeNull();
        });

        it('can reset to defaults', function () {
            $settings = UserSettings::factory()->create([
                'language' => 'en',
                'theme' => 'dark',
                'notifications_enabled' => false,
            ]);

            $result = $settings->resetToDefaults();
            expect($result)->toBe(true);

            $settings->refresh();
            expect($settings->language)->toBe('es');
            expect($settings->theme)->toBe('light');
            expect($settings->notifications_enabled)->toBe(true);
        });
    });

    describe('Edge cases and validation', function () {
        it('handles invalid timezone gracefully', function () {
            Sanctum::actingAs($this->user);

            $response = $this->putJson('/api/v1/user-settings', [
                'timezone' => 'Not/A/Real/Timezone',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['timezone']);
        });

        it('handles empty custom settings', function () {
            Sanctum::actingAs($this->user);

            $response = $this->putJson('/api/v1/user-settings', [
                'custom_settings' => [],
            ]);

            $response->assertStatus(200);
            
            $settings = UserSettings::forUser($this->user->id)->first();
            expect($settings->custom_settings)->toBe([]);
        });

        it('handles null custom settings', function () {
            Sanctum::actingAs($this->user);

            $response = $this->putJson('/api/v1/user-settings', [
                'custom_settings' => null,
            ]);

            $response->assertStatus(200);
            
            $settings = UserSettings::forUser($this->user->id)->first();
            expect($settings->custom_settings)->toBeNull();
        });

        it('handles complex custom settings object', function () {
            Sanctum::actingAs($this->user);

            $complexSettings = [
                'dashboard' => [
                    'layout' => 'grid',
                    'widgets' => ['energy', 'stats', 'achievements'],
                    'refresh_interval' => 30
                ],
                'charts' => [
                    'default_period' => 'month',
                    'show_comparisons' => true,
                    'colors' => ['#ff0000', '#00ff00', '#0000ff']
                ]
            ];

            $response = $this->putJson('/api/v1/user-settings', [
                'custom_settings' => $complexSettings,
            ]);

            $response->assertStatus(200);
            
            $settings = UserSettings::forUser($this->user->id)->first();
            expect($settings->custom_settings)->toBe($complexSettings);
        });

        it('validates currency length', function () {
            Sanctum::actingAs($this->user);

            $response = $this->putJson('/api/v1/user-settings', [
                'currency' => 'EURO', // Too long
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['currency']);

            $response = $this->putJson('/api/v1/user-settings', [
                'currency' => 'EU', // Too short
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['currency']);
        });

        it('handles long date format strings', function () {
            Sanctum::actingAs($this->user);

            $longFormat = str_repeat('d/m/Y ', 10); // Very long format

            $response = $this->putJson('/api/v1/user-settings', [
                'date_format' => $longFormat,
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['date_format']);
        });

        it('allows valid energy units', function () {
            Sanctum::actingAs($this->user);

            foreach (UserSettings::ENERGY_UNITS as $unit) {
                $response = $this->putJson('/api/v1/user-settings', [
                    'energy_unit' => $unit,
                ]);

                $response->assertStatus(200);
            }
        });

        it('ensures user can only access their own settings', function () {
            // User 1 creates settings
            $user1Settings = UserSettings::factory()->forUser($this->user)->create([
                'language' => 'es',
                'theme' => 'dark',
            ]);

            // User 2 creates different settings
            $user2Settings = UserSettings::factory()->forUser($this->otherUser)->create([
                'language' => 'en',
                'theme' => 'light',
            ]);

            // User 1 authenticates and gets their settings
            Sanctum::actingAs($this->user);
            $response = $this->getJson('/api/v1/user-settings');

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'language' => 'es',
                            'theme' => 'dark',
                        ]
                    ]);

            // User 2 authenticates and gets their settings
            Sanctum::actingAs($this->otherUser);
            $response = $this->getJson('/api/v1/user-settings');

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'language' => 'en',
                            'theme' => 'light',
                        ]
                    ]);
        });
    });
});
