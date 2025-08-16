<?php

use App\Models\Hero;
use App\Models\Organization;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    // Clean up data before each test
    Hero::query()->delete();
});

// GET /api/v1/heroes - List Heroes
it('can list published heroes', function () {
    $publishedHeroes = Hero::factory()->count(3)->published()->create();
    $draftHero = Hero::factory()->draft()->create();

    $response = $this->getJson('/api/v1/heroes');

    $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'text', 'subtext', 'image', 'position', 'active',
                        'exhibition_beginning', 'exhibition_end', 'priority',
                        'text_align', 'animation_type', 'cta_style', 'overlay_opacity',
                        'created_at', 'updated_at', 'is_published', 'is_in_exhibition_period',
                        'display_text', 'display_subtext', 'cta_url', 'has_video'
                    ]
                ]
            ]);
});

it('orders heroes by priority, position and creation date', function () {
    $hero1 = Hero::factory()->published()->create(['priority' => 1, 'position' => 3]);
    $hero2 = Hero::factory()->published()->create(['priority' => 3, 'position' => 1]);
    $hero3 = Hero::factory()->published()->create(['priority' => 2, 'position' => 2]);

    $response = $this->getJson('/api/v1/heroes');

    $response->assertStatus(200);
    $data = $response->json('data');
    
    expect($data[0]['id'])->toBe($hero2->id); // priority 3
    expect($data[1]['id'])->toBe($hero3->id); // priority 2
    expect($data[2]['id'])->toBe($hero1->id); // priority 1
});

it('can filter heroes by active status', function () {
    Hero::factory()->count(2)->published()->create(['active' => true]);
    Hero::factory()->count(3)->published()->create(['active' => false]);

    $response = $this->getJson('/api/v1/heroes?active=1');

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    
    foreach ($response->json('data') as $hero) {
        expect($hero['active'])->toBeTrue();
    }
});

it('can filter heroes by language', function () {
    Hero::factory()->count(2)->published()->spanish()->create();
    Hero::factory()->count(3)->published()->english()->create();

    $response = $this->getJson('/api/v1/heroes?language=es');

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    
    foreach ($response->json('data') as $hero) {
        expect($hero['language'])->toBe('es');
    }
});

it('orders heroes for slideshow when requested', function () {
    $hero1 = Hero::factory()->published()->slideshowReady()->create(['priority' => 1]);
    $hero2 = Hero::factory()->published()->slideshowReady()->create(['priority' => 3]);
    $hero3 = Hero::factory()->published()->outsideExhibitionPeriod()->create(['priority' => 5]);

    $response = $this->getJson('/api/v1/heroes?for_slideshow=1');

    $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // Only slideshow-ready heroes
    
    $data = $response->json('data');
    expect($data[0]['id'])->toBe($hero2->id); // Higher priority first
});

// POST /api/v1/heroes - Store Hero
it('can create a new hero', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Sanctum::actingAs($user);

    $heroData = [
        'text' => 'Welcome to our platform',
        'subtext' => 'Discover amazing features',
        'image' => 'https://example.com/image.jpg',
        'mobile_image' => 'https://example.com/mobile-image.jpg',
        'text_button' => 'Get Started',
        'cta_link_external' => 'https://example.com/signup',
        'position' => 1,
        'active' => true,
        'text_align' => 'center',
        'cta_style' => 'primary',
        'animation_type' => 'fade',
        'overlay_opacity' => 50,
        'priority' => 10,
        'language' => 'en',
        'organization_id' => $organization->id,
        'is_draft' => false,
    ];

    $response = $this->postJson('/api/v1/heroes', $heroData);

    $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'text', 'subtext', 'display_text'],
                'message'
            ]);

    $this->assertDatabaseHas('heroes', [
        'text' => 'Welcome to our platform',
        'created_by_user_id' => $user->id,
    ]);
});

it('sets default values when creating hero', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $heroData = [
        'text' => 'Simple hero text',
    ];

    $response = $this->postJson('/api/v1/heroes', $heroData);

    $response->assertStatus(201);
    
    $hero = Hero::latest()->first();
    expect($hero->active)->toBeTrue(); // Default
    expect($hero->is_draft)->toBeTrue(); // Default
    expect($hero->text_align)->toBe('center'); // Default
    expect($hero->cta_style)->toBe('primary'); // Default
    expect($hero->overlay_opacity)->toBe(50); // Default
    expect($hero->priority)->toBe(0); // Default
    expect($hero->language)->toBe('es'); // Default
});

it('auto-sets position when creating hero via model', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    
    // Create existing heroes with explicit position values
    Hero::create([
        'text' => 'First hero',
        'organization_id' => $organization->id,
        'language' => 'es',
        'position' => 1,
        'is_draft' => false,
        'published_at' => now(),
        'created_by_user_id' => $user->id,
    ]);
    
    Hero::create([
        'text' => 'Second hero',
        'organization_id' => $organization->id,
        'language' => 'es',
        'position' => 3,
        'is_draft' => false,
        'published_at' => now(),
        'created_by_user_id' => $user->id,
    ]);

    // Test: Create hero via model directly (position = null)
    $directHero = Hero::create([
        'text' => 'Direct hero',
        'organization_id' => $organization->id,
        'language' => 'es',
        'position' => null,  // Explicitly null
        'is_draft' => false,
        'published_at' => now(),
        'created_by_user_id' => $user->id,
    ]);
    
    expect($directHero->position)->toBe(4);
});

it('validates required content when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/heroes', [
        'subtext' => 'Only subtext, no main content',
    ]);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
});

it('validates text length when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $heroData = [
        'text' => str_repeat('a', 1001), // Exceeds 1000 char limit
    ];

    $response = $this->postJson('/api/v1/heroes', $heroData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
});

it('validates text align enum when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $heroData = [
        'text' => 'Valid text',
        'text_align' => 'invalid_align',
    ];

    $response = $this->postJson('/api/v1/heroes', $heroData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['text_align']);
});

it('validates cta consistency when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $heroData = [
        'text' => 'Valid text',
        'text_button' => 'Click me',
        // No internal_link or cta_link_external
    ];

    $response = $this->postJson('/api/v1/heroes', $heroData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['text_button']);
});

it('validates video url platform when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $heroData = [
        'text' => 'Valid text',
        'video_url' => 'https://unknown-platform.com/video',
    ];

    $response = $this->postJson('/api/v1/heroes', $heroData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['video_url']);
});

it('validates exhibition period dates when creating', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $heroData = [
        'text' => 'Valid text',
        'exhibition_beginning' => '2024-12-31',
        'exhibition_end' => '2024-01-01', // Before beginning
    ];

    $response = $this->postJson('/api/v1/heroes', $heroData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['exhibition_end']);
});

// GET /api/v1/heroes/{hero} - Show Hero
it('can show a published and active hero', function () {
    $hero = Hero::factory()->published()->create(['active' => true]);

    $response = $this->getJson("/api/v1/heroes/{$hero->id}");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'text', 'is_published', 'is_in_exhibition_period',
                    'display_text', 'cta_url', 'has_video', 'image_url',
                    'text_alignment_class', 'cta_style_class'
                ]
            ]);
});

it('returns 404 for draft hero', function () {
    $hero = Hero::factory()->draft()->create();

    $response = $this->getJson("/api/v1/heroes/{$hero->id}");

    $response->assertStatus(404)
            ->assertJson(['message' => 'Hero no encontrado']);
});

it('returns 404 for inactive hero', function () {
    $hero = Hero::factory()->published()->inactive()->create();

    $response = $this->getJson("/api/v1/heroes/{$hero->id}");

    $response->assertStatus(404)
            ->assertJson(['message' => 'Hero no encontrado']);
});

it('returns 404 for non-existent hero', function () {
    $response = $this->getJson('/api/v1/heroes/999999');

    $response->assertStatus(404);
});

// PUT /api/v1/heroes/{hero} - Update Hero
it('can update a hero', function () {
    $user = User::factory()->create();
    $hero = Hero::factory()->published()->create([
        'text' => 'Original text',
        'priority' => 5,
    ]);
    Sanctum::actingAs($user);

    $updateData = [
        'text' => 'Updated text',
        'priority' => 10,
        'active' => false,
    ];

    $response = $this->putJson("/api/v1/heroes/{$hero->id}", $updateData);

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'text', 'priority'],
                'message'
            ]);

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'text' => 'Updated text',
        'priority' => 10,
        'active' => false,
        'updated_by_user_id' => $user->id,
    ]);
});

it('validates content consistency when updating', function () {
    $user = User::factory()->create();
    $hero = Hero::factory()->published()->create(['text' => 'Original text']);
    Sanctum::actingAs($user);

    $updateData = [
        'text' => '', // Remove text
        'image' => null, // Remove image
        'video_url' => null, // Remove video
    ];

    $response = $this->putJson("/api/v1/heroes/{$hero->id}", $updateData);

    $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
});

// DELETE /api/v1/heroes/{hero} - Delete Hero
it('can delete a hero', function () {
    $user = User::factory()->create();
    $hero = Hero::factory()->published()->create();
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/heroes/{$hero->id}");

    $response->assertStatus(200)
            ->assertJson(['message' => 'Hero eliminado exitosamente']);

    $this->assertDatabaseMissing('heroes', ['id' => $hero->id]);
});

// GET /api/v1/heroes/slideshow - Get Slideshow Heroes
it('can get slideshow heroes', function () {
    $slideshowHero1 = Hero::factory()->slideshowReady()->create(['priority' => 10]);
    $slideshowHero2 = Hero::factory()->slideshowReady()->create(['priority' => 5]);
    $nonSlideshowHero = Hero::factory()->published()->outsideExhibitionPeriod()->create();

    $response = $this->getJson('/api/v1/heroes/slideshow');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'text', 'priority']],
                'total',
                'slideshow_ready'
            ])
            ->assertJsonCount(2, 'data'); // Only slideshow-ready heroes
    
    $data = $response->json('data');
    expect($data[0]['priority'])->toBe(10); // Higher priority first
    expect($response->json('slideshow_ready'))->toBeTrue();
});

it('can limit slideshow heroes results', function () {
    Hero::factory()->count(8)->slideshowReady()->create();

    $response = $this->getJson('/api/v1/heroes/slideshow?limit=3');

    $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
});

it('respects maximum limit for slideshow heroes', function () {
    Hero::factory()->count(25)->slideshowReady()->create();

    $response = $this->getJson('/api/v1/heroes/slideshow?limit=30'); // Requesting more than max

    $response->assertStatus(200)
            ->assertJsonCount(20, 'data'); // Should be limited to 20
});

it('can filter slideshow heroes by language', function () {
    Hero::factory()->count(3)->slideshowReady()->spanish()->create();
    Hero::factory()->count(2)->slideshowReady()->english()->create();

    $response = $this->getJson('/api/v1/heroes/slideshow?language=es');

    $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    
    foreach ($response->json('data') as $hero) {
        expect($hero['language'])->toBe('es');
    }
});

// POST /api/v1/heroes/{hero}/duplicate - Duplicate Hero
it('can duplicate a hero', function () {
    $user = User::factory()->create();
    $originalHero = Hero::factory()->published()->create([
        'text' => 'Original hero',
        'position' => 5,
        'active' => true,
    ]);
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/heroes/{$originalHero->id}/duplicate");

    $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'text', 'position', 'active', 'is_draft'],
                'message'
            ]);
    
    $duplicateData = $response->json('data');
    expect($duplicateData['text'])->toBe('Original hero'); // Same text
    expect($duplicateData['position'])->toBeGreaterThan(5); // Next position
    expect($duplicateData['active'])->toBeFalse(); // Duplicates are inactive
    expect($duplicateData['is_draft'])->toBeTrue(); // Duplicates are drafts
    expect($duplicateData['id'])->not->toBe($originalHero->id); // Different ID

    expect(Hero::count())->toBe(2); // Original + duplicate
});

// GET /api/v1/heroes/active - Get Active Heroes
it('can get active heroes in exhibition period', function () {
    $activeHero = Hero::factory()->published()->inExhibitionPeriod()->create(['active' => true]);
    $inactiveHero = Hero::factory()->published()->create(['active' => false]);
    $outsidePeriodHero = Hero::factory()->published()->outsideExhibitionPeriod()->create(['active' => true]);

    $response = $this->getJson('/api/v1/heroes/active');

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'text', 'priority']],
                'total'
            ])
            ->assertJsonCount(1, 'data'); // Only active and in exhibition period
    
    $data = $response->json('data');
    expect($data[0]['id'])->toBe($activeHero->id);
});

it('can filter active heroes by language', function () {
    Hero::factory()->count(3)->published()->inExhibitionPeriod()->spanish()->create(['active' => true]);
    Hero::factory()->count(2)->published()->inExhibitionPeriod()->english()->create(['active' => true]);

    $response = $this->getJson('/api/v1/heroes/active?language=es');

    $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    
    foreach ($response->json('data') as $hero) {
        expect($hero['language'])->toBe('es');
    }
});

// Authentication Tests
it('requires authentication for creating hero', function () {
    $response = $this->postJson('/api/v1/heroes', [
        'text' => 'Test hero',
    ]);

    $response->assertStatus(401);
});

it('requires authentication for updating hero', function () {
    $hero = Hero::factory()->published()->create();

    $response = $this->putJson("/api/v1/heroes/{$hero->id}", [
        'text' => 'Updated text',
    ]);

    $response->assertStatus(401);
});

it('requires authentication for deleting hero', function () {
    $hero = Hero::factory()->published()->create();

    $response = $this->deleteJson("/api/v1/heroes/{$hero->id}");

    $response->assertStatus(401);
});

it('requires authentication for duplicating hero', function () {
    $hero = Hero::factory()->published()->create();

    $response = $this->postJson("/api/v1/heroes/{$hero->id}/duplicate");

    $response->assertStatus(401);
});

// Model Logic Tests
it('calculates exhibition period correctly', function () {
    $currentHero = Hero::factory()->inExhibitionPeriod()->create();
    $pastHero = Hero::factory()->outsideExhibitionPeriod()->create();
    $futureHero = Hero::factory()->create([
        'exhibition_beginning' => now()->addDays(10),
        'exhibition_end' => now()->addDays(20),
    ]);

    expect($currentHero->isInExhibitionPeriod())->toBeTrue();
    expect($pastHero->isInExhibitionPeriod())->toBeFalse();
    expect($futureHero->isInExhibitionPeriod())->toBeFalse();
});

it('generates display text correctly', function () {
    $heroWithText = Hero::factory()->create(['text' => 'Hello World']);
    $heroWithoutText = Hero::factory()->create(['text' => null]);

    expect($heroWithText->getDisplayText())->toBe('Hello World');
    expect($heroWithoutText->getDisplayText())->toBe('');
});

it('generates cta url correctly', function () {
    $heroWithExternal = Hero::factory()->create([
        'cta_link_external' => 'https://example.com',
        'internal_link' => '/internal',
    ]);
    
    $heroWithInternal = Hero::factory()->create([
        'cta_link_external' => null,
        'internal_link' => '/internal',
    ]);
    
    $heroWithoutCta = Hero::factory()->create([
        'cta_link_external' => null,
        'internal_link' => null,
    ]);

    expect($heroWithExternal->getCtaUrl())->toBe('https://example.com'); // External takes priority
    expect($heroWithInternal->getCtaUrl())->toBe('/internal');
    expect($heroWithoutCta->getCtaUrl())->toBeNull();
});

it('detects video content correctly', function () {
    $heroWithVideo = Hero::factory()->withVideo()->create();
    $heroWithoutVideo = Hero::factory()->textOnly()->create();

    expect($heroWithVideo->hasVideo())->toBeTrue();
    expect($heroWithoutVideo->hasVideo())->toBeFalse();
});

it('generates css classes correctly', function () {
    $hero = Hero::factory()->create([
        'text_align' => 'left',
        'cta_style' => 'secondary',
        'animation_type' => 'slide_left',
    ]);

    expect($hero->getTextAlignmentClass())->toBe('text-left');
    expect($hero->getCtaStyleClass())->toBe('btn-secondary');
    expect($hero->getAnimationClass())->toBe('animate-slide-left');
});

it('generates overlay style correctly', function () {
    $heroWithOverlay = Hero::factory()->create(['overlay_opacity' => 75]);
    $heroWithoutOverlay = Hero::factory()->create(['overlay_opacity' => 0]);

    expect($heroWithOverlay->getOverlayStyle())->toBe('background: rgba(0, 0, 0, 0.75);');
    expect($heroWithoutOverlay->getOverlayStyle())->toBe('');
});

it('calculates word count correctly', function () {
    $hero = Hero::factory()->create([
        'text' => 'Hello world this is a test',
        'subtext' => 'Additional text here',
        'text_button' => 'Click me',
    ]);

    expect($hero->getWordCount())->toBe(11); // 6 + 3 + 2 words
});

it('includes computed properties in API response', function () {
    $hero = Hero::factory()->published()->withCta()->create([
        'text' => 'Test hero',
        'text_align' => 'center',
        'animation_type' => 'fade',
    ]);

    $response = $this->getJson("/api/v1/heroes/{$hero->id}");

    $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'is_published', 'is_in_exhibition_period', 'display_text',
                    'display_subtext', 'cta_url', 'has_video', 'image_url',
                    'text_alignment_class', 'cta_style_class', 'animation_class',
                    'overlay_style', 'word_count'
                ]
            ]);
    
    $data = $response->json('data');
    expect($data['is_published'])->toBeTrue();
    expect($data['display_text'])->toBe('Test hero');
    expect($data['text_alignment_class'])->toBe('text-center');
    expect($data['animation_class'])->toBe('animate-fade');
});

it('can create heroes with different configurations', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $configurations = [
        ['text' => 'Text only hero'],
        ['image' => 'https://example.com/image.jpg'],
        ['video_url' => 'https://youtube.com/watch?v=test'],
        ['text' => 'Hero with CTA', 'text_button' => 'Click', 'cta_link_external' => 'https://example.com'],
    ];

    foreach ($configurations as $config) {
        $response = $this->postJson('/api/v1/heroes', $config);
        $response->assertStatus(201);
    }

    expect(Hero::count())->toBe(4);
});
