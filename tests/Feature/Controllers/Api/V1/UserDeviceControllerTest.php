<?php

use App\Models\UserDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean up any existing data
    UserDevice::query()->delete();
    
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

describe('UserDeviceController', function () {
    describe('Index endpoint', function () {
        it('lists authenticated user devices only', function () {
            Sanctum::actingAs($this->user);
            
            // Create devices for current user
            $userDevices = UserDevice::factory()->count(3)->create(['user_id' => $this->user->id]);
            
            // Create devices for other user (should not appear)
            UserDevice::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

            $response = $this->getJson('/api/v1/user-devices');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'data' => [
                            '*' => [
                                'id',
                                'device_name',
                                'device_type',
                                'platform',
                                'is_current',
                                'is_active',
                                'last_seen_at'
                            ]
                        ]
                    ]);

            $data = $response->json('data');
            expect($data)->toHaveCount(3);
            
            foreach ($data as $device) {
                expect($userDevices->pluck('id')->toArray())->toContain($device['id']);
            }
        });

        it('orders by is_current desc and last_seen_at desc', function () {
            Sanctum::actingAs($this->user);
            
            $oldDevice = UserDevice::factory()->create([
                'user_id' => $this->user->id,
                'is_current' => false,
                'last_seen_at' => now()->subDays(5)
            ]);
            
            $currentDevice = UserDevice::factory()->create([
                'user_id' => $this->user->id,
                'is_current' => true,
                'last_seen_at' => now()->subDay()
            ]);
            
            $recentDevice = UserDevice::factory()->create([
                'user_id' => $this->user->id,
                'is_current' => false,
                'last_seen_at' => now()->subHours(2)
            ]);

            $response = $this->getJson('/api/v1/user-devices');

            $response->assertStatus(200);
            $data = $response->json('data');
            
            // Current device should be first, then recent, then old
            expect($data[0]['id'])->toBe($currentDevice->id);
            expect($data[1]['id'])->toBe($recentDevice->id);
            expect($data[2]['id'])->toBe($oldDevice->id);
        });

        it('can filter to active devices only', function () {
            Sanctum::actingAs($this->user);
            
            $activeDevice = UserDevice::factory()->active()->create(['user_id' => $this->user->id]);
            $revokedDevice = UserDevice::factory()->revoked()->create(['user_id' => $this->user->id]);

            $response = $this->getJson('/api/v1/user-devices?active_only=true');

            $response->assertStatus(200);
            $data = $response->json('data');
            expect($data)->toHaveCount(1);
            expect($data[0]['id'])->toBe($activeDevice->id);
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/v1/user-devices');
            $response->assertStatus(401);
        });
    });

    describe('Store endpoint', function () {
        it('can register a new device', function () {
            Sanctum::actingAs($this->user);
            
            $deviceData = [
                'device_name' => 'iPhone 15 Pro',
                'device_type' => 'mobile',
                'platform' => 'iOS',
                'push_token' => 'test_push_token_123',
                'is_current' => true,
            ];

            $response = $this->postJson('/api/v1/user-devices', $deviceData);

            $response->assertStatus(201)
                    ->assertJsonStructure([
                        'data' => [
                            'id',
                            'device_name',
                            'device_type',
                            'platform',
                            'is_current',
                            'is_active',
                            'has_push_token',
                            'device_info',
                            'user_agent',
                            'ip_address',
                            'last_seen_at'
                        ],
                        'message'
                    ])
                    ->assertJson([
                        'data' => [
                            'device_name' => 'iPhone 15 Pro',
                            'device_type' => 'mobile',
                            'platform' => 'iOS',
                            'is_current' => true,
                            'is_active' => true,
                            'has_push_token' => true,
                        ],
                        'message' => 'Dispositivo registrado exitosamente'
                    ]);

            $this->assertDatabaseHas('user_devices', [
                'user_id' => $this->user->id,
                'device_name' => 'iPhone 15 Pro',
                'device_type' => 'mobile',
                'platform' => 'iOS',
                'is_current' => true,
            ]);
        });

        it('validates required fields', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/user-devices', []);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['device_name', 'device_type', 'platform']);
        });

        it('validates device_type is valid', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/user-devices', [
                'device_name' => 'Test Device',
                'device_type' => 'invalid_type',
                'platform' => 'iOS',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['device_type']);
        });

        it('validates platform is valid', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/user-devices', [
                'device_name' => 'Test Device',
                'device_type' => 'mobile',
                'platform' => 'invalid_platform',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['platform']);
        });

        it('sets device as current when requested', function () {
            Sanctum::actingAs($this->user);
            
            // Create existing current device
            $existingDevice = UserDevice::factory()->current()->create(['user_id' => $this->user->id]);

            $response = $this->postJson('/api/v1/user-devices', [
                'device_name' => 'New Current Device',
                'device_type' => 'mobile',
                'platform' => 'iOS',
                'is_current' => true,
            ]);

            $response->assertStatus(201);
            
            // Get the created device ID from response
            $newDeviceId = $response->json('data.id');
            $newDevice = UserDevice::find($newDeviceId);
            expect($newDevice->is_current)->toBe(true);
            
            // Old device should no longer be current
            $existingDevice->refresh();
            expect($existingDevice->is_current)->toBe(false);
        });

        it('captures user agent and IP address automatically', function () {
            Sanctum::actingAs($this->user);

            $response = $this->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15',
            ])->postJson('/api/v1/user-devices', [
                'device_name' => 'iPhone Test',
                'device_type' => 'mobile',
                'platform' => 'iOS',
            ]);

            $response->assertStatus(201);
            
            $device = UserDevice::latest()->first();
            expect($device->user_agent)->toContain('iPhone');
            expect($device->ip_address)->not->toBeNull();
        });

        it('requires authentication', function () {
            $response = $this->postJson('/api/v1/user-devices', [
                'device_name' => 'Test Device',
                'device_type' => 'mobile',
                'platform' => 'iOS',
            ]);

            $response->assertStatus(401);
        });
    });

    describe('Show endpoint', function () {
        it('can show user own device', function () {
            Sanctum::actingAs($this->user);
            
            $device = UserDevice::factory()->create(['user_id' => $this->user->id]);

            $response = $this->getJson("/api/v1/user-devices/{$device->id}");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'id' => $device->id,
                            'device_name' => $device->device_name,
                            'device_type' => $device->device_type,
                            'platform' => $device->platform,
                        ]
                    ]);
        });

        it('cannot show other user device', function () {
            Sanctum::actingAs($this->user);
            
            $otherUserDevice = UserDevice::factory()->create(['user_id' => $this->otherUser->id]);

            $response = $this->getJson("/api/v1/user-devices/{$otherUserDevice->id}");

            $response->assertStatus(404)
                    ->assertJson(['message' => 'Dispositivo no encontrado']);
        });

        it('requires authentication', function () {
            $device = UserDevice::factory()->create(['user_id' => $this->user->id]);

            $response = $this->getJson("/api/v1/user-devices/{$device->id}");
            $response->assertStatus(401);
        });
    });

    describe('Update endpoint', function () {
        it('can update user own device', function () {
            Sanctum::actingAs($this->user);
            
            $device = UserDevice::factory()->create([
                'user_id' => $this->user->id,
                'device_name' => 'Old Name',
                'is_current' => false,
            ]);

            $updateData = [
                'device_name' => 'Updated Device Name',
                'push_token' => 'new_push_token_456',
                'is_current' => true,
            ];

            $response = $this->putJson("/api/v1/user-devices/{$device->id}", $updateData);

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'device_name' => 'Updated Device Name',
                            'is_current' => true,
                            'has_push_token' => true,
                        ],
                        'message' => 'Dispositivo actualizado exitosamente'
                    ]);

            $device->refresh();
            expect($device->device_name)->toBe('Updated Device Name');
            expect($device->is_current)->toBe(true);
        });

        it('can partially update device', function () {
            Sanctum::actingAs($this->user);
            
            $device = UserDevice::factory()->create([
                'user_id' => $this->user->id,
                'device_name' => 'Original Name',
                'push_token' => 'original_token',
            ]);

            $response = $this->putJson("/api/v1/user-devices/{$device->id}", [
                'device_name' => 'Updated Name Only',
            ]);

            $response->assertStatus(200);
            
            $device->refresh();
            expect($device->device_name)->toBe('Updated Name Only');
            expect($device->push_token)->toBe('original_token'); // Should remain unchanged
        });

        it('cannot update other user device', function () {
            Sanctum::actingAs($this->user);
            
            $otherUserDevice = UserDevice::factory()->create(['user_id' => $this->otherUser->id]);

            $response = $this->putJson("/api/v1/user-devices/{$otherUserDevice->id}", [
                'device_name' => 'Hacked Name',
            ]);

            $response->assertStatus(404)
                    ->assertJson(['message' => 'Dispositivo no encontrado']);
        });

        it('updates last_seen_at when updating', function () {
            Sanctum::actingAs($this->user);
            
            $device = UserDevice::factory()->create([
                'user_id' => $this->user->id,
                'last_seen_at' => now()->subHours(5),
            ]);
            
            $oldLastSeen = $device->last_seen_at;

            $response = $this->putJson("/api/v1/user-devices/{$device->id}", [
                'device_name' => 'Updated Name',
            ]);

            $response->assertStatus(200);
            
            $device->refresh();
            expect($device->last_seen_at->isAfter($oldLastSeen))->toBe(true);
        });

        it('requires authentication', function () {
            $device = UserDevice::factory()->create(['user_id' => $this->user->id]);

            $response = $this->putJson("/api/v1/user-devices/{$device->id}", [
                'device_name' => 'Updated Name',
            ]);

            $response->assertStatus(401);
        });
    });

    describe('Destroy endpoint', function () {
        it('can revoke user own device', function () {
            Sanctum::actingAs($this->user);
            
            $device = UserDevice::factory()->active()->current()->create(['user_id' => $this->user->id]);

            $response = $this->deleteJson("/api/v1/user-devices/{$device->id}");

            $response->assertStatus(200)
                    ->assertJson(['message' => 'Dispositivo revocado exitosamente']);

            $device->refresh();
            expect($device->isRevoked())->toBe(true);
            expect($device->is_current)->toBe(false);
        });

        it('cannot revoke other user device', function () {
            Sanctum::actingAs($this->user);
            
            $otherUserDevice = UserDevice::factory()->create(['user_id' => $this->otherUser->id]);

            $response = $this->deleteJson("/api/v1/user-devices/{$otherUserDevice->id}");

            $response->assertStatus(404)
                    ->assertJson(['message' => 'Dispositivo no encontrado']);
        });

        it('requires authentication', function () {
            $device = UserDevice::factory()->create(['user_id' => $this->user->id]);

            $response = $this->deleteJson("/api/v1/user-devices/{$device->id}");
            $response->assertStatus(401);
        });
    });

    describe('setCurrent endpoint', function () {
        it('can set device as current', function () {
            Sanctum::actingAs($this->user);
            
            $currentDevice = UserDevice::factory()->current()->create(['user_id' => $this->user->id]);
            $newDevice = UserDevice::factory()->create(['user_id' => $this->user->id, 'is_current' => false]);

            $response = $this->postJson("/api/v1/user-devices/{$newDevice->id}/set-current");

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'is_current' => true,
                        ],
                        'message' => 'Dispositivo establecido como actual'
                    ]);

            // New device should be current
            $newDevice->refresh();
            expect($newDevice->is_current)->toBe(true);
            
            // Old current device should no longer be current
            $currentDevice->refresh();
            expect($currentDevice->is_current)->toBe(false);
        });

        it('cannot set other user device as current', function () {
            Sanctum::actingAs($this->user);
            
            $otherUserDevice = UserDevice::factory()->create(['user_id' => $this->otherUser->id]);

            $response = $this->postJson("/api/v1/user-devices/{$otherUserDevice->id}/set-current");

            $response->assertStatus(404)
                    ->assertJson(['message' => 'Dispositivo no encontrado']);
        });

        it('requires authentication', function () {
            $device = UserDevice::factory()->create(['user_id' => $this->user->id]);

            $response = $this->postJson("/api/v1/user-devices/{$device->id}/set-current");
            $response->assertStatus(401);
        });
    });

    describe('updateActivity endpoint', function () {
        it('can update device activity', function () {
            Sanctum::actingAs($this->user);
            
            $device = UserDevice::factory()->create([
                'user_id' => $this->user->id,
                'last_seen_at' => now()->subHours(5),
            ]);
            
            $oldLastSeen = $device->last_seen_at;

            $response = $this->postJson("/api/v1/user-devices/{$device->id}/update-activity");

            $response->assertStatus(200)
                    ->assertJson(['message' => 'Actividad actualizada']);

            $device->refresh();
            expect($device->last_seen_at->isAfter($oldLastSeen))->toBe(true);
        });

        it('cannot update other user device activity', function () {
            Sanctum::actingAs($this->user);
            
            $otherUserDevice = UserDevice::factory()->create(['user_id' => $this->otherUser->id]);

            $response = $this->postJson("/api/v1/user-devices/{$otherUserDevice->id}/update-activity");

            $response->assertStatus(404)
                    ->assertJson(['message' => 'Dispositivo no encontrado']);
        });

        it('requires authentication', function () {
            $device = UserDevice::factory()->create(['user_id' => $this->user->id]);

            $response = $this->postJson("/api/v1/user-devices/{$device->id}/update-activity");
            $response->assertStatus(401);
        });
    });

    describe('current endpoint', function () {
        it('can get current device', function () {
            Sanctum::actingAs($this->user);
            
            $currentDevice = UserDevice::factory()->current()->create(['user_id' => $this->user->id]);
            $otherDevice = UserDevice::factory()->create(['user_id' => $this->user->id, 'is_current' => false]);

            $response = $this->getJson('/api/v1/user-devices/current');

            $response->assertStatus(200)
                    ->assertJson([
                        'data' => [
                            'id' => $currentDevice->id,
                            'is_current' => true,
                        ]
                    ]);
        });

        it('returns 404 when no current device', function () {
            Sanctum::actingAs($this->user);
            
            UserDevice::factory()->create(['user_id' => $this->user->id, 'is_current' => false]);

            $response = $this->getJson('/api/v1/user-devices/current');

            $response->assertStatus(404)
                    ->assertJson(['message' => 'No hay dispositivo actual configurado']);
        });

        it('only returns current user current device', function () {
            Sanctum::actingAs($this->user);
            
            // Other user has current device, but this user doesn't
            UserDevice::factory()->current()->create(['user_id' => $this->otherUser->id]);
            UserDevice::factory()->create(['user_id' => $this->user->id, 'is_current' => false]);

            $response = $this->getJson('/api/v1/user-devices/current');

            $response->assertStatus(404);
        });

        it('requires authentication', function () {
            $response = $this->getJson('/api/v1/user-devices/current');
            $response->assertStatus(401);
        });
    });

    describe('Model business logic', function () {
        it('can register new device using static method', function () {
            $device = UserDevice::registerDevice(
                $this->user->id,
                'mobile',
                'iPhone Test',
                'iOS',
                'Mozilla/5.0 (iPhone)',
                '192.168.1.1',
                'push_token_123'
            );

            expect($device->user_id)->toBe($this->user->id);
            expect($device->device_type)->toBe('mobile');
            expect($device->device_name)->toBe('iPhone Test');
            expect($device->platform)->toBe('iOS');
            expect($device->push_token)->toBe('push_token_123');
            expect($device->is_current)->toBe(true); // Default behavior
        });

        it('updates existing device when registering with same user_agent', function () {
            $userAgent = 'Mozilla/5.0 (iPhone) Test Browser';
            
            // Create initial device
            $device1 = UserDevice::registerDevice(
                $this->user->id,
                'mobile',
                'iPhone 1',
                'iOS',
                $userAgent,
                '192.168.1.1'
            );

            // Register again with same user_agent
            $device2 = UserDevice::registerDevice(
                $this->user->id,
                'mobile',
                'iPhone 2 Updated',
                'iOS',
                $userAgent,
                '192.168.1.2'
            );

            // Should be the same device, updated
            expect($device1->id)->toBe($device2->id);
            expect($device2->device_name)->toBe('iPhone 2 Updated');
            expect($device2->ip_address)->toBe('192.168.1.2');
            
            // Should only have one device in database
            expect(UserDevice::where('user_id', $this->user->id)->count())->toBe(1);
        });

        it('can check if device is active', function () {
            $activeDevice = UserDevice::factory()->active()->create();
            $revokedDevice = UserDevice::factory()->revoked()->create();

            expect($activeDevice->isActive())->toBe(true);
            expect($revokedDevice->isActive())->toBe(false);
        });

        it('can check if device is revoked', function () {
            $activeDevice = UserDevice::factory()->active()->create();
            $revokedDevice = UserDevice::factory()->revoked()->create();

            expect($activeDevice->isRevoked())->toBe(false);
            expect($revokedDevice->isRevoked())->toBe(true);
        });

        it('can revoke device', function () {
            $device = UserDevice::factory()->current()->create();

            expect($device->isActive())->toBe(true);
            expect($device->is_current)->toBe(true);

            $device->revoke();

            expect($device->isRevoked())->toBe(true);
            expect($device->is_current)->toBe(false);
        });

        it('can set device as current', function () {
            $currentDevice = UserDevice::factory()->current()->create(['user_id' => $this->user->id]);
            $newDevice = UserDevice::factory()->create(['user_id' => $this->user->id, 'is_current' => false]);

            $newDevice->setCurrent();

            $newDevice->refresh();
            $currentDevice->refresh();

            expect($newDevice->is_current)->toBe(true);
            expect($currentDevice->is_current)->toBe(false);
        });

        it('can update last seen', function () {
            $device = UserDevice::factory()->create(['last_seen_at' => now()->subHours(5)]);
            $oldLastSeen = $device->last_seen_at;

            $device->updateLastSeen('192.168.1.100');

            $device->refresh();
            expect($device->last_seen_at->isAfter($oldLastSeen))->toBe(true);
            expect($device->ip_address)->toBe('192.168.1.100');
        });

        it('generates device type name', function () {
            $device = UserDevice::factory()->create(['device_type' => 'mobile']);
            expect($device->device_type_name)->toBe('Móvil');
        });

        it('generates device info string', function () {
            $device = UserDevice::factory()->create([
                'device_name' => 'My iPhone',
                'platform' => 'iOS',
            ]);

            expect($device->device_info)->toBe('My iPhone (iOS)');
        });

        it('can check if recently active', function () {
            $recentDevice = UserDevice::factory()->create(['last_seen_at' => now()->subHours(5)]);
            $oldDevice = UserDevice::factory()->create(['last_seen_at' => now()->subDays(40)]);

            expect($recentDevice->isRecentlyActive())->toBe(true);
            expect($oldDevice->isRecentlyActive())->toBe(false);
        });

        it('can get active devices for user', function () {
            $activeDevices = UserDevice::factory()->active()->count(3)->create(['user_id' => $this->user->id]);
            $revokedDevice = UserDevice::factory()->revoked()->create(['user_id' => $this->user->id]);
            $otherUserDevice = UserDevice::factory()->active()->create(['user_id' => $this->otherUser->id]);

            $userActiveDevices = UserDevice::getActiveDevicesForUser($this->user->id);

            expect($userActiveDevices)->toHaveCount(3);
            foreach ($userActiveDevices as $device) {
                expect($device->user_id)->toBe($this->user->id);
                expect($device->isActive())->toBe(true);
            }
        });
    });

    describe('Scopes and filtering', function () {
        it('active scope filters revoked devices', function () {
            $activeDevices = UserDevice::factory()->active()->count(3)->create();
            $revokedDevices = UserDevice::factory()->revoked()->count(2)->create();

            $active = UserDevice::active()->get();

            expect($active)->toHaveCount(3);
            foreach ($active as $device) {
                expect($device->isActive())->toBe(true);
            }
        });

        it('revoked scope filters active devices', function () {
            $activeDevices = UserDevice::factory()->active()->count(3)->create();
            $revokedDevices = UserDevice::factory()->revoked()->count(2)->create();

            $revoked = UserDevice::revoked()->get();

            expect($revoked)->toHaveCount(2);
            foreach ($revoked as $device) {
                expect($device->isRevoked())->toBe(true);
            }
        });

        it('current scope filters non-current devices', function () {
            $currentDevices = UserDevice::factory()->current()->count(2)->create();
            $nonCurrentDevices = UserDevice::factory()->count(3)->create(['is_current' => false]);

            $current = UserDevice::current()->get();

            expect($current)->toHaveCount(2);
            foreach ($current as $device) {
                expect($device->is_current)->toBe(true);
            }
        });

        it('forUser scope filters by user', function () {
            $userDevices = UserDevice::factory()->count(3)->create(['user_id' => $this->user->id]);
            $otherUserDevices = UserDevice::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

            $userFiltered = UserDevice::forUser($this->user->id)->get();

            expect($userFiltered)->toHaveCount(3);
            foreach ($userFiltered as $device) {
                expect($device->user_id)->toBe($this->user->id);
            }
        });

        it('ofType scope filters by device type', function () {
            $mobileDevices = UserDevice::factory()->mobile()->count(2)->create();
            $webDevices = UserDevice::factory()->web()->count(3)->create();

            $mobile = UserDevice::ofType('mobile')->get();

            expect($mobile)->toHaveCount(2);
            foreach ($mobile as $device) {
                expect($device->device_type)->toBe('mobile');
            }
        });

        it('withPushToken scope filters devices with push tokens', function () {
            $withTokens = UserDevice::factory()->withPushToken()->count(2)->create();
            $withoutTokens = UserDevice::factory()->count(3)->create(['push_token' => null]);

            $withPushToken = UserDevice::withPushToken()->get();

            expect($withPushToken)->toHaveCount(2);
            foreach ($withPushToken as $device) {
                expect($device->push_token)->not->toBeNull();
            }
        });

        it('recentlyActive scope filters by recent activity', function () {
            $recentDevices = UserDevice::factory()->recentlyActive()->count(2)->create();
            $oldDevices = UserDevice::factory()->count(3)->create(['last_seen_at' => now()->subDays(40)]);

            $recent = UserDevice::recentlyActive()->get();

            expect($recent)->toHaveCount(2);
            foreach ($recent as $device) {
                expect($device->isRecentlyActive())->toBe(true);
            }
        });
    });

    describe('Edge cases and validation', function () {
        it('handles device without name gracefully', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/user-devices', [
                'device_name' => '',
                'device_type' => 'mobile',
                'platform' => 'iOS',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['device_name']);
        });

        it('handles long device names', function () {
            Sanctum::actingAs($this->user);

            $longName = str_repeat('Very Long Device Name ', 20); // > 255 chars

            $response = $this->postJson('/api/v1/user-devices', [
                'device_name' => $longName,
                'device_type' => 'mobile',
                'platform' => 'iOS',
            ]);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['device_name']);
        });

        it('handles device without push token', function () {
            Sanctum::actingAs($this->user);

            $response = $this->postJson('/api/v1/user-devices', [
                'device_name' => 'Device Without Token',
                'device_type' => 'web',
                'platform' => 'Web',
            ]);

            $response->assertStatus(201);
            
            $device = UserDevice::latest()->first();
            expect($device->push_token)->toBeNull();
            expect(empty($device->push_token))->toBe(true);
        });

        it('handles multiple devices being set as current', function () {
            Sanctum::actingAs($this->user);
            
            // Create first current device
            $response1 = $this->postJson('/api/v1/user-devices', [
                'device_name' => 'Device 1',
                'device_type' => 'mobile',
                'platform' => 'iOS',
                'is_current' => true,
            ]);

            // Create second current device
            $response2 = $this->postJson('/api/v1/user-devices', [
                'device_name' => 'Device 2',
                'device_type' => 'desktop',
                'platform' => 'Windows',
                'is_current' => true,
            ]);

            $response1->assertStatus(201);
            $response2->assertStatus(201);

            // Only the latest should be current
            $currentDevices = UserDevice::forUser($this->user->id)->current()->get();
            expect($currentDevices)->toHaveCount(1);
            expect($currentDevices->first()->device_name)->toBe('Device 2');
        });

        it('cleans up old devices', function () {
            // Create old devices
            $oldDevices = UserDevice::factory()->count(3)->create([
                'last_seen_at' => now()->subDays(100),
                'revoked_at' => null,
            ]);
            
            // Create recent devices
            $recentDevices = UserDevice::factory()->count(2)->create([
                'last_seen_at' => now()->subDays(10),
                'revoked_at' => null,
            ]);

            $cleanedCount = UserDevice::cleanupOldDevices(90);

            expect($cleanedCount)->toBe(3);
            
            foreach ($oldDevices as $device) {
                $device->refresh();
                expect($device->isRevoked())->toBe(true);
            }
            
            foreach ($recentDevices as $device) {
                $device->refresh();
                expect($device->isActive())->toBe(true);
            }
        });
    });
});
