<?php

use App\Models\ConsentLog;
use App\Models\User;
use App\Enums\AppEnums;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean up any existing data
    ConsentLog::query()->delete();
    
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    
    $this->validConsentTypes = array_keys(AppEnums::CONSENT_TYPES);
});

describe('ConsentLogController', function () {
    describe('Index endpoint', function () {
        it('can list user consent logs', function () {
            Sanctum::actingAs($this->user);
            
            // Create consent logs for the user
            $consents = ConsentLog::factory()->count(3)->forUser($this->user)->create();
            
            // Create consent logs for another user (should not appear)
            ConsentLog::factory()->count(2)->forUser($this->otherUser)->create();

            $response = $this->getJson('/api/v1/consent-logs');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            '*' => [
                                'id',
                                'consent_type',
                                'consent_given',
                                'version',
                                'purpose',
                                'legal_basis',
                                'data_categories',
                                'retention_period',
                                'third_parties',
                                'withdrawal_method',
                                'ip_address',
                                'user_agent',
                                'metadata',
                                'is_active',
                                'created_at',
                                'updated_at',
                                'revoked_at',
                                'revocation_reason'
                            ]
                        ],
                        'links',
                        'meta'
                    ]);

            expect($response->json('data'))->toHaveCount(3);
        });

        it('can filter by consent type', function () {
            Sanctum::actingAs($this->user);
            
            ConsentLog::factory()->forUser($this->user)->ofType('privacy_policy')->count(2)->create();
            ConsentLog::factory()->forUser($this->user)->ofType('marketing_communications')->count(1)->create();

            $response = $this->getJson('/api/v1/consent-logs?consent_type=privacy_policy');

            $response->assertStatus(200);
            expect($response->json('data'))->toHaveCount(2);
            
            foreach ($response->json('data') as $consent) {
                expect($consent['consent_type'])->toBe('privacy_policy');
            }
        });

        it('can filter active consents only', function () {
            Sanctum::actingAs($this->user);
            
            ConsentLog::factory()->forUser($this->user)->active()->count(2)->create();
            ConsentLog::factory()->forUser($this->user)->revoked()->count(1)->create();

            $response = $this->getJson('/api/v1/consent-logs?active_only=true');

            $response->assertStatus(200);
            expect($response->json('data'))->toHaveCount(2);
            
            foreach ($response->json('data') as $consent) {
                expect($consent['is_active'])->toBe(true);
                expect($consent['revoked_at'])->toBeNull();
            }
        });

        it('orders by creation date descending', function () {
            Sanctum::actingAs($this->user);
            
            $oldConsent = ConsentLog::factory()->forUser($this->user)->create([
                'created_at' => now()->subDays(2)
            ]);
            $newConsent = ConsentLog::factory()->forUser($this->user)->create([
                'created_at' => now()->subDay()
            ]);

            $response = $this->getJson('/api/v1/consent-logs');

            $response->assertStatus(200);
            $data = $response->json('data');
            
            expect($data[0]['id'])->toBe($newConsent->id);
            expect($data[1]['id'])->toBe($oldConsent->id);
        });

        it('paginates results', function () {
            Sanctum::actingAs($this->user);
            
            ConsentLog::factory()->count(25)->forUser($this->user)->create();

            $response = $this->getJson('/api/v1/consent-logs');

            $response->assertStatus(200);
            expect($response->json('data'))->toHaveCount(20); // Default pagination limit
            expect($response->json('meta.total'))->toBe(25);
            expect($response->json('links.next'))->not->toBeNull();
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/v1/consent-logs');
            $response->assertStatus(401);
        });
    });

    describe('Store endpoint', function () {
        it('can create a new consent log', function () {
            Sanctum::actingAs($this->user);
            
            $consentData = [
                'consent_type' => 'privacy_policy',
                'consent_given' => true,
                'version' => '1.0',
                'purpose' => 'Processing personal data for service delivery',
                'legal_basis' => 'Artículo 6.1.a GDPR - Consentimiento',
                'data_categories' => ['personal_data', 'contact_info'],
                'retention_period' => '5 años',
                'third_parties' => ['Google Analytics'],
                'withdrawal_method' => 'Contactar a privacy@example.com',
            ];

            $response = $this->postJson('/api/v1/consent-logs', $consentData);

            $response->assertStatus(201)
                    ->assertJsonStructure([
                        'data' => [
                            'id',
                            'consent_type',
                            'consent_given',
                            'version',
                            'purpose',
                            'legal_basis',
                            'data_categories',
                            'retention_period',
                            'third_parties',
                            'withdrawal_method',
                            'ip_address',
                            'user_agent',
                            'is_active',
                        ],
                        'message'
                    ])
                    ->assertJson([
                        'data' => [
                            'consent_type' => 'privacy_policy',
                            'consent_given' => true,
                            'version' => '1.0',
                            'purpose' => 'Processing personal data for service delivery',
                        ],
                        'message' => 'Consentimiento registrado exitosamente'
                    ]);

            $this->assertDatabaseHas('consent_logs', [
                'user_id' => $this->user->id,
                'consent_type' => 'privacy_policy',
                'consent_given' => true,
                'version' => '1.0',
            ]);
        });

        it('can create minimal consent log', function () {
            Sanctum::actingAs($this->user);
            
            $response = $this->postJson('/api/v1/consent-logs', [
                'consent_type' => 'terms_and_conditions',
                'consent_given' => true,
            ]);

            $response->assertStatus(201);
            
            $this->assertDatabaseHas('consent_logs', [
                'user_id' => $this->user->id,
                'consent_type' => 'terms_and_conditions',
                'consent_given' => true,
            ]);
        });

        it('can record consent denial', function () {
            Sanctum::actingAs($this->user);
            
            $response = $this->postJson('/api/v1/consent-logs', [
                'consent_type' => 'marketing_communications',
                'consent_given' => false,
            ]);

            $response->assertStatus(201);
            
            $consent = ConsentLog::latest()->first();
            expect($consent->consent_given)->toBe(false);
            expect($consent->isActive())->toBe(false); // Should be inactive because consent_given is false
        });

        it('automatically captures IP and user agent', function () {
            Sanctum::actingAs($this->user);
            
            $response = $this->postJson('/api/v1/consent-logs', [
                'consent_type' => 'privacy_policy',
                'consent_given' => true,
            ], [
                'HTTP_USER_AGENT' => 'Test User Agent',
                'REMOTE_ADDR' => '192.168.1.100'
            ]);

            $response->assertStatus(201);
            
            $consent = ConsentLog::latest()->first();
            expect($consent->user_agent)->toContain('Test User Agent');
            expect($consent->ip_address)->not->toBeNull();
        });

        it('validates required fields', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/consent-logs', []);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['consent_type', 'consent_given']);
        });

        it('validates consent type', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/consent-logs', [
                'consent_type' => 'invalid_type',
                'consent_given' => true,
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['consent_type']);
        });

        it('validates field lengths', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/consent-logs', [
                'consent_type' => 'privacy_policy',
                'consent_given' => true,
                'version' => str_repeat('a', 51), // Too long
                'purpose' => str_repeat('a', 501), // Too long
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['version', 'purpose']);
        });

        it('validates array fields', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/consent-logs', [
                'consent_type' => 'privacy_policy',
                'consent_given' => true,
                'data_categories' => 'not_an_array',
                'third_parties' => 'not_an_array',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['data_categories', 'third_parties']);
        });

        it('requires authentication', function () {
            $response = $this->postJson('/api/v1/consent-logs', [
                'consent_type' => 'privacy_policy',
                'consent_given' => true,
            ]);

            $response->assertStatus(401);
        });
    });

    describe('Show endpoint', function () {
        it('can show user consent log', function () {
            Sanctum::actingAs($this->user);
            
            $consent = ConsentLog::factory()->forUser($this->user)->create([
                'consent_type' => 'privacy_policy',
                'consent_given' => true,
                'version' => '1.0',
            ]);

            $response = $this->getJson("/api/v1/consent-logs/{$consent->id}");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'id' => $consent->id,
                            'consent_type' => 'privacy_policy',
                            'consent_given' => true,
                            'version' => '1.0',
                        ]
                    ]);
        });

        it('prevents access to other users consent logs', function () {
            Sanctum::actingAs($this->user);
            
            $otherUserConsent = ConsentLog::factory()->forUser($this->otherUser)->create();

            $response = $this->getJson("/api/v1/consent-logs/{$otherUserConsent->id}");

            $response->assertStatus(404)
                    ->assertJson(['message' => 'Consentimiento no encontrado']);
        });

        it('returns 404 for non-existent consent', function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/v1/consent-logs/999999');

            $response->assertStatus(404);
        });

        it('requires authentication', function () {
            $consent = ConsentLog::factory()->create();

            $response = $this->getJson("/api/v1/consent-logs/{$consent->id}");

            $response->assertStatus(401);
        });
    });

    describe('Revoke endpoint', function () {
        it('can revoke a consent', function () {
            Sanctum::actingAs($this->user);
            
            $consent = ConsentLog::factory()->forUser($this->user)->active()->create();

            $response = $this->postJson("/api/v1/consent-logs/{$consent->id}/revoke", [
                'reason' => 'User requested revocation'
            ]);

            $response->assertStatus(200)
                    ->assertJson([
                        'message' => 'Consentimiento revocado exitosamente'
                    ]);

            $consent->refresh();
            expect($consent->revoked_at)->not->toBeNull();
            expect($consent->revocation_reason)->toBe('User requested revocation');
            expect($consent->isRevoked())->toBe(true);
            expect($consent->isActive())->toBe(false);
        });

        it('can revoke without reason', function () {
            Sanctum::actingAs($this->user);
            
            $consent = ConsentLog::factory()->forUser($this->user)->active()->create();

            $response = $this->postJson("/api/v1/consent-logs/{$consent->id}/revoke");

            $response->assertStatus(200);
            
            $consent->refresh();
            expect($consent->revoked_at)->not->toBeNull();
            expect($consent->revocation_reason)->toBeNull();
        });

        it('prevents revoking already revoked consent', function () {
            Sanctum::actingAs($this->user);
            
            $consent = ConsentLog::factory()->forUser($this->user)->revoked()->create();

            $response = $this->postJson("/api/v1/consent-logs/{$consent->id}/revoke");

            $response->assertStatus(422)
                    ->assertJson(['message' => 'El consentimiento ya ha sido revocado']);
        });

        it('prevents access to other users consent logs', function () {
            Sanctum::actingAs($this->user);
            
            $otherUserConsent = ConsentLog::factory()->forUser($this->otherUser)->active()->create();

            $response = $this->postJson("/api/v1/consent-logs/{$otherUserConsent->id}/revoke");

            $response->assertStatus(404)
                    ->assertJson(['message' => 'Consentimiento no encontrado']);
        });

        it('validates reason length', function () {
            Sanctum::actingAs($this->user);
            
            $consent = ConsentLog::factory()->forUser($this->user)->active()->create();

            $response = $this->postJson("/api/v1/consent-logs/{$consent->id}/revoke", [
                'reason' => str_repeat('a', 501) // Too long
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['reason']);
        });

        it('requires authentication', function () {
            $consent = ConsentLog::factory()->create();

            $response = $this->postJson("/api/v1/consent-logs/{$consent->id}/revoke");

            $response->assertStatus(401);
        });
    });

    describe('Current Status endpoint', function () {
        it('returns current status of all consent types', function () {
            Sanctum::actingAs($this->user);
            
            // Create some consents
            ConsentLog::factory()->forUser($this->user)->ofType('privacy_policy')->given()->active()->create();
            ConsentLog::factory()->forUser($this->user)->ofType('marketing_communications')->denied()->create();
            ConsentLog::factory()->forUser($this->user)->ofType('terms_and_conditions')->given()->revoked()->create();

            $response = $this->getJson('/api/v1/consent-logs/current-status');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            'privacy_policy' => [
                                'given',
                                'version',
                                'granted_at',
                                'revoked_at'
                            ],
                            'terms_and_conditions',
                            'cookies_policy',
                            'marketing_communications',
                            'data_processing',
                            'newsletter',
                            'analytics',
                            'third_party_sharing'
                        ]
                    ]);

            $data = $response->json('data');
            expect($data['privacy_policy']['given'])->toBe(true);
            expect($data['marketing_communications']['given'])->toBe(false);
            expect($data['terms_and_conditions']['given'])->toBe(false); // Revoked
        });

        it('returns false for consent types with no records', function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/v1/consent-logs/current-status');

            $response->assertStatus(200);
            
            $data = $response->json('data');
            foreach ($this->validConsentTypes as $type) {
                expect($data[$type]['given'])->toBe(false);
                expect($data[$type]['version'])->toBeNull();
                expect($data[$type]['granted_at'])->toBeNull();
                expect($data[$type]['revoked_at'])->toBeNull();
            }
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/v1/consent-logs/current-status');
            $response->assertStatus(401);
        });
    });

    describe('History endpoint', function () {
        it('returns history for specific consent type', function () {
            Sanctum::actingAs($this->user);
            
            // Create history for privacy_policy
            $old = ConsentLog::factory()->forUser($this->user)->ofType('privacy_policy')->create([
                'version' => '1.0',
                'created_at' => now()->subMonths(2)
            ]);
            $new = ConsentLog::factory()->forUser($this->user)->ofType('privacy_policy')->create([
                'version' => '2.0',
                'created_at' => now()->subMonth()
            ]);
            
            // Create history for other type (should not appear)
            ConsentLog::factory()->forUser($this->user)->ofType('marketing_communications')->create();

            $response = $this->getJson('/api/v1/consent-logs/history/privacy_policy');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            '*' => [
                                'id',
                                'consent_type',
                                'consent_given',
                                'version',
                                'purpose',
                                'legal_basis',
                                'data_categories',
                                'retention_period',
                                'third_parties',
                                'withdrawal_method',
                                'ip_address',
                                'user_agent',
                                'metadata',
                                'is_active',
                                'created_at',
                                'updated_at',
                                'revoked_at',
                                'revocation_reason'
                            ]
                        ]
                    ]);

            $data = $response->json('data');
            expect($data)->toHaveCount(2);
            
            // Should be ordered by created_at desc
            expect($data[0]['id'])->toBe($new->id);
            expect($data[1]['id'])->toBe($old->id);
            
            // All should be privacy_policy
            foreach ($data as $consent) {
                expect($consent['consent_type'])->toBe('privacy_policy');
            }
        });

        it('returns empty for consent type with no history', function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/v1/consent-logs/history/privacy_policy');

            $response->assertStatus(200);
            expect($response->json('data'))->toHaveCount(0);
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/v1/consent-logs/history/privacy_policy');
            $response->assertStatus(401);
        });
    });

    describe('GDPR Report endpoint', function () {
        it('generates comprehensive GDPR report', function () {
            Sanctum::actingAs($this->user);
            
            // Create various consents
            $activeConsent = ConsentLog::factory()->forUser($this->user)->given()->active()->create([
                'consent_type' => 'privacy_policy',
                'version' => '1.0'
            ]);
            $revokedConsent = ConsentLog::factory()->forUser($this->user)->given()->revoked()->create([
                'consent_type' => 'marketing_communications',
                'version' => '1.1'
            ]);

            $response = $this->getJson('/api/v1/consent-logs/gdpr-report');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            'user_id',
                            'report_generated_at',
                            'summary' => [
                                'total_consents',
                                'active_consents',
                                'revoked_consents'
                            ],
                            'active_consents' => [
                                '*' => [
                                    'type',
                                    'type_name',
                                    'version',
                                    'granted_at',
                                    'document_url'
                                ]
                            ],
                            'consent_history'
                        ]
                    ]);

            $data = $response->json('data');
            expect($data['user_id'])->toBe($this->user->id);
            expect($data['summary']['total_consents'])->toBe(2);
            expect($data['summary']['active_consents'])->toBe(1);
            expect($data['summary']['revoked_consents'])->toBe(1);
            expect($data['active_consents'])->toHaveCount(1);
            expect($data['active_consents'][0]['type'])->toBe('privacy_policy');
        });

        it('handles user with no consents', function () {
            Sanctum::actingAs($this->user);

            $response = $this->getJson('/api/v1/consent-logs/gdpr-report');

            $response->assertStatus(200);
            
            $data = $response->json('data');
            expect($data['summary']['total_consents'])->toBe(0);
            expect($data['summary']['active_consents'])->toBe(0);
            expect($data['summary']['revoked_consents'])->toBe(0);
            expect($data['active_consents'])->toHaveCount(0);
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/v1/consent-logs/gdpr-report');
            $response->assertStatus(401);
        });
    });

    describe('Model business logic', function () {
        it('can determine if consent is active', function () {
            $activeConsent = ConsentLog::factory()->given()->active()->create();
            $revokedConsent = ConsentLog::factory()->given()->revoked()->create();
            $deniedConsent = ConsentLog::factory()->denied()->create();

            expect($activeConsent->isActive())->toBe(true);
            expect($revokedConsent->isActive())->toBe(false);
            expect($deniedConsent->isActive())->toBe(false);
        });

        it('can determine if consent is revoked', function () {
            $activeConsent = ConsentLog::factory()->active()->create();
            $revokedConsent = ConsentLog::factory()->revoked()->create();

            expect($activeConsent->isRevoked())->toBe(false);
            expect($revokedConsent->isRevoked())->toBe(true);
        });

        it('can revoke consent using model method', function () {
            $consent = ConsentLog::factory()->active()->create();

            $result = $consent->revoke();

            expect($result)->toBe(true);
            $consent->refresh();
            expect($consent->isRevoked())->toBe(true);
        });

        it('provides consent type name', function () {
            $consent = ConsentLog::factory()->ofType('privacy_policy')->create();

            expect($consent->consent_type_name)->toBe(AppEnums::CONSENT_TYPES['privacy_policy']);
        });

        it('can validate consent for specific version', function () {
            $consent = ConsentLog::factory()->given()->active()->create(['version' => '1.0']);

            expect($consent->isValidForVersion())->toBe(true);
            expect($consent->isValidForVersion('1.0'))->toBe(true);
            expect($consent->isValidForVersion('2.0'))->toBe(false);

            $consent->update(['revoked_at' => now()]);
            expect($consent->isValidForVersion('1.0'))->toBe(false);
        });

        it('can get latest consent for user', function () {
            $user = User::factory()->create();
            
            $old = ConsentLog::factory()->forUser($user)->ofType('privacy_policy')->create([
                'consented_at' => now()->subDays(2)
            ]);
            $latest = ConsentLog::factory()->forUser($user)->ofType('privacy_policy')->create([
                'consented_at' => now()->subDay()
            ]);

            $result = ConsentLog::getLatestConsentForUser($user->id, 'privacy_policy');

            expect($result->id)->toBe($latest->id);
        });

        it('can check if user has consented', function () {
            $user = User::factory()->create();
            
            // No consent
            expect(ConsentLog::hasUserConsented($user->id, 'privacy_policy'))->toBe(false);
            
            // Denied consent
            ConsentLog::factory()->forUser($user)->ofType('privacy_policy')->denied()->create();
            expect(ConsentLog::hasUserConsented($user->id, 'privacy_policy'))->toBe(false);
            
            // Given consent
            ConsentLog::factory()->forUser($user)->ofType('privacy_policy')->given()->active()->create();
            expect(ConsentLog::hasUserConsented($user->id, 'privacy_policy'))->toBe(true);
            
            // Revoked consent
            ConsentLog::where('user_id', $user->id)->update(['revoked_at' => now()]);
            expect(ConsentLog::hasUserConsented($user->id, 'privacy_policy'))->toBe(false);
        });

        it('can record consent using static method', function () {
            $user = User::factory()->create();
            
            $consent = ConsentLog::recordConsent(
                $user->id,
                'privacy_policy',
                true,
                '1.0',
                [
                    'ip_address' => '192.168.1.1',
                    'user_agent' => 'Test Browser',
                    'purpose' => 'Test purpose'
                ]
            );

            expect($consent)->toBeInstanceOf(ConsentLog::class);
            expect($consent->user_id)->toBe($user->id);
            expect($consent->consent_type)->toBe('privacy_policy');
            expect($consent->consent_given)->toBe(true);
            expect($consent->version)->toBe('1.0');
            expect($consent->ip_address)->toBe('192.168.1.1');
            expect($consent->purpose)->toBe('Test purpose');
        });

        it('revokes previous consents when recording new version', function () {
            $user = User::factory()->create();
            
            $oldConsent = ConsentLog::recordConsent($user->id, 'privacy_policy', true, '1.0');
            expect($oldConsent->isActive())->toBe(true);
            
            $newConsent = ConsentLog::recordConsent($user->id, 'privacy_policy', true, '2.0');
            
            $oldConsent->refresh();
            expect($oldConsent->isRevoked())->toBe(true);
            expect($newConsent->isActive())->toBe(true);
        });
    });

    describe('Scopes', function () {
        it('can filter active consents', function () {
            $activeConsent = ConsentLog::factory()->active()->create();
            $revokedConsent = ConsentLog::factory()->revoked()->create();

            $activeConsents = ConsentLog::active()->get();

            expect($activeConsents)->toHaveCount(1);
            expect($activeConsents->first()->id)->toBe($activeConsent->id);
        });

        it('can filter revoked consents', function () {
            $activeConsent = ConsentLog::factory()->active()->create();
            $revokedConsent = ConsentLog::factory()->revoked()->create();

            $revokedConsents = ConsentLog::revoked()->get();

            expect($revokedConsents)->toHaveCount(1);
            expect($revokedConsents->first()->id)->toBe($revokedConsent->id);
        });

        it('can filter by consent type', function () {
            $privacyConsent = ConsentLog::factory()->ofType('privacy_policy')->create();
            $marketingConsent = ConsentLog::factory()->ofType('marketing_communications')->create();

            $privacyConsents = ConsentLog::ofType('privacy_policy')->get();

            expect($privacyConsents)->toHaveCount(1);
            expect($privacyConsents->first()->id)->toBe($privacyConsent->id);
        });

        it('can filter by user', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            
            $user1Consent = ConsentLog::factory()->forUser($user1)->create();
            $user2Consent = ConsentLog::factory()->forUser($user2)->create();

            $user1Consents = ConsentLog::forUser($user1->id)->get();

            expect($user1Consents)->toHaveCount(1);
            expect($user1Consents->first()->id)->toBe($user1Consent->id);
        });
    });

    describe('Edge cases and validation', function () {
        it('handles all valid consent types', function () {
            Sanctum::actingAs($this->user);

            foreach ($this->validConsentTypes as $type) {
                $response = $this->postJson('/api/v1/consent-logs', [
                    'consent_type' => $type,
                    'consent_given' => true,
                ]);

                $response->assertStatus(201);
                
                $this->assertDatabaseHas('consent_logs', [
                    'user_id' => $this->user->id,
                    'consent_type' => $type,
                ]);
            }
        });

        it('handles complex data categories array', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/consent-logs', [
                'consent_type' => 'privacy_policy',
                'consent_given' => true,
                'data_categories' => [
                    'personal_identification',
                    'contact_information',
                    'usage_analytics',
                    'preferences',
                    'location_data'
                ],
            ]);

            $response->assertStatus(201);
            
            $consent = ConsentLog::latest()->first();
            expect($consent->data_categories)->toHaveCount(5);
            expect($consent->data_categories)->toContain('personal_identification');
        });

        it('handles complex third parties array', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/consent-logs', [
                'consent_type' => 'analytics',
                'consent_given' => true,
                'third_parties' => [
                    'Google Analytics',
                    'Facebook Pixel',
                    'Hotjar',
                    'Mailchimp'
                ],
            ]);

            $response->assertStatus(201);
            
            $consent = ConsentLog::latest()->first();
            expect($consent->third_parties)->toHaveCount(4);
            expect($consent->third_parties)->toContain('Google Analytics');
        });

        it('handles empty arrays for optional fields', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/consent-logs', [
                'consent_type' => 'privacy_policy',
                'consent_given' => true,
                'data_categories' => [],
                'third_parties' => [],
            ]);

            $response->assertStatus(201);
            
            $consent = ConsentLog::latest()->first();
            expect($consent->data_categories)->toBeEmpty();
            expect($consent->third_parties)->toBeEmpty();
        });

        it('tracks timestamp accurately', function () {
            Sanctum::actingAs($this->user);

            $beforeRequest = now()->subSecond(); // Add some buffer
            
            $response = $this->postJson('/api/v1/consent-logs', [
                'consent_type' => 'privacy_policy',
                'consent_given' => true,
            ]);

            $afterRequest = now()->addSecond(); // Add some buffer
            
            $response->assertStatus(201);
            
            $consent = ConsentLog::latest()->first();
            expect($consent->consented_at)->not->toBeNull();
            expect($consent->consented_at->between($beforeRequest, $afterRequest))->toBe(true);
            expect($consent->created_at->between($beforeRequest, $afterRequest))->toBe(true);
        });

        it('ensures user isolation in current status', function () {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            
            // User 1 consents to privacy policy
            ConsentLog::factory()->forUser($user1)->ofType('privacy_policy')->given()->active()->create();
            
            // User 2 denies privacy policy
            ConsentLog::factory()->forUser($user2)->ofType('privacy_policy')->denied()->create();

            // Check user 1's status
            Sanctum::actingAs($user1);
            $response1 = $this->getJson('/api/v1/consent-logs/current-status');
            expect($response1->json('data.privacy_policy.given'))->toBe(true);

            // Check user 2's status
            Sanctum::actingAs($user2);
            $response2 = $this->getJson('/api/v1/consent-logs/current-status');
            expect($response2->json('data.privacy_policy.given'))->toBe(false);
        });
    });
});
