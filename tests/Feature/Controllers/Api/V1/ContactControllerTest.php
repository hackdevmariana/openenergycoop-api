<?php

use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    // Clean up data before each test
    Contact::query()->delete();
});

// GET /api/v1/contacts - List Contacts
it('can list published contacts', function () {
    $publishedContacts = Contact::factory()->count(3)->published()->create();
    $draftContact = Contact::factory()->draft()->create();

    $response = $this->getJson('/api/v1/contacts');

    $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'address', 'phone', 'email', 'contact_type',
                        'latitude', 'longitude', 'business_hours', 'additional_info',
                        'is_draft', 'is_primary', 'created_at', 'updated_at',
                        'is_published', 'has_location', 'type_label'
                    ]
                ]
            ]);
});

it('orders contacts by primary status first', function () {
    $contact1 = Contact::factory()->published()->create(['is_primary' => false, 'contact_type' => 'main']);
    $contact2 = Contact::factory()->published()->primary()->create(['contact_type' => 'support']);
    $contact3 = Contact::factory()->published()->create(['is_primary' => false, 'contact_type' => 'sales']);

    $response = $this->getJson('/api/v1/contacts');

    $response->assertStatus(200);
    $data = $response->json('data');
    
    expect($data[0]['id'])->toBe($contact2->id); // Primary contact first
    expect($data[0]['is_primary'])->toBeTrue();
});

it('can filter contacts by type', function () {
    Contact::factory()->count(2)->published()->support()->create();
    Contact::factory()->count(3)->published()->sales()->create();

    $response = $this->getJson('/api/v1/contacts?type=support');

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    
    foreach ($response->json('data') as $contact) {
        expect($contact['contact_type'])->toBe('support');
    }
});

it('can filter primary contacts only', function () {
    // Create primary contacts of different types to avoid the "one primary per type" logic
    Contact::factory()->published()->primary()->create(['contact_type' => 'main']);
    Contact::factory()->published()->primary()->create(['contact_type' => 'support']);
    Contact::factory()->count(3)->published()->create(['is_primary' => false]);

    $response = $this->getJson('/api/v1/contacts?primary_only=1');

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    
    foreach ($response->json('data') as $contact) {
        expect($contact['is_primary'])->toBeTrue();
    }
});

it('can filter contacts with location', function () {
    Contact::factory()->count(2)->published()->withLocation()->create();
    Contact::factory()->count(3)->published()->withoutLocation()->create();

    $response = $this->getJson('/api/v1/contacts?with_location=1');

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    
    foreach ($response->json('data') as $contact) {
        expect($contact['has_location'])->toBeTrue();
        expect($contact['latitude'])->not->toBeNull();
        expect($contact['longitude'])->not->toBeNull();
    }
});

// POST /api/v1/contacts - Store Contact
it('can create a new contact', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Sanctum::actingAs($user);

    $contactData = [
        'address' => 'Calle Principal 123, Madrid',
        'phone' => '+34 900 123 456',
        'email' => 'info@cooperativa.com',
        'latitude' => 40.4168,
        'longitude' => -3.7038,
        'contact_type' => 'main',
        'business_hours' => [
            'monday' => ['open' => '09:00', 'close' => '18:00'],
            'friday' => ['open' => '09:00', 'close' => '17:00'],
        ],
        'additional_info' => 'Horario de atención al público',
        'organization_id' => $organization->id,
        'is_draft' => false,
        'is_primary' => true,
    ];

    $response = $this->postJson('/api/v1/contacts', $contactData);

    $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'address', 'phone', 'email', 'contact_type'],
                'message'
            ]);

    $this->assertDatabaseHas('contacts', [
        'address' => 'Calle Principal 123, Madrid',
        'contact_type' => 'main',
        'created_by_user_id' => $user->id,
    ]);
});

it('sets default values when creating contact', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $contactData = [
        'contact_type' => 'main',
        'address' => 'Test Address',
    ];

    $response = $this->postJson('/api/v1/contacts', $contactData);

    $response->assertStatus(201);
    
    $contact = Contact::latest()->first();
    expect($contact->is_draft)->toBeTrue(); // Default
    expect($contact->is_primary)->toBeFalse(); // Default
});

it('validates required contact type when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/contacts', [
        'address' => 'Some address',
    ]);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['contact_type']);
});

it('validates contact type enum when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $contactData = [
        'contact_type' => 'invalid_type',
        'address' => 'Some address',
    ];

    $response = $this->postJson('/api/v1/contacts', $contactData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['contact_type']);
});

it('validates at least one contact method is provided', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $contactData = [
        'contact_type' => 'main',
        // No address, phone, or email
        'additional_info' => 'Just some info',
    ];

    $response = $this->postJson('/api/v1/contacts', $contactData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['contact_info']);
});

it('validates location coordinates together', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $contactData = [
        'contact_type' => 'main',
        'address' => 'Test Address',
        'latitude' => 40.4168,
        // Missing longitude
    ];

    $response = $this->postJson('/api/v1/contacts', $contactData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['location']);
});

// Business hours validation is simplified, no specific format validation in tests

it('validates organization existence when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $contactData = [
        'contact_type' => 'main',
        'address' => 'Test Address',
        'organization_id' => 999999, // Non-existent
    ];

    $response = $this->postJson('/api/v1/contacts', $contactData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['organization_id']);
});

// GET /api/v1/contacts/{contact} - Show Contact
it('can show a published contact', function () {
    $contact = Contact::factory()->published()->create();

    $response = $this->getJson("/api/v1/contacts/{$contact->id}");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'address', 'phone', 'email', 'contact_type',
                    'is_published', 'has_location', 'type_label'
                ]
            ]);
});

it('returns 404 for draft contact', function () {
    $contact = Contact::factory()->draft()->create();

    $response = $this->getJson("/api/v1/contacts/{$contact->id}");

    $response->assertStatus(404)
            ->assertJson(['message' => 'Contacto no encontrado']);
});

it('returns 404 for non-existent contact', function () {
    $response = $this->getJson('/api/v1/contacts/999999');

    $response->assertStatus(404);
});

// PUT /api/v1/contacts/{contact} - Update Contact
it('can update a contact', function () {
    $user = User::factory()->create();
    $contact = Contact::factory()->published()->create([
        'contact_type' => 'main',
        'address' => 'Original Address',
    ]);
    Sanctum::actingAs($user);

    $updateData = [
        'address' => 'Updated Address',
        'contact_type' => 'support',
        'is_primary' => true,
    ];

    $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'address', 'contact_type'],
                'message'
            ]);

    $this->assertDatabaseHas('contacts', [
        'id' => $contact->id,
        'address' => 'Updated Address',
        'contact_type' => 'support',
    ]);
});

it('validates location coordinates together when updating', function () {
    $user = User::factory()->create();
    $contact = Contact::factory()->published()->create();
    Sanctum::actingAs($user);

    $updateData = [
        'latitude' => 40.4168,
        // Missing longitude
    ];

    $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['location']);
});

// DELETE /api/v1/contacts/{contact} - Delete Contact
it('can delete a contact', function () {
    $user = User::factory()->create();
    $contact = Contact::factory()->published()->create();
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

    $response->assertStatus(200)
            ->assertJson(['message' => 'Contacto eliminado exitosamente']);

    $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
});

// GET /api/v1/contacts/by-type/{type} - Get Contacts by Type
it('can get contacts by type', function () {
    Contact::factory()->count(3)->published()->support()->create();
    Contact::factory()->count(2)->published()->sales()->create();

    $response = $this->getJson('/api/v1/contacts/by-type/support');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'contact_type']],
                'type',
                'total'
            ])
            ->assertJson([
                'type' => 'support',
                'total' => 3
            ])
            ->assertJsonCount(3, 'data');
    
    foreach ($response->json('data') as $contact) {
        expect($contact['contact_type'])->toBe('support');
    }
});

it('can filter primary contacts by type', function () {
    // Create primary support contacts for different organizations to avoid conflict
    $org1 = Organization::factory()->create();
    $org2 = Organization::factory()->create();
    
    Contact::factory()->published()->support()->primary()->create(['organization_id' => $org1->id]);
    Contact::factory()->published()->support()->primary()->create(['organization_id' => $org2->id]);
    Contact::factory()->count(3)->published()->support()->create(['is_primary' => false]);

    $response = $this->getJson('/api/v1/contacts/by-type/support?primary_only=1');

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    
    foreach ($response->json('data') as $contact) {
        expect($contact['contact_type'])->toBe('support');
        expect($contact['is_primary'])->toBeTrue();
    }
});

// Authentication Tests
it('requires authentication for creating contact', function () {
    $response = $this->postJson('/api/v1/contacts', [
        'contact_type' => 'main',
        'address' => 'Test Address',
    ]);

    $response->assertStatus(401);
});

it('requires authentication for updating contact', function () {
    $contact = Contact::factory()->published()->create();

    $response = $this->putJson("/api/v1/contacts/{$contact->id}", [
        'address' => 'Updated Address',
    ]);

    $response->assertStatus(401);
});

it('requires authentication for deleting contact', function () {
    $contact = Contact::factory()->published()->create();

    $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

    $response->assertStatus(401);
});

// Model Logic Tests
it('ensures only one primary contact per type per organization', function () {
    $organization = Organization::factory()->create();
    
    // Create first primary main contact
    $contact1 = Contact::factory()->published()->create([
        'contact_type' => 'main',
        'is_primary' => true,
        'organization_id' => $organization->id,
    ]);
    
    // Create second primary main contact for same organization
    $contact2 = Contact::factory()->published()->create([
        'contact_type' => 'main',
        'is_primary' => true,
        'organization_id' => $organization->id,
    ]);

    // First contact should no longer be primary
    expect($contact1->fresh()->is_primary)->toBeFalse();
    expect($contact2->fresh()->is_primary)->toBeTrue();
});

it('allows multiple primary contacts of different types', function () {
    $organization = Organization::factory()->create();
    
    $mainContact = Contact::factory()->published()->create([
        'contact_type' => 'main',
        'is_primary' => true,
        'organization_id' => $organization->id,
    ]);
    
    $supportContact = Contact::factory()->published()->create([
        'contact_type' => 'support',
        'is_primary' => true,
        'organization_id' => $organization->id,
    ]);

    // Both should remain primary as they are different types
    expect($mainContact->fresh()->is_primary)->toBeTrue();
    expect($supportContact->fresh()->is_primary)->toBeTrue();
});

it('calculates has_location correctly', function () {
    $contactWithLocation = Contact::factory()->published()->withLocation()->create();
    $contactWithoutLocation = Contact::factory()->published()->withoutLocation()->create();

    expect($contactWithLocation->hasLocation())->toBeTrue();
    expect($contactWithoutLocation->hasLocation())->toBeFalse();
});

it('formats phone number correctly', function () {
    $contact = Contact::factory()->published()->create([
        'phone' => '+34 900-123.456',
    ]);

    expect($contact->getFormattedPhone())->toBe('+34900123456');
});

it('returns empty string for null phone', function () {
    $contact = Contact::factory()->published()->create([
        'phone' => null,
    ]);

    expect($contact->getFormattedPhone())->toBe('');
});

it('gets type label correctly', function () {
    $contact = Contact::factory()->published()->create([
        'contact_type' => 'support',
    ]);

    expect($contact->getTypeLabel())->toBe('Soporte');
});

it('checks business hours correctly', function () {
    // Create a contact with current day business hours
    $currentDay = strtolower(now()->format('l')); // monday, tuesday, etc.
    $contact = Contact::factory()->published()->create([
        'business_hours' => [
            $currentDay => ['open' => '00:00', 'close' => '23:59'],
        ],
    ]);

    expect($contact->isBusinessHours())->toBeTrue();
});

it('returns true for business hours when no hours specified', function () {
    $contact = Contact::factory()->published()->create([
        'business_hours' => null,
    ]);

    expect($contact->isBusinessHours())->toBeTrue(); // Always available
});

it('includes computed properties in API response', function () {
    $contact = Contact::factory()->published()->withLocation()->create([
        'contact_type' => 'support',
        'phone' => '+34-900-123-456',
    ]);

    $response = $this->getJson("/api/v1/contacts/{$contact->id}");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'is_published', 'has_location', 'type_label',
                    'formatted_address', 'formatted_phone', 'is_business_hours'
                ]
            ]);
    
    $data = $response->json('data');
    expect($data['is_published'])->toBeTrue();
    expect($data['has_location'])->toBeTrue();
    expect($data['type_label'])->toBe('Soporte');
    expect($data['formatted_phone'])->toBe('+34900123456');
});

it('can create contact with comprehensive business hours', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $contactData = [
        'contact_type' => 'main',
        'address' => 'Test Address',
        'business_hours' => [
            'monday' => ['open' => '09:00', 'close' => '18:00'],
            'tuesday' => ['open' => '09:00', 'close' => '18:00'],
            'wednesday' => ['open' => '09:00', 'close' => '18:00'],
            'thursday' => ['open' => '09:00', 'close' => '18:00'],
            'friday' => ['open' => '09:00', 'close' => '17:00'],
            'saturday' => ['open' => '10:00', 'close' => '14:00'],
        ],
    ];

    $response = $this->postJson('/api/v1/contacts', $contactData);

    $response->assertStatus(201);
    
    $contact = Contact::latest()->first();
    expect($contact->business_hours)->toBeArray();
    expect($contact->business_hours['friday']['close'])->toBe('17:00');
});
