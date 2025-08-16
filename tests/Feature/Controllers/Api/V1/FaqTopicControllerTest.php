<?php

use App\Models\Faq;
use App\Models\FaqTopic;
use App\Models\Organization;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    // Clean up data before each test
    Faq::query()->delete();
    FaqTopic::query()->delete();
});

// GET /api/v1/faq-topics - List FAQ Topics
it('can list active faq topics', function () {
    FaqTopic::factory()->count(3)->create(['is_active' => true]);
    FaqTopic::factory()->count(2)->inactive()->create();

    $response = $this->getJson('/api/v1/faq-topics');

    $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'name', 'slug', 'description', 'icon', 'color',
                        'sort_order', 'is_active', 'language', 'organization_id',
                        'created_at', 'updated_at'
                    ]
                ]
            ]);
});

it('can filter faq topics by language', function () {
    FaqTopic::factory()->count(2)->spanish()->create();
    FaqTopic::factory()->count(3)->english()->create();

    $response = $this->getJson('/api/v1/faq-topics?language=es');

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
});

it('can include faq count in topic listing', function () {
    $topic1 = FaqTopic::factory()->create();
    $topic2 = FaqTopic::factory()->create();
    
    Faq::factory()->count(3)->published()->create(['topic_id' => $topic1->id]);
    Faq::factory()->count(2)->published()->create(['topic_id' => $topic2->id]);

    $response = $this->getJson('/api/v1/faq-topics?with_faqs_count=1');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['faqs_count']
                ]
            ]);
});

it('orders topics by sort_order and name', function () {
    $topic1 = FaqTopic::factory()->create(['name' => 'C Topic', 'sort_order' => 2]);
    $topic2 = FaqTopic::factory()->create(['name' => 'A Topic', 'sort_order' => 1]);
    $topic3 = FaqTopic::factory()->create(['name' => 'B Topic', 'sort_order' => 2]);

    $response = $this->getJson('/api/v1/faq-topics');

    $response->assertStatus(200);
    $data = $response->json('data');
    
    expect($data[0]['id'])->toBe($topic2->id); // sort_order 1
    expect($data[1]['id'])->toBe($topic3->id); // sort_order 2, name B
    expect($data[2]['id'])->toBe($topic1->id); // sort_order 2, name C
});

// POST /api/v1/faq-topics - Store FAQ Topic
it('can create a new faq topic', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $topicData = [
        'name' => 'Energía Renovable',
        'description' => 'Preguntas sobre energía renovable y sostenibilidad',
        'icon' => 'fas fa-leaf',
        'color' => '#28a745',
        'sort_order' => 5,
        'language' => 'es',
    ];

    $response = $this->postJson('/api/v1/faq-topics', $topicData);

    $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'description'],
                'message'
            ]);

    $this->assertDatabaseHas('faq_topics', [
        'name' => 'Energía Renovable',
        'slug' => 'energia-renovable',
    ]);
});

it('auto-generates slug when creating topic', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $topicData = [
        'name' => 'Preguntas Técnicas',
    ];

    $response = $this->postJson('/api/v1/faq-topics', $topicData);

    $response->assertStatus(201);
    
    $topic = FaqTopic::latest()->first();
    expect($topic->slug)->toBe('preguntas-tecnicas');
});

it('sets default values when creating topic', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $topicData = [
        'name' => 'Tema de Prueba',
    ];

    $response = $this->postJson('/api/v1/faq-topics', $topicData);

    $response->assertStatus(201);
    
    $topic = FaqTopic::latest()->first();
    expect($topic->is_active)->toBeTrue();
    expect($topic->sort_order)->toBe(0);
    expect($topic->language)->toBe('es');
});

it('validates required fields when creating topic', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/faq-topics', []);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
});

it('validates unique slug when creating topic', function () {
    $user = User::factory()->create();
    $existingTopic = FaqTopic::factory()->create(['slug' => 'test-topic']);
    Sanctum::actingAs($user);

    $topicData = [
        'name' => 'Another Topic',
        'slug' => 'test-topic', // Same slug
    ];

    $response = $this->postJson('/api/v1/faq-topics', $topicData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
});

it('validates color format when creating topic', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $topicData = [
        'name' => 'Test Topic',
        'color' => 'invalid-color',
    ];

    $response = $this->postJson('/api/v1/faq-topics', $topicData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['color']);
});

// GET /api/v1/faq-topics/{faqTopic} - Show FAQ Topic
it('can show an active faq topic', function () {
    $topic = FaqTopic::factory()->create(['is_active' => true]);

    $response = $this->getJson("/api/v1/faq-topics/{$topic->id}");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'slug', 'description', 'icon', 'color',
                    'sort_order', 'is_active', 'language'
                ]
            ]);
});

it('can show topic with faqs included', function () {
    $topic = FaqTopic::factory()->create();
    Faq::factory()->count(2)->published()->create(['topic_id' => $topic->id]);

    $response = $this->getJson("/api/v1/faq-topics/{$topic->id}?include_faqs=1");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'faqs'
                ]
            ]);
});

it('returns 404 for inactive faq topic', function () {
    $topic = FaqTopic::factory()->inactive()->create();

    $response = $this->getJson("/api/v1/faq-topics/{$topic->id}");

    $response->assertStatus(404)
            ->assertJson(['message' => 'Tema de FAQ no encontrado']);
});

it('returns 404 for non-existent faq topic', function () {
    $response = $this->getJson('/api/v1/faq-topics/999999');

    $response->assertStatus(404);
});

// PUT /api/v1/faq-topics/{faqTopic} - Update FAQ Topic
it('can update a faq topic', function () {
    $user = User::factory()->create();
    $topic = FaqTopic::factory()->create(['name' => 'Original Name']);
    Sanctum::actingAs($user);

    $updateData = [
        'name' => 'Updated Name',
        'description' => 'Updated description',
        'color' => '#ff0000',
        'is_active' => false,
    ];

    $response = $this->putJson("/api/v1/faq-topics/{$topic->id}", $updateData);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'description'],
                'message'
            ]);

    $this->assertDatabaseHas('faq_topics', [
        'id' => $topic->id,
        'name' => 'Updated Name',
        'slug' => 'updated-name',
        'is_active' => false,
    ]);
});

it('updates slug when name changes but slug not provided', function () {
    $user = User::factory()->create();
    $topic = FaqTopic::factory()->create(['name' => 'Original Name', 'slug' => 'original-name']);
    Sanctum::actingAs($user);

    $updateData = [
        'name' => 'New Topic Name',
    ];

    $response = $this->putJson("/api/v1/faq-topics/{$topic->id}", $updateData);

    $response->assertStatus(200);
    
    $topic->refresh();
    expect($topic->slug)->toBe('new-topic-name');
});

it('validates unique slug when updating topic', function () {
    $user = User::factory()->create();
    $topic1 = FaqTopic::factory()->create(['slug' => 'existing-slug']);
    $topic2 = FaqTopic::factory()->create(['slug' => 'another-slug']);
    Sanctum::actingAs($user);

    $updateData = [
        'slug' => 'existing-slug', // Already exists
    ];

    $response = $this->putJson("/api/v1/faq-topics/{$topic2->id}", $updateData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
});

// DELETE /api/v1/faq-topics/{faqTopic} - Delete FAQ Topic
it('can delete a faq topic without faqs', function () {
    $user = User::factory()->create();
    $topic = FaqTopic::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/faq-topics/{$topic->id}");

    $response->assertStatus(200)
            ->assertJson(['message' => 'Tema de FAQ eliminado exitosamente']);

    $this->assertDatabaseMissing('faq_topics', ['id' => $topic->id]);
});

it('prevents deletion of topic with associated faqs', function () {
    $user = User::factory()->create();
    $topic = FaqTopic::factory()->create();
    Faq::factory()->published()->create(['topic_id' => $topic->id]);
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/faq-topics/{$topic->id}");

    $response->assertStatus(422)
            ->assertJson(['message' => 'No se puede eliminar un tema que tiene FAQs asociadas']);

    $this->assertDatabaseHas('faq_topics', ['id' => $topic->id]);
});

// GET /api/v1/faq-topics/{faqTopic}/faqs - Get Topic FAQs
it('can get faqs for a topic', function () {
    $topic = FaqTopic::factory()->create();
    $faqs = Faq::factory()->count(3)->published()->create(['topic_id' => $topic->id]);
    Faq::factory()->draft()->create(['topic_id' => $topic->id]); // Should not appear

    $response = $this->getJson("/api/v1/faq-topics/{$topic->id}/faqs");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'topic' => ['id', 'name'],
                'faqs' => [
                    '*' => ['id', 'question', 'answer', 'position', 'is_featured']
                ],
                'total_faqs'
            ])
            ->assertJsonCount(3, 'faqs');
});

it('can filter topic faqs by language', function () {
    $topic = FaqTopic::factory()->create();
    Faq::factory()->count(2)->published()->spanish()->create(['topic_id' => $topic->id]);
    Faq::factory()->count(3)->published()->english()->create(['topic_id' => $topic->id]);

    $response = $this->getJson("/api/v1/faq-topics/{$topic->id}/faqs?language=es");

    $response->assertStatus(200)
            ->assertJsonCount(2, 'faqs');
});

it('orders topic faqs by position', function () {
    $topic = FaqTopic::factory()->create();
    $faq1 = Faq::factory()->published()->create(['topic_id' => $topic->id, 'position' => 3]);
    $faq2 = Faq::factory()->published()->create(['topic_id' => $topic->id, 'position' => 1]);
    $faq3 = Faq::factory()->published()->create(['topic_id' => $topic->id, 'position' => 2]);

    $response = $this->getJson("/api/v1/faq-topics/{$topic->id}/faqs");

    $response->assertStatus(200);
    $faqs = $response->json('faqs');
    
    expect($faqs[0]['id'])->toBe($faq2->id); // position 1
    expect($faqs[1]['id'])->toBe($faq3->id); // position 2
    expect($faqs[2]['id'])->toBe($faq1->id); // position 3
});

// Authentication Tests
it('requires authentication for creating faq topic', function () {
    $response = $this->postJson('/api/v1/faq-topics', [
        'name' => 'Test Topic',
    ]);

    $response->assertStatus(401);
});

it('requires authentication for updating faq topic', function () {
    $topic = FaqTopic::factory()->create();

    $response = $this->putJson("/api/v1/faq-topics/{$topic->id}", [
        'name' => 'Updated Name',
    ]);

    $response->assertStatus(401);
});

it('requires authentication for deleting faq topic', function () {
    $topic = FaqTopic::factory()->create();

    $response = $this->deleteJson("/api/v1/faq-topics/{$topic->id}");

    $response->assertStatus(401);
});

// Edge Cases and Business Logic
it('handles empty faqs list for topic', function () {
    $topic = FaqTopic::factory()->create();

    $response = $this->getJson("/api/v1/faq-topics/{$topic->id}/faqs");

    $response->assertStatus(200)
            ->assertJsonCount(0, 'faqs')
            ->assertJson(['total_faqs' => 0]);
});

it('can create topic with valid hex color', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $topicData = [
        'name' => 'Colorful Topic',
        'color' => '#FF5733',
    ];

    $response = $this->postJson('/api/v1/faq-topics', $topicData);

    $response->assertStatus(201);
    
    $topic = FaqTopic::latest()->first();
    expect($topic->color)->toBe('#FF5733');
});

it('can create topic with organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Sanctum::actingAs($user);

    $topicData = [
        'name' => 'Org Topic',
        'organization_id' => $organization->id,
    ];

    $response = $this->postJson('/api/v1/faq-topics', $topicData);

    $response->assertStatus(201);
    
    $topic = FaqTopic::latest()->first();
    expect($topic->organization_id)->toBe($organization->id);
});

it('validates organization existence when creating topic', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $topicData = [
        'name' => 'Test Topic',
        'organization_id' => 999999, // Non-existent
    ];

    $response = $this->postJson('/api/v1/faq-topics', $topicData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['organization_id']);
});

it('validates language parameter', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $topicData = [
        'name' => 'Test Topic',
        'language' => 'invalid', // Not in allowed list
    ];

    $response = $this->postJson('/api/v1/faq-topics', $topicData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['language']);
});

it('preserves custom slug when provided', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $topicData = [
        'name' => 'Test Topic',
        'slug' => 'custom-slug-here',
    ];

    $response = $this->postJson('/api/v1/faq-topics', $topicData);

    $response->assertStatus(201);
    
    $topic = FaqTopic::latest()->first();
    expect($topic->slug)->toBe('custom-slug-here');
});

it('can update only specific fields', function () {
    $user = User::factory()->create();
    $topic = FaqTopic::factory()->create([
        'name' => 'Original Name',
        'description' => 'Original description',
        'color' => '#000000',
    ]);
    Sanctum::actingAs($user);

    $updateData = [
        'color' => '#FF0000', // Only update color
    ];

    $response = $this->putJson("/api/v1/faq-topics/{$topic->id}", $updateData);

    $response->assertStatus(200);
    
    $topic->refresh();
    expect($topic->name)->toBe('Original Name'); // Unchanged
    expect($topic->description)->toBe('Original description'); // Unchanged
    expect($topic->color)->toBe('#FF0000'); // Changed
});
