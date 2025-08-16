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

// GET /api/v1/faqs - List FAQs
it('can list published faqs', function () {
    $topic = FaqTopic::factory()->create();
    $publishedFaqs = Faq::factory()->count(3)->published()->create(['topic_id' => $topic->id]);
    $draftFaq = Faq::factory()->draft()->create(['topic_id' => $topic->id]);

    $response = $this->getJson('/api/v1/faqs');

    $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'question', 'answer', 'topic_id', 'position',
                        'views_count', 'helpful_count', 'not_helpful_count',
                        'is_featured', 'tags', 'language', 'is_draft',
                        'published_at', 'created_at', 'updated_at',
                        'is_published', 'helpful_rate'
                    ]
                ]
            ]);
});

it('can filter faqs by topic', function () {
    $topic1 = FaqTopic::factory()->create();
    $topic2 = FaqTopic::factory()->create();
    
    Faq::factory()->count(2)->published()->create(['topic_id' => $topic1->id]);
    Faq::factory()->count(3)->published()->create(['topic_id' => $topic2->id]);

    $response = $this->getJson("/api/v1/faqs?topic_id={$topic1->id}");

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
});

it('can filter faqs by language', function () {
    $topic = FaqTopic::factory()->create();
    Faq::factory()->count(2)->published()->spanish()->create(['topic_id' => $topic->id]);
    Faq::factory()->count(3)->published()->english()->create(['topic_id' => $topic->id]);

    $response = $this->getJson('/api/v1/faqs?language=es');

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
});

it('can search faqs by question and answer', function () {
    $topic = FaqTopic::factory()->create();
    Faq::factory()->published()->create([
        'topic_id' => $topic->id,
        'question' => '¿Cómo me uno a la cooperativa?',
        'answer' => 'Puedes unirte completando el formulario...'
    ]);
    Faq::factory()->published()->create([
        'topic_id' => $topic->id,
        'question' => '¿Cuánto cuesta la membresía?',
        'answer' => 'La membresía tiene diferentes precios...'
    ]);

    $response = $this->getJson('/api/v1/faqs?search=cooperativa');

    $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
});

it('can search faqs by tags', function () {
    $topic = FaqTopic::factory()->create();
    Faq::factory()->published()->create([
        'topic_id' => $topic->id,
        'tags' => ['membership', 'general']
    ]);
    Faq::factory()->published()->create([
        'topic_id' => $topic->id,
        'tags' => ['billing', 'technical']
    ]);

    $response = $this->getJson('/api/v1/faqs?search=membership');

    $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
});

it('can filter featured faqs', function () {
    $topic = FaqTopic::factory()->create();
    Faq::factory()->count(2)->published()->featured()->create(['topic_id' => $topic->id]);
    Faq::factory()->count(3)->published()->create(['topic_id' => $topic->id, 'is_featured' => false]);

    $response = $this->getJson('/api/v1/faqs?featured=1');

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
});

it('orders faqs by position and creation date', function () {
    $topic = FaqTopic::factory()->create();
    $faq1 = Faq::factory()->published()->create(['topic_id' => $topic->id, 'position' => 3]);
    $faq2 = Faq::factory()->published()->create(['topic_id' => $topic->id, 'position' => 1]);
    $faq3 = Faq::factory()->published()->create(['topic_id' => $topic->id, 'position' => 2]);

    $response = $this->getJson('/api/v1/faqs');

    $response->assertStatus(200);
    $data = $response->json('data');
    
    expect($data[0]['id'])->toBe($faq2->id); // position 1
    expect($data[1]['id'])->toBe($faq3->id); // position 2
    expect($data[2]['id'])->toBe($faq1->id); // position 3
});

// POST /api/v1/faqs - Store FAQ
it('can create a new faq', function () {
    $user = User::factory()->create();
    $topic = FaqTopic::factory()->create();
    Sanctum::actingAs($user);

    $faqData = [
        'topic_id' => $topic->id,
        'question' => '¿Cómo funciona la energía renovable?',
        'answer' => 'La energía renovable se genera a partir de fuentes naturales...',
        'position' => 5,
        'is_featured' => true,
        'tags' => ['renewable', 'energy'],
        'language' => 'es',
        'is_draft' => false,
    ];

    $response = $this->postJson('/api/v1/faqs', $faqData);

    $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'question', 'answer', 'topic_id'],
                'message'
            ]);

    $this->assertDatabaseHas('faqs', [
        'question' => '¿Cómo funciona la energía renovable?',
        'topic_id' => $topic->id,
        'created_by_user_id' => $user->id,
    ]);
});

it('sets default values when creating faq', function () {
    $user = User::factory()->create();
    $topic = FaqTopic::factory()->create();
    Sanctum::actingAs($user);

    $faqData = [
        'question' => '¿Pregunta de prueba?',
        'answer' => 'Respuesta de prueba.',
    ];

    $response = $this->postJson('/api/v1/faqs', $faqData);

    $response->assertStatus(201);
    
    $faq = Faq::latest()->first();
    expect($faq->is_featured)->toBeFalse();
    expect($faq->is_draft)->toBeTrue();
    expect($faq->position)->toBe(0);
    expect($faq->language)->toBe('es');
});

it('validates required fields when creating faq', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/faqs', []);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['question', 'answer']);
});

it('validates topic existence when creating faq', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $faqData = [
        'topic_id' => 999999, // Non-existent topic
        'question' => '¿Pregunta?',
        'answer' => 'Respuesta.',
    ];

    $response = $this->postJson('/api/v1/faqs', $faqData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['topic_id']);
});

// GET /api/v1/faqs/{faq} - Show FAQ
it('can show a published faq', function () {
    $topic = FaqTopic::factory()->create();
    $faq = Faq::factory()->published()->create(['topic_id' => $topic->id]);

    $response = $this->getJson("/api/v1/faqs/{$faq->id}");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'question', 'answer', 'topic_id', 'is_published'
                ]
            ]);
});

it('returns 404 for draft faq', function () {
    $topic = FaqTopic::factory()->create();
    $faq = Faq::factory()->draft()->create(['topic_id' => $topic->id]);

    $response = $this->getJson("/api/v1/faqs/{$faq->id}");

    $response->assertStatus(404)
            ->assertJson(['message' => 'FAQ no encontrada']);
});

it('returns 404 for non-existent faq', function () {
    $response = $this->getJson('/api/v1/faqs/999999');

    $response->assertStatus(404);
});

// PUT /api/v1/faqs/{faq} - Update FAQ
it('can update a faq', function () {
    $user = User::factory()->create();
    $topic1 = FaqTopic::factory()->create();
    $topic2 = FaqTopic::factory()->create();
    $faq = Faq::factory()->published()->create(['topic_id' => $topic1->id]);
    Sanctum::actingAs($user);

    $updateData = [
        'topic_id' => $topic2->id,
        'question' => 'Pregunta actualizada',
        'answer' => 'Respuesta actualizada',
        'is_featured' => true,
    ];

    $response = $this->putJson("/api/v1/faqs/{$faq->id}", $updateData);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'question', 'answer'],
                'message'
            ]);

    $this->assertDatabaseHas('faqs', [
        'id' => $faq->id,
        'question' => 'Pregunta actualizada',
        'topic_id' => $topic2->id,
        'updated_by_user_id' => $user->id,
    ]);
});

it('validates fields when updating faq', function () {
    $user = User::factory()->create();
    $topic = FaqTopic::factory()->create();
    $faq = Faq::factory()->published()->create(['topic_id' => $topic->id]);
    Sanctum::actingAs($user);

    $response = $this->putJson("/api/v1/faqs/{$faq->id}", [
        'question' => '', // Invalid
        'topic_id' => 999999, // Non-existent
    ]);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['question', 'topic_id']);
});

// DELETE /api/v1/faqs/{faq} - Delete FAQ
it('can delete a faq', function () {
    $user = User::factory()->create();
    $topic = FaqTopic::factory()->create();
    $faq = Faq::factory()->published()->create(['topic_id' => $topic->id]);
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/faqs/{$faq->id}");

    $response->assertStatus(200)
            ->assertJson(['message' => 'FAQ eliminada exitosamente']);

    $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
});

// GET /api/v1/faqs/featured - Featured FAQs
it('can get featured faqs', function () {
    $topic = FaqTopic::factory()->create();
    Faq::factory()->count(3)->published()->featured()->create(['topic_id' => $topic->id]);
    Faq::factory()->count(2)->published()->create(['topic_id' => $topic->id, 'is_featured' => false]);

    $response = $this->getJson('/api/v1/faqs/featured');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'question', 'answer']],
                'total'
            ])
            ->assertJsonCount(3, 'data');
});

it('can limit featured faqs results', function () {
    $topic = FaqTopic::factory()->create();
    Faq::factory()->count(15)->published()->featured()->create(['topic_id' => $topic->id]);

    $response = $this->getJson('/api/v1/faqs/featured?limit=5');

    $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
});

it('respects maximum limit for featured faqs', function () {
    $topic = FaqTopic::factory()->create();
    Faq::factory()->count(25)->published()->featured()->create(['topic_id' => $topic->id]);

    $response = $this->getJson('/api/v1/faqs/featured?limit=30'); // Requesting more than max

    $response->assertStatus(200)
            ->assertJsonCount(20, 'data'); // Should be limited to 20
});

it('can filter featured faqs by language', function () {
    $topic = FaqTopic::factory()->create();
    Faq::factory()->count(2)->published()->featured()->spanish()->create(['topic_id' => $topic->id]);
    Faq::factory()->count(3)->published()->featured()->english()->create(['topic_id' => $topic->id]);

    $response = $this->getJson('/api/v1/faqs/featured?language=es');

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
});

// GET /api/v1/faqs/search - Search FAQs
it('can search faqs with minimum query length', function () {
    $topic = FaqTopic::factory()->create();
    Faq::factory()->published()->create([
        'topic_id' => $topic->id,
        'question' => 'Pregunta sobre energía solar',
        'answer' => 'La energía solar es renovable'
    ]);

    $response = $this->getJson('/api/v1/faqs/search?q=energía');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'question', 'answer']],
                'query',
                'total'
            ])
            ->assertJson(['query' => 'energía'])
            ->assertJsonCount(1, 'data');
});

it('validates minimum search query length', function () {
    $response = $this->getJson('/api/v1/faqs/search?q=ab'); // Too short

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
});

it('validates search language parameter', function () {
    $response = $this->getJson('/api/v1/faqs/search?q=test&language=invalid');

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['language']);
});

it('limits search results to 20', function () {
    $topic = FaqTopic::factory()->create();
    Faq::factory()->count(25)->published()->create([
        'topic_id' => $topic->id,
        'question' => 'Pregunta sobre test',
    ]);

    $response = $this->getJson('/api/v1/faqs/search?q=test');

    $response->assertStatus(200)
            ->assertJsonCount(20, 'data');
});

// Authentication Tests
it('requires authentication for creating faq', function () {
    $topic = FaqTopic::factory()->create();
    
    $response = $this->postJson('/api/v1/faqs', [
        'topic_id' => $topic->id,
        'question' => 'Test question',
        'answer' => 'Test answer',
    ]);

    $response->assertStatus(401);
});

it('requires authentication for updating faq', function () {
    $topic = FaqTopic::factory()->create();
    $faq = Faq::factory()->published()->create(['topic_id' => $topic->id]);

    $response = $this->putJson("/api/v1/faqs/{$faq->id}", [
        'question' => 'Updated question',
    ]);

    $response->assertStatus(401);
});

it('requires authentication for deleting faq', function () {
    $topic = FaqTopic::factory()->create();
    $faq = Faq::factory()->published()->create(['topic_id' => $topic->id]);

    $response = $this->deleteJson("/api/v1/faqs/{$faq->id}");

    $response->assertStatus(401);
});

// Model Logic Tests
it('calculates helpful rate correctly', function () {
    $topic = FaqTopic::factory()->create();
    $faq = Faq::factory()->create([
        'topic_id' => $topic->id,
        'helpful_count' => 8,
        'not_helpful_count' => 2,
    ]);

    expect($faq->helpful_rate)->toBe(80.0);
});

it('returns zero helpful rate when no votes', function () {
    $topic = FaqTopic::factory()->create();
    $faq = Faq::factory()->create([
        'topic_id' => $topic->id,
        'helpful_count' => 0,
        'not_helpful_count' => 0,
    ]);

    expect($faq->helpful_rate)->toBe(0);
});

it('can increment views count', function () {
    $topic = FaqTopic::factory()->create();
    $faq = Faq::factory()->create([
        'topic_id' => $topic->id,
        'views_count' => 5,
    ]);

    $faq->incrementViews();

    expect($faq->fresh()->views_count)->toBe(6);
});

it('can mark as helpful', function () {
    $topic = FaqTopic::factory()->create();
    $faq = Faq::factory()->create([
        'topic_id' => $topic->id,
        'helpful_count' => 3,
    ]);

    $faq->markAsHelpful();

    expect($faq->fresh()->helpful_count)->toBe(4);
});

it('can mark as not helpful', function () {
    $topic = FaqTopic::factory()->create();
    $faq = Faq::factory()->create([
        'topic_id' => $topic->id,
        'not_helpful_count' => 1,
    ]);

    $faq->markAsNotHelpful();

    expect($faq->fresh()->not_helpful_count)->toBe(2);
});

it('auto-sets published_at when creating non-draft faq', function () {
    $topic = FaqTopic::factory()->create();
    $faq = Faq::factory()->create([
        'topic_id' => $topic->id,
        'is_draft' => false,
        'published_at' => null,
    ]);

    expect($faq->published_at)->not->toBeNull();
});

it('generates readable and short answer attributes', function () {
    $topic = FaqTopic::factory()->create();
    $htmlAnswer = '<p>Esta es una <strong>respuesta</strong> con <em>HTML</em> que es muy larga y necesita ser cortada en algún punto para mostrar solo una parte de la respuesta completa.</p>';
    
    $faq = Faq::factory()->create([
        'topic_id' => $topic->id,
        'answer' => $htmlAnswer,
    ]);

    expect($faq->readable_answer)->toBe('Esta es una respuesta con HTML que es muy larga y necesita ser cortada en algún punto para mostrar solo una parte de la respuesta completa.');
    expect(strlen($faq->short_answer))->toBeLessThanOrEqual(153); // 150 + "..."
});
