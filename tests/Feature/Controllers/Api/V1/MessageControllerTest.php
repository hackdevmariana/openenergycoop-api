<?php

use App\Models\Message;
use App\Models\Organization;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    // Clean up data before each test
    Message::query()->delete();
});

// GET /api/v1/messages - List Messages
it('can list messages with pagination', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Message::factory()->count(5)->pending()->create();
    Message::factory()->count(3)->read()->create();

    $response = $this->getJson('/api/v1/messages');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'name', 'email', 'subject', 'message', 'status',
                        'priority', 'message_type', 'read_at', 'replied_at',
                        'created_at', 'updated_at', 'is_read', 'is_replied',
                        'is_pending', 'status_label', 'priority_label'
                    ]
                ],
                'links',
                'meta'
            ])
            ->assertJsonCount(8, 'data');
});

it('orders messages by priority correctly', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $urgent = Message::factory()->urgent()->create();
    $high = Message::factory()->highPriority()->create();
    $normal = Message::factory()->create(['priority' => 'normal']);
    $low = Message::factory()->create(['priority' => 'low']);

    $response = $this->getJson('/api/v1/messages');

    $response->assertStatus(200);
    $data = $response->json('data');
    
    expect($data[0]['priority'])->toBe('urgent');
    expect($data[1]['priority'])->toBe('high');
    expect($data[2]['priority'])->toBe('normal');
    expect($data[3]['priority'])->toBe('low');
});

it('can filter messages by status', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Message::factory()->count(3)->pending()->create();
    Message::factory()->count(2)->read()->create();

    $response = $this->getJson('/api/v1/messages?status=pending');

    $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    
    foreach ($response->json('data') as $message) {
        expect($message['status'])->toBe('pending');
    }
});

it('can filter messages by priority', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Message::factory()->count(2)->urgent()->create();
    Message::factory()->count(3)->create(['priority' => 'normal']);

    $response = $this->getJson('/api/v1/messages?priority=urgent');

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    
    foreach ($response->json('data') as $message) {
        expect($message['priority'])->toBe('urgent');
    }
});

it('can filter messages by type', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Message::factory()->count(2)->support()->create();
    Message::factory()->count(3)->contact()->create();

    $response = $this->getJson('/api/v1/messages?message_type=support');

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    
    foreach ($response->json('data') as $message) {
        expect($message['message_type'])->toBe('support');
    }
});

it('can search messages by name, email, or subject', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Message::factory()->create(['name' => 'Juan Pérez', 'email' => 'juan@test.com']);
    Message::factory()->create(['email' => 'maria@test.com', 'subject' => 'Consulta urgente']);
    Message::factory()->create(['name' => 'Pedro', 'email' => 'pedro@other.com']);

    $response = $this->getJson('/api/v1/messages?search=juan');

    $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    
    expect($response->json('data.0.name'))->toContain('Juan');
});

it('can filter unread messages only', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Message::factory()->count(3)->unread()->create();
    Message::factory()->count(2)->read()->create();

    $response = $this->getJson('/api/v1/messages?unread_only=1');

    $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    
    foreach ($response->json('data') as $message) {
        expect($message['is_read'])->toBeFalse();
    }
});

// POST /api/v1/messages - Store Message (Public)
it('can create a new message via public form', function () {
    $organization = Organization::factory()->create();

    $messageData = [
        'name' => 'Juan Pérez',
        'email' => 'juan@test.com',
        'phone' => '+34 600 123 456',
        'subject' => 'Consulta sobre membresía',
        'message' => 'Hola, me gustaría saber más sobre cómo unirme a la cooperativa.',
        'priority' => 'normal',
        'message_type' => 'contact',
        'organization_id' => $organization->id,
    ];

    $response = $this->postJson('/api/v1/messages', $messageData);

    $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'subject', 'status'],
                'message'
            ]);

    $this->assertDatabaseHas('messages', [
        'name' => 'Juan Pérez',
        'email' => 'juan@test.com',
        'status' => 'pending',
        'message_type' => 'contact',
    ]);
});

it('sets default values when creating message', function () {
    $messageData = [
        'email' => 'test@example.com',
        'subject' => 'Test subject',
        'message' => 'Test message content',
    ];

    $response = $this->postJson('/api/v1/messages', $messageData);

    $response->assertStatus(201);
    
    $message = Message::latest()->first();
    expect($message->priority)->toBe('normal'); // Default
    expect($message->message_type)->toBe('contact'); // Default
    expect($message->status)->toBe('pending'); // Default
});

it('captures client information when creating message', function () {
    $messageData = [
        'email' => 'test@example.com',
        'subject' => 'Test subject',
        'message' => 'Test message content',
    ];

    $response = $this->postJson('/api/v1/messages', $messageData);

    $response->assertStatus(201);
    
    $message = Message::latest()->first();
    expect($message->ip_address)->not->toBeNull();
    expect($message->user_agent)->not->toBeNull();
});

it('validates required fields when creating message', function () {
    $response = $this->postJson('/api/v1/messages', [
        'name' => 'Test Name',
        // Missing email, subject, message
    ]);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'subject', 'message']);
});

it('validates email format when creating message', function () {
    $messageData = [
        'email' => 'invalid-email',
        'subject' => 'Test subject',
        'message' => 'Test message',
    ];

    $response = $this->postJson('/api/v1/messages', $messageData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
});

it('validates priority enum when creating message', function () {
    $messageData = [
        'email' => 'test@example.com',
        'subject' => 'Test subject',
        'message' => 'Test message',
        'priority' => 'invalid_priority',
    ];

    $response = $this->postJson('/api/v1/messages', $messageData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
});

it('validates message type enum when creating message', function () {
    $messageData = [
        'email' => 'test@example.com',
        'subject' => 'Test subject',
        'message' => 'Test message',
        'message_type' => 'invalid_type',
    ];

    $response = $this->postJson('/api/v1/messages', $messageData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['message_type']);
});

it('validates phone format when creating message', function () {
    $messageData = [
        'email' => 'test@example.com',
        'subject' => 'Test subject',
        'message' => 'Test message',
        'phone' => 'invalid-phone-format',
    ];

    $response = $this->postJson('/api/v1/messages', $messageData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
});

it('detects spam patterns when creating message', function () {
    $messageData = [
        'email' => 'test@example.com',
        'subject' => 'Win lottery now!',
        'message' => 'Congratulations! You won the lottery. Click here to claim your prize.',
    ];

    $response = $this->postJson('/api/v1/messages', $messageData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
});

it('enforces rate limiting by email', function () {
    $messageData = [
        'email' => 'test@example.com',
        'subject' => 'Test subject',
        'message' => 'Test message',
    ];

    // Send 5 messages (at the limit)
    for ($i = 0; $i < 5; $i++) {
        $response = $this->postJson('/api/v1/messages', $messageData);
        $response->assertStatus(201);
    }

    // 6th message should be rate limited
    $response = $this->postJson('/api/v1/messages', $messageData);
    $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
});

// GET /api/v1/messages/{message} - Show Message
it('can show a message and mark it as read', function () {
    $user = User::factory()->create();
    $message = Message::factory()->pending()->create();
    Sanctum::actingAs($user);

    expect($message->isRead())->toBeFalse();

    $response = $this->getJson("/api/v1/messages/{$message->id}");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'is_read', 'status']
            ]);

    $message->refresh();
    expect($message->isRead())->toBeTrue();
    expect($message->status)->toBe('read');
});

it('returns 404 for non-existent message', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/messages/999999');

    $response->assertStatus(404);
});

// PUT /api/v1/messages/{message} - Update Message
it('can update a message', function () {
    $user = User::factory()->create();
    $message = Message::factory()->pending()->create(['priority' => 'normal']);
    Sanctum::actingAs($user);

    $updateData = [
        'priority' => 'high',
        'status' => 'read',
        'internal_notes' => 'Cliente importante, responder pronto.',
    ];

    $response = $this->putJson("/api/v1/messages/{$message->id}", $updateData);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'priority', 'status', 'internal_notes'],
                'message'
            ]);

    $this->assertDatabaseHas('messages', [
        'id' => $message->id,
        'priority' => 'high',
        'status' => 'read',
        'internal_notes' => 'Cliente importante, responder pronto.',
    ]);
});

it('validates status transitions when updating', function () {
    $user = User::factory()->create();
    $message = Message::factory()->archived()->create();
    Sanctum::actingAs($user);

    $updateData = ['status' => 'replied']; // Invalid transition from archived

    $response = $this->putJson("/api/v1/messages/{$message->id}", $updateData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
});

it('auto-sets timestamps when changing status to replied', function () {
    $user = User::factory()->create();
    $message = Message::factory()->read()->create();
    Sanctum::actingAs($user);

    $updateData = ['status' => 'replied'];

    $response = $this->putJson("/api/v1/messages/{$message->id}", $updateData);

    $response->assertStatus(200);
    
    $message->refresh();
    expect($message->replied_at)->not->toBeNull();
    expect($message->replied_by_user_id)->toBe($user->id);
});

// DELETE /api/v1/messages/{message} - Delete Message
it('can delete a message', function () {
    $user = User::factory()->create();
    $message = Message::factory()->pending()->create();
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/messages/{$message->id}");

    $response->assertStatus(200)
            ->assertJson(['message' => 'Mensaje eliminado exitosamente']);

    $this->assertDatabaseMissing('messages', ['id' => $message->id]);
});

// POST /api/v1/messages/{message}/mark-as-read - Mark as Read
it('can mark message as read', function () {
    $user = User::factory()->create();
    $message = Message::factory()->pending()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/messages/{$message->id}/mark-as-read");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'status', 'read_at'],
                'message'
            ]);

    $message->refresh();
    expect($message->status)->toBe('read');
    expect($message->read_at)->not->toBeNull();
});

// POST /api/v1/messages/{message}/mark-as-replied - Mark as Replied
it('can mark message as replied', function () {
    $user = User::factory()->create();
    $message = Message::factory()->read()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/messages/{$message->id}/mark-as-replied");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'status', 'replied_at', 'replied_by_user_id'],
                'message'
            ]);

    $message->refresh();
    expect($message->status)->toBe('replied');
    expect($message->replied_at)->not->toBeNull();
    expect($message->replied_by_user_id)->toBe($user->id);
});

// POST /api/v1/messages/{message}/mark-as-spam - Mark as Spam
it('can mark message as spam', function () {
    $user = User::factory()->create();
    $message = Message::factory()->pending()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/messages/{$message->id}/mark-as-spam");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'status'],
                'message'
            ]);

    $message->refresh();
    expect($message->status)->toBe('spam');
});

// POST /api/v1/messages/{message}/archive - Archive Message
it('can archive message', function () {
    $user = User::factory()->create();
    $message = Message::factory()->read()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/messages/{$message->id}/archive");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'status'],
                'message'
            ]);

    $message->refresh();
    expect($message->status)->toBe('archived');
});

// POST /api/v1/messages/{message}/assign - Assign Message
it('can assign message to user', function () {
    $user = User::factory()->create();
    $assignee = User::factory()->create();
    $message = Message::factory()->pending()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/messages/{$message->id}/assign", [
        'user_id' => $assignee->id,
    ]);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'assigned_to_user_id'],
                'message'
            ]);

    $message->refresh();
    expect($message->assigned_to_user_id)->toBe($assignee->id);
});

it('validates user exists when assigning message', function () {
    $user = User::factory()->create();
    $message = Message::factory()->pending()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/messages/{$message->id}/assign", [
        'user_id' => 999999,
    ]);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
});

// DELETE /api/v1/messages/{message}/assign - Unassign Message
it('can unassign message', function () {
    $user = User::factory()->create();
    $message = Message::factory()->assigned()->create();
    Sanctum::actingAs($user);

    expect($message->assigned_to_user_id)->not->toBeNull();

    $response = $this->deleteJson("/api/v1/messages/{$message->id}/assign");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'assigned_to_user_id'],
                'message'
            ]);

    $message->refresh();
    expect($message->assigned_to_user_id)->toBeNull();
});

// GET /api/v1/messages/pending - Get Pending Messages
it('can get pending messages', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Message::factory()->count(3)->pending()->create();
    Message::factory()->count(2)->read()->create();

    $response = $this->getJson('/api/v1/messages/pending');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'status']],
                'total_pending'
            ])
            ->assertJsonCount(3, 'data');
    
    expect($response->json('total_pending'))->toBe(3);
});

// GET /api/v1/messages/unread - Get Unread Messages
it('can get unread messages', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Message::factory()->count(4)->unread()->create();
    Message::factory()->count(2)->read()->create();

    $response = $this->getJson('/api/v1/messages/unread');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'status']],
                'total_unread'
            ])
            ->assertJsonCount(4, 'data');
    
    expect($response->json('total_unread'))->toBe(4);
});

// GET /api/v1/messages/assigned - Get Assigned Messages
it('can get assigned messages for current user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Sanctum::actingAs($user);

    Message::factory()->count(2)->create(['assigned_to_user_id' => $user->id, 'status' => 'read']);
    Message::factory()->count(3)->create(['assigned_to_user_id' => $otherUser->id, 'status' => 'read']);
    Message::factory()->count(1)->archived()->create(['assigned_to_user_id' => $user->id]);

    $response = $this->getJson('/api/v1/messages/assigned');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'assigned_to_user_id']],
                'total_assigned'
            ])
            ->assertJsonCount(2, 'data'); // Only non-archived messages for current user
    
    expect($response->json('total_assigned'))->toBe(2);
});

it('can get assigned messages for specific user', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();
    Sanctum::actingAs($user);

    Message::factory()->count(3)->create(['assigned_to_user_id' => $targetUser->id, 'status' => 'read']);
    Message::factory()->count(2)->create(['assigned_to_user_id' => $user->id, 'status' => 'read']);

    $response = $this->getJson("/api/v1/messages/assigned?user_id={$targetUser->id}");

    $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    
    foreach ($response->json('data') as $message) {
        expect($message['assigned_to_user_id'])->toBe($targetUser->id);
    }
});

// GET /api/v1/messages/by-email/{email} - Get Messages by Email
it('can get messages by email address', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $email = 'test@example.com';
    Message::factory()->count(3)->fromEmail($email)->create();
    Message::factory()->count(2)->create(); // Different emails

    $response = $this->getJson("/api/v1/messages/by-email/{$email}");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'email']],
                'total',
                'email'
            ])
            ->assertJsonCount(3, 'data');
    
    expect($response->json('email'))->toBe($email);
    expect($response->json('total'))->toBe(3);
    
    foreach ($response->json('data') as $message) {
        expect($message['email'])->toBe($email);
    }
});

// GET /api/v1/messages/stats - Get Message Statistics
it('can get message statistics', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Sanctum::actingAs($user);

    // Ensure clean state for this test
    Message::query()->delete();

    Message::factory()->count(2)->pending()->withOrganization($organization)->create();
    Message::factory()->count(3)->read()->withOrganization($organization)->create();
    Message::factory()->count(1)->replied()->withOrganization($organization)->create();
    Message::factory()->count(1)->archived()->withOrganization($organization)->create();
    Message::factory()->count(1)->spam()->withOrganization($organization)->create();

    $response = $this->getJson("/api/v1/messages/stats?organization_id={$organization->id}");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'stats' => [
                    'total', 'pending', 'read', 'replied', 'archived', 'spam',
                    'urgent', 'high_priority', 'assigned', 'unassigned'
                ],
                'by_type' => ['contact', 'support', 'complaint', 'suggestion'],
                'response_time_avg'
            ]);
    
    $stats = $response->json('stats');
    expect($stats['total'])->toBe(8);
    expect($stats['pending'])->toBe(2);
    expect($stats['read'])->toBe(3);
    expect($stats['replied'])->toBe(1);
    expect($stats['archived'])->toBe(1);
    expect($stats['spam'])->toBe(1);
});

// Authentication Tests
it('requires authentication for listing messages', function () {
    $response = $this->getJson('/api/v1/messages');

    $response->assertStatus(401);
});

it('requires authentication for updating message', function () {
    $message = Message::factory()->pending()->create();

    $response = $this->putJson("/api/v1/messages/{$message->id}", [
        'status' => 'read',
    ]);

    $response->assertStatus(401);
});

it('requires authentication for deleting message', function () {
    $message = Message::factory()->pending()->create();

    $response = $this->deleteJson("/api/v1/messages/{$message->id}");

    $response->assertStatus(401);
});

it('allows public access for creating message', function () {
    $messageData = [
        'email' => 'public@example.com',
        'subject' => 'Public inquiry',
        'message' => 'This is a public contact form submission.',
    ];

    $response = $this->postJson('/api/v1/messages', $messageData);

    $response->assertStatus(201); // Should work without authentication
});

// Model Logic Tests
it('calculates status correctly', function () {
    $pendingMessage = Message::factory()->pending()->create();
    $readMessage = Message::factory()->read()->create();
    $repliedMessage = Message::factory()->replied()->create();

    expect($pendingMessage->isPending())->toBeTrue();
    expect($pendingMessage->isRead())->toBeFalse();
    expect($pendingMessage->isReplied())->toBeFalse();

    expect($readMessage->isPending())->toBeFalse();
    expect($readMessage->isRead())->toBeTrue();
    expect($readMessage->isReplied())->toBeFalse();

    expect($repliedMessage->isPending())->toBeFalse();
    expect($repliedMessage->isRead())->toBeTrue();
    expect($repliedMessage->isReplied())->toBeTrue();
});

it('calculates priority correctly', function () {
    $urgentMessage = Message::factory()->urgent()->create();
    $highMessage = Message::factory()->highPriority()->create();
    $normalMessage = Message::factory()->create(['priority' => 'normal']);

    expect($urgentMessage->isUrgent())->toBeTrue();
    expect($urgentMessage->isHighPriority())->toBeTrue();

    expect($highMessage->isUrgent())->toBeFalse();
    expect($highMessage->isHighPriority())->toBeTrue();

    expect($normalMessage->isUrgent())->toBeFalse();
    expect($normalMessage->isHighPriority())->toBeFalse();
});

it('formats phone numbers correctly', function () {
    $messageWithSpanishPhone = Message::factory()->create(['phone' => '600123456']);
    $messageWithInternationalPhone = Message::factory()->create(['phone' => '+34 600 123 456']);
    $messageWithoutPhone = Message::factory()->create(['phone' => null]);

    expect($messageWithSpanishPhone->getFormattedPhone())->toBe('+34 600 123 456');
    expect($messageWithInternationalPhone->getFormattedPhone())->toBe('+34 600 123 456');
    expect($messageWithoutPhone->getFormattedPhone())->toBeNull();
});

it('calculates response time correctly', function () {
    $createdAt = now()->subHours(5);
    $repliedAt = now();
    
    $repliedMessage = Message::factory()->create([
        'created_at' => $createdAt,
        'replied_at' => $repliedAt,
    ]);
    
    $unrepliedMessage = Message::factory()->create(['replied_at' => null]);

    expect($repliedMessage->getResponseTime())->toBe(5); // 5 hours
    expect($unrepliedMessage->getResponseTime())->toBeNull();
});

it('generates short message correctly', function () {
    $longMessage = Message::factory()->create([
        'message' => str_repeat('This is a very long message. ', 20) // > 100 chars
    ]);
    
    $shortMessage = Message::factory()->create(['message' => 'Short message.']);

    expect(strlen($longMessage->getShortMessage()))->toBeLessThanOrEqual(103); // 100 + '...'
    expect($longMessage->getShortMessage())->toEndWith('...');
    expect($shortMessage->getShortMessage())->toBe('Short message.');
});

it('includes computed properties in API response', function () {
    $user = User::factory()->create();
    $message = Message::factory()->read()->urgent()->withPhone()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson("/api/v1/messages/{$message->id}");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'is_read', 'is_replied', 'is_pending', 'is_assigned',
                    'is_urgent', 'is_high_priority', 'status_label',
                    'priority_label', 'type_label', 'formatted_phone',
                    'response_time_hours', 'has_internal_notes', 'short_message'
                ]
            ]);
    
    $data = $response->json('data');
    expect($data['is_read'])->toBeTrue();
    expect($data['is_pending'])->toBeFalse();
    expect($data['is_urgent'])->toBeTrue();
    expect($data['is_high_priority'])->toBeTrue();
    expect($data['status_label'])->toBe('Leído');
    expect($data['priority_label'])->toBe('Urgente');
});

it('can create messages of different types', function () {
    $types = ['contact', 'support', 'complaint', 'suggestion'];

    foreach ($types as $type) {
        $messageData = [
            'email' => "test_{$type}@example.com",
            'subject' => "Test {$type} message",
            'message' => "This is a {$type} message.",
            'message_type' => $type,
        ];

        $response = $this->postJson('/api/v1/messages', $messageData);
        $response->assertStatus(201);
    }

    expect(Message::count())->toBe(4);
});
